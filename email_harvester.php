<?php
/**
 * Attorney Email Harvester
 * Visits each attorney's website, finds contact email, updates DB
 * Upload to: /public_html/divorcefinder/admin/email_harvester.php
 */
require __DIR__ . '/auth.php';
define('DB_HOST','localhost');
define('DB_NAME','YOUR_DB');
define('DB_USER','YOUR_USER');
define('DB_PASS','YOUR_PW');
define('REQUEST_DELAY', 800000); // 0.8s between requests
define('BATCH_SIZE', 50); // attorneys per run

set_time_limit(0);
ini_set('max_execution_time',0);
ini_set('memory_limit','256M');
ob_implicit_flush(true);
if(ob_get_level())ob_end_flush();

function db():PDO{
    static $p=null;
    if(!$p)$p=new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4",
        DB_USER,DB_PASS,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
    return $p;
}

function fetch_url(string $url):string{
    $ch=curl_init();
    curl_setopt_array($ch,[
        CURLOPT_URL=>$url,
        CURLOPT_RETURNTRANSFER=>true,
        CURLOPT_FOLLOWLOCATION=>true,
        CURLOPT_MAXREDIRS=>3,
        CURLOPT_TIMEOUT=>12,
        CURLOPT_USERAGENT=>'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36',
        CURLOPT_SSL_VERIFYPEER=>false,
        CURLOPT_HTTPHEADER=>['Accept: text/html,*/*','Accept-Language: en-US,en;q=0.5'],
    ]);
    $body=curl_exec($ch);
    $code=curl_getinfo($ch,CURLINFO_HTTP_CODE);
    curl_close($ch);
    if(!$body||$code>=400)return '';
    return $body;
}

function extract_emails(string $html, string $domain):array{
    $emails=[];
    // Direct mailto links — highest confidence
    preg_match_all('/mailto:([a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})/i',$html,$m);
    foreach($m[1] as $e) $emails[]=['email'=>strtolower($e),'confidence'=>'high','source'=>'mailto'];

    // Visible email text
    preg_match_all('/\b([a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})\b/',$html,$m);
    foreach($m[1] as $e){
        $e=strtolower($e);
        // Skip common non-contact emails
        $skip=['example.com','sentry.io','wixpress.com','squarespace.com','wordpress.com',
               'amazonaws.com','googleapis.com','schema.org','w3.org','yoursite.com'];
        $skip_ok=false;
        foreach($skip as $s){ if(str_contains($e,$s)){$skip_ok=true;break;} }
        if(!$skip_ok && !in_array($e,array_column($emails,'email')))
            $emails[]=['email'=>$e,'confidence'=>'medium','source'=>'text'];
    }

    // Prefer emails matching the domain
    usort($emails,function($a,$b) use ($domain){
        $da = str_contains($a['email'],$domain)?0:1;
        $db2= str_contains($b['email'],$domain)?0:1;
        if($da!==$db2) return $da-$db2;
        return $a['confidence']==='high'?-1:1;
    });

    return array_slice($emails,0,3); // return top 3
}

function find_contact_page(string $base_url, string $html):?string{
    // Look for contact page links
    preg_match_all('/href=["\']([^"\']*(?:contact|about|reach)[^"\']*)["\']/',$html,$m,PREG_SET_ORDER);
    foreach($m as $match){
        $href=$match[1];
        if(str_starts_with($href,'http')) return $href;
        if(str_starts_with($href,'/')) return rtrim($base_url,'/').$href;
        return rtrim($base_url,'/').'/'.$href;
    }
    return null;
}

function out(string $msg,string $type='info'):void{
    $c=['info'=>'#6b7280','success'=>'#16a34a','error'=>'#dc2626','head'=>'#3b82f6','warn'=>'#d97706'];
    echo "<div style='color:".($c[$type]??'#6b7280').";font-family:monospace;font-size:12px;padding:1px 0;line-height:1.5'>[".date('H:i:s')."] {$msg}</div>\n";
    flush();
}

// ── STATS ─────────────────────────────────────────────────
$total_with_web  = db()->query("SELECT COUNT(*) FROM attorneys WHERE website IS NOT NULL AND website!='' AND email IS NULL")->fetchColumn();
$total_with_email= db()->query("SELECT COUNT(*) FROM attorneys WHERE email IS NOT NULL")->fetchColumn();
$total_attorneys = db()->query("SELECT COUNT(*) FROM attorneys")->fetchColumn();

$action = $_GET['action'] ?? 'home';
$state  = $_GET['state']  ?? '';
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Email Harvester</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#0f172a;color:#e2e8f0;padding:20px;font-size:13px}
.wrap{max-width:860px;margin:0 auto}
h1{font-size:18px;font-weight:600;color:#f8fafc;margin-bottom:2px}
.sub{color:#64748b;font-size:12px;margin-bottom:20px}
.card{background:#1e293b;border:1px solid #334155;border-radius:10px;padding:18px 22px;margin-bottom:14px}
.card h2{font-size:14px;font-weight:600;color:#f1f5f9;margin-bottom:12px}
.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:16px}
.stat{background:#0f172a;border:1px solid #334155;border-radius:8px;padding:12px 14px}
.stat-label{font-size:10px;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:3px}
.stat-val{font-size:22px;font-weight:600;color:#f8fafc}
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:7px;border:none;font-size:13px;font-weight:500;cursor:pointer;text-decoration:none}
.bp{background:#3b82f6;color:#fff}.bs{background:#16a34a;color:#fff}.bg{background:#1e293b;color:#94a3b8;border:1px solid #334155}
.btn:hover{opacity:.85}
.log-box{background:#020617;border:1px solid #1e293b;border-radius:8px;padding:14px;min-height:300px;max-height:600px;overflow-y:auto}
select{background:#0f172a;border:1px solid #334155;border-radius:6px;padding:7px 10px;font-size:13px;color:#e2e8f0;outline:none}
table{width:100%;border-collapse:collapse}
th{text-align:left;font-size:10px;color:#64748b;text-transform:uppercase;letter-spacing:.05em;padding:6px 8px;border-bottom:1px solid #334155}
td{padding:7px 8px;border-bottom:1px solid #1e293b;color:#cbd5e1;font-size:12px}
.ok{color:#4ade80}.err{color:#f87171}
</style></head><body>
<div class="wrap">
<h1>📧 Attorney Email Harvester</h1>
<p class="sub">Visits attorney websites to find and store contact emails</p>

<div class="grid">
  <div class="stat"><div class="stat-label">Have Website, No Email</div><div class="stat-val"><?=number_format($total_with_web)?></div></div>
  <div class="stat"><div class="stat-label">Emails Harvested</div><div class="stat-val"><?=number_format($total_with_email)?></div></div>
  <div class="stat"><div class="stat-label">Total Attorneys</div><div class="stat-val"><?=number_format($total_attorneys)?></div></div>
</div>

<?php if($action==='home'): ?>
<div class="card">
  <h2>Harvest Settings</h2>
  <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
    <div>
      <label style="font-size:11px;color:#64748b;display:block;margin-bottom:4px">Filter by State</label>
      <select id="state-sel">
        <option value="">All States</option>
        <?php foreach(['CA','NY','TX','PA','WV','NJ'] as $s): ?>
        <option value="<?=$s?>"><?=$s?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div style="margin-top:16px">
      <a href="#" onclick="startHarvest()" class="btn bs">⚡ Start Harvesting (<?=BATCH_SIZE?> at a time)</a>
    </div>
  </div>
  <p style="font-size:11px;color:#64748b;margin-top:10px">
    Visits each attorney's website, checks homepage + /contact page for email addresses.
    Runs <?=BATCH_SIZE?> attorneys per batch. Re-run to continue where you left off.
  </p>
</div>

<!-- RECENTLY FOUND -->
<?php
$recent = db()->query("SELECT full_name,city,bar_state,email,website,last_scraped
                        FROM attorneys WHERE email IS NOT NULL ORDER BY last_scraped DESC LIMIT 15")->fetchAll();
if($recent):
?>
<div class="card">
  <h2>Recently Found Emails</h2>
  <table>
    <tr><th>Name</th><th>Location</th><th>Email</th><th>Website</th></tr>
    <?php foreach($recent as $r): ?>
    <tr>
      <td><?=htmlspecialchars($r['full_name']??'—')?></td>
      <td style="color:#94a3b8"><?=htmlspecialchars($r['city']??'—')?>, <?=$r['bar_state']?></td>
      <td class="ok"><?=htmlspecialchars($r['email'])?></td>
      <td><a href="<?=htmlspecialchars($r['website']??'')?>" target="_blank" style="color:#3b82f6;font-size:11px">🔗 visit</a></td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php endif; ?>

<script>
function startHarvest(){
    const state=document.getElementById('state-sel').value;
    window.location.href='?action=harvest&state='+state;
}
</script>

<?php elseif($action==='harvest'): ?>
<div class="card">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
    <h2>Harvesting Emails<?=$state?" — {$state}":''?></h2>
    <a href="?action=home" class="btn bg">← Back</a>
  </div>
  <div class="log-box">
<?php
// Get attorneys with website but no email
$where = ["website IS NOT NULL","website != ''","email IS NULL","bar_status='active'"];
$params=[];
if($state){ $where[]="bar_state=?"; $params[]=$state; }
$sql="SELECT id,full_name,first_name,last_name,city,bar_state,website FROM attorneys
      WHERE ".implode(' AND ',$where)." ORDER BY google_rating DESC LIMIT ".BATCH_SIZE;
$st=db()->prepare($sql);$st->execute($params);
$attorneys=$st->fetchAll();

out("Found ".count($attorneys)." attorneys to check",'head');

$found=0;$skipped=0;$errors=0;

foreach($attorneys as $a){
    $name=htmlspecialchars(trim(($a['first_name']??'').(' '.($a['last_name']??''))));
    $url =$a['website'];

    // Normalize URL
    if(!str_starts_with($url,'http')) $url='https://'.$url;
    $url=rtrim($url,'/');

    usleep(REQUEST_DELAY);
    out("Checking: <span style='color:#93c5fd'>{$name}</span> → ".htmlspecialchars($url));

    $html=fetch_url($url);
    if(empty($html)){
        out("  ↷ No response",'warn');
        $skipped++;
        continue;
    }

    // Parse domain from URL for confidence scoring
    $domain=parse_url($url,PHP_URL_HOST)?:'';
    $domain=preg_replace('/^www\./i','',$domain);

    $emails=extract_emails($html,$domain);

    // If no email found on homepage, try /contact
    if(empty($emails)){
        $contact_url=find_contact_page($url,$html);
        if($contact_url){
            usleep(400000);
            $contact_html=fetch_url($contact_url);
            if($contact_html) $emails=extract_emails($contact_html,$domain);
        }
    }

    if(empty($emails)){
        out("  ✗ No email found",'info');
        // Mark as checked so we skip next time
        db()->prepare("UPDATE attorneys SET last_scraped=NOW() WHERE id=?")->execute([$a['id']]);
        $skipped++;
        continue;
    }

    $best=$emails[0]['email'];

    try{
        db()->prepare("UPDATE attorneys SET email=?,last_scraped=NOW() WHERE id=?")
           ->execute([$best,$a['id']]);
        out("  ✓ Found: <strong style='color:#4ade80'>{$best}</strong> (confidence: {$emails[0]['confidence']})",'success');
        $found++;
    }catch(Exception $e){
        out("  ✗ DB: ".$e->getMessage(),'error');
        $errors++;
    }
}

out("","info");
out("━━━ Complete: Found <strong style='color:#4ade80'>{$found}</strong> | Skipped {$skipped} | Errors {$errors} ━━━",'head');
$new_total=db()->query("SELECT COUNT(*) FROM attorneys WHERE email IS NOT NULL")->fetchColumn();
out("Total emails in DB: <strong style='color:#93c5fd'>".number_format($new_total)."</strong>",'head');
?>
  </div>
</div>
<div style="display:flex;gap:10px;margin-top:8px">
  <a href="?action=home" class="btn bg">← Back</a>
  <a href="?action=harvest&state=<?=htmlspecialchars($state)?>" class="btn bs">▶ Run Next Batch</a>
</div>
<?php endif; ?>

</div></body></html>
