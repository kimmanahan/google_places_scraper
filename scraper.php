<?php
require __DIR__ . '/auth.php';
/**
 * AttorneyFinder — Google Places Scraper v2
 * All 50 states · Email harvesting built in
 */
/* the below connects to Maria DB on Apache server. Change values & add as needed. Also add your own Google key*/
define('DB_HOST', 'localhost');
define('DB_NAME', 'YOUR_DB');
define('DB_USER', 'YOUR_USER');
define('DB_PASS', 'YOUR_PASS');
define('GOOGLE_KEY', 'YOUR_KEY');
define('REQUEST_DELAY', 600000); // 0.6s between Places API calls
define('WEB_DELAY',     400000); // 0.4s between website fetches

// ── All 50 states + top cities ────────────────────────────
$STATES = [
    'AL'=>['Birmingham','Montgomery','Huntsville','Mobile','Tuscaloosa','Dothan','Decatur','Auburn','Florence','Gadsden','Anniston','Phenix City'],
    'AK'=>['Anchorage','Fairbanks','Juneau','Sitka','Ketchikan'],
    'AZ'=>['Phoenix','Tucson','Scottsdale','Mesa','Chandler','Gilbert','Tempe'],
    'AR'=>['Little Rock','Fort Smith','Fayetteville','Springdale','Jonesboro','Conway','Rogers','Pine Bluff','Bentonville','Hot Springs','Texarkana','Russellville'],
    'CA'=>['Los Angeles','San Francisco','San Diego','Sacramento','San Jose','Oakland','Fresno','Long Beach','Bakersfield','Anaheim','Santa Ana','Riverside','Irvine','Modesto','Stockton'],
    'CO'=>['Denver','Colorado Springs','Aurora','Fort Collins','Boulder','Lakewood'],
    'CT'=>['Bridgeport','New Haven','Hartford','Stamford','Waterbury','Norwalk'],
    'DE'=>['Wilmington','Dover','Newark','Middletown','Smyrna'],
    'FL'=>['Miami','Orlando','Tampa','Jacksonville','Fort Lauderdale','St Petersburg','Hialeah','Tallahassee','Fort Myers','Boca Raton'],
    'GA'=>['Atlanta','Augusta','Columbus','Macon','Savannah','Athens','Sandy Springs'],
    'HI'=>['Honolulu','Pearl City','Hilo','Kailua','Waipahu'],
    'ID'=>['Boise','Meridian','Nampa','Idaho Falls','Pocatello'],
    'IL'=>['Chicago','Aurora','Naperville','Joliet','Rockford','Springfield','Peoria'],
    'IN'=>['Indianapolis','Fort Wayne','Evansville','South Bend','Carmel','Fishers','Bloomington','Hammond','Gary','Muncie','Terre Haute','Anderson','Kokomo','Lafayette'],
    'IA'=>['Des Moines','Cedar Rapids','Davenport','Sioux City','Iowa City'],
    'KS'=>['Wichita','Overland Park','Kansas City','Olathe','Topeka','Lawrence'],
    'KY'=>['Louisville','Lexington','Bowling Green','Owensboro','Covington','Richmond','Florence','Georgetown','Henderson','Elizabethtown','Frankfort','Hopkinsville','Nicholasville','Paducah'],
    'LA'=>['New Orleans','Baton Rouge','Shreveport','Metairie','Lafayette','Lake Charles','Kenner','Bossier City','Monroe','Alexandria','Houma','Marrero','Hammond','Prairieville'],
    'ME'=>['Portland','Lewiston','Bangor','South Portland','Auburn','Biddeford','Sanford','Augusta','Saco','Westbrook','Waterville','Brewer','Presque Isle','Bath'],
    'MD'=>['Baltimore','Frederick','Rockville','Gaithersburg','Bowie','Silver Spring'],
    'MA'=>['Boston','Worcester','Springfield','Cambridge','Lowell','Brockton'],
    'MI'=>['Detroit','Grand Rapids','Warren','Sterling Heights','Lansing','Ann Arbor','Flint','Dearborn','Livonia','Clinton Township','Westland','Troy','Farmington Hills','Kalamazoo','Wyoming','Pontiac','Saginaw','Muskegon'],
    'MN'=>['Minneapolis','Saint Paul','Rochester','Duluth','Bloomington','Plymouth'],
    'MS'=>['Jackson','Gulfport','Southaven','Hattiesburg','Biloxi','Meridian','Tupelo','Greenville','Olive Branch','Horn Lake','Clinton','Pearl','Madison','Ridgeland'],
    'MO'=>['Kansas City','Saint Louis','Springfield','Columbia','Independence','Lee\'s Summit','O\'Fallon','St Joseph','St Charles','Blue Springs','Joplin','Florissant','Jefferson City','Cape Girardeau'],
    'MT'=>['Billings','Missoula','Great Falls','Bozeman','Butte'],
    'NE'=>['Omaha','Lincoln','Bellevue','Grand Island','Kearney'],
    'NV'=>['Las Vegas','Henderson','Reno','North Las Vegas','Sparks'],
    'NH'=>['Manchester','Nashua','Concord','Derry','Dover','Rochester'],
    'NJ'=>['Newark','Jersey City','Paterson','Elizabeth','Trenton','Camden','Edison','Woodbridge'],
    'NM'=>['Albuquerque','Las Cruces','Rio Rancho','Santa Fe','Roswell'],
    'NY'=>['New York City','Brooklyn','Queens','Buffalo','Rochester','Yonkers','Syracuse','Albany'],
    'NC'=>['Charlotte','Raleigh','Greensboro','Durham','Winston-Salem','Fayetteville'],
    'ND'=>['Fargo','Bismarck','Grand Forks','Minot','West Fargo'],
    'OH'=>['Columbus','Cleveland','Cincinnati','Toledo','Akron','Dayton','Parma','Canton','Youngstown','Lorain','Hamilton','Springfield','Kettering','Elyria','Lakewood','Cuyahoga Falls','Middletown','Newark','Mansfield'],
    'OK'=>['Oklahoma City','Tulsa','Norman','Broken Arrow','Lawton','Edmond'],
    'OR'=>['Portland','Salem','Eugene','Gresham','Hillsboro','Beaverton'],
    'PA'=>['Philadelphia','Pittsburgh','Allentown','Erie','Coatesville','Reading','Scranton','Lancaster','Harrisburg'],
    'RI'=>['Providence','Cranston','Warwick','Pawtucket','East Providence'],
    'SC'=>['Charleston','Columbia','North Charleston','Mount Pleasant','Rock Hill'],
    'SD'=>['Sioux Falls','Rapid City','Aberdeen','Brookings','Watertown'],
    'TN'=>['Nashville','Memphis','Knoxville','Chattanooga','Clarksville','Murfreesboro'],
    'TX'=>['Houston','Dallas','Austin','San Antonio','Fort Worth','El Paso','Arlington','Corpus Christi','Plano','Laredo','Lubbock','Irving'],
    'UT'=>['Salt Lake City','West Valley City','Provo','West Jordan','Orem','Sandy'],
    'VT'=>['Burlington','South Burlington','Rutland','Barre','Montpelier'],
    'VA'=>['Virginia Beach','Norfolk','Chesapeake','Richmond','Newport News','Alexandria'],
    'WA'=>['Seattle','Spokane','Tacoma','Vancouver','Bellevue','Kirkland','Redmond'],
    'WV'=>['Charleston','Huntington','Morgantown','Parkersburg','Wheeling','Weirton','Fairmont','Martinsburg','Beckley','Clarksburg','South Charleston','St Albans','Vienna','Bluefield','Lewisburg'],
    'WI'=>['Milwaukee','Madison','Green Bay','Kenosha','Racine','Appleton'],
    'WY'=>['Cheyenne','Casper','Laramie','Gillette','Rock Springs','Sheridan','Logan','Evanston','Riverton','Jackson'],
];

$ALL_STATE_NAMES = [
    'AL'=>'Alabama','AK'=>'Alaska','AZ'=>'Arizona','AR'=>'Arkansas','CA'=>'California',
    'CO'=>'Colorado','CT'=>'Connecticut','DE'=>'Delaware','FL'=>'Florida','GA'=>'Georgia',
    'HI'=>'Hawaii','ID'=>'Idaho','IL'=>'Illinois','IN'=>'Indiana','IA'=>'Iowa',
    'KS'=>'Kansas','KY'=>'Kentucky','LA'=>'Louisiana','ME'=>'Maine','MD'=>'Maryland',
    'MA'=>'Massachusetts','MI'=>'Michigan','MN'=>'Minnesota','MS'=>'Mississippi','MO'=>'Missouri',
    'MT'=>'Montana','NE'=>'Nebraska','NV'=>'Nevada','NH'=>'New Hampshire','NJ'=>'New Jersey',
    'NM'=>'New Mexico','NY'=>'New York','NC'=>'North Carolina','ND'=>'North Dakota','OH'=>'Ohio',
    'OK'=>'Oklahoma','OR'=>'Oregon','PA'=>'Pennsylvania','RI'=>'Rhode Island','SC'=>'South Carolina',
    'SD'=>'South Dakota','TN'=>'Tennessee','TX'=>'Texas','UT'=>'Utah','VT'=>'Vermont',
    'VA'=>'Virginia','WA'=>'Washington','WV'=>'West Virginia','WI'=>'Wisconsin','WY'=>'Wyoming',
];

$SEARCH_TERMS = [
    // Core divorce
    'divorce attorney',
    'divorce lawyer',
    'uncontested divorce attorney',
    'contested divorce lawyer',
    'no fault divorce attorney',
    'divorce filing attorney',
    'divorce mediation attorney',
    'cheap divorce lawyer',
    'affordable divorce attorney',
    'online divorce attorney',

    // Family law
    'family law attorney',
    'family law lawyer',
    'family court attorney',
    'family lawyer',

    // Child custody
    'child custody lawyer',
    'child custody attorney',
    'custody modification attorney',
    'emergency custody attorney',
    'sole custody lawyer',
    'joint custody lawyer',
    'grandparent custody attorney',
    'parental rights attorney',
    'visitation rights lawyer',
    'parenting plan attorney',

    // Child support
    'child support lawyer',
    'child support attorney',
    'child support modification attorney',
    'child support enforcement lawyer',

    // Alimony / spousal support
    'alimony lawyer',
    'alimony attorney',
    'spousal support attorney',
    'spousal support lawyer',
    'spousal maintenance attorney',

    // Property / assets
    'property division attorney',
    'property law attorney',
    'marital property lawyer',
    'asset division attorney',
    'high net worth divorce attorney',
    'business valuation divorce attorney',
    'real estate divorce attorney',
    'retirement asset division lawyer',
    'QDRO attorney',

    // Protective orders
    'domestic violence attorney',
    'restraining order lawyer',
    'protective order attorney',
    'order of protection attorney',

    // Mediation / collaborative
    'legal mediation',
    'mediation attorney',
    'divorce mediator',
    'collaborative divorce attorney',
    'collaborative law attorney',

    // Prenup / postnup
    'prenuptial agreement attorney',
    'prenup lawyer',
    'postnuptial agreement attorney',

    // Paternity
    'paternity attorney',
    'paternity lawyer',
    'father\'s rights attorney',
    'father\'s rights lawyer',
    'mother\'s rights attorney',

    // Annulment / separation
    'annulment attorney',
    'legal separation attorney',
    'separation agreement lawyer',

    // Military / interstate
    'military divorce attorney',
    'interstate custody attorney',
    'relocation custody attorney',
];

set_time_limit(0);
ini_set('max_execution_time', 0);
ini_set('memory_limit', '256M');
ob_implicit_flush(true);
if (ob_get_level()) ob_end_flush();

// ── DB ────────────────────────────────────────────────────
function db(): PDO {
    static $pdo = null;
    if (!$pdo) $pdo = new PDO(
        sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_NAME),
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]
    );
    return $pdo;
}

// ── Google Places API ─────────────────────────────────────
function places_search(string $query, ?string $token=null): ?array {
    $p = ['query'=>$query,'type'=>'lawyer','key'=>GOOGLE_KEY];
    if ($token) $p['pagetoken'] = $token;
    $ch = curl_init();
    curl_setopt_array($ch,[
        CURLOPT_URL=>'https://maps.googleapis.com/maps/api/place/textsearch/json?'.http_build_query($p),
        CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>15,CURLOPT_SSL_VERIFYPEER=>false,
    ]);
    $r = json_decode(curl_exec($ch),true);
    curl_close($ch);
    return $r;
}

function place_details(string $pid): ?array {
    $ch = curl_init();
    curl_setopt_array($ch,[
        CURLOPT_URL=>'https://maps.googleapis.com/maps/api/place/details/json?'.http_build_query([
            'place_id'=>$pid,
            'fields'=>'name,formatted_address,formatted_phone_number,website,address_components,rating,user_ratings_total,business_status',
            'key'=>GOOGLE_KEY,
        ]),
        CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>15,CURLOPT_SSL_VERIFYPEER=>false,
    ]);
    $r = json_decode(curl_exec($ch),true);
    curl_close($ch);
    return $r['result'] ?? null;
}

// ── Email harvester ───────────────────────────────────────
function harvest_email(string $url): ?string {
    if (empty($url)) return null;
    if (!str_starts_with($url,'http')) $url = 'https://'.$url;
    $url = rtrim($url,'/');

    $html = fetch_url($url);
    if (!$html) return null;

    $email = extract_best_email($html, $url);
    if ($email) return $email;

    // Try /contact page
    $contact = find_contact_url($html, $url);
    if ($contact) {
        usleep(WEB_DELAY);
        $html2 = fetch_url($contact);
        if ($html2) $email = extract_best_email($html2, $url);
    }
    return $email;
}

function fetch_url(string $url): string {
    $ch = curl_init();
    curl_setopt_array($ch,[
        CURLOPT_URL=>$url,
        CURLOPT_RETURNTRANSFER=>true,
        CURLOPT_FOLLOWLOCATION=>true,
        CURLOPT_MAXREDIRS=>3,
        CURLOPT_TIMEOUT=>10,
        CURLOPT_USERAGENT=>'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36',
        CURLOPT_SSL_VERIFYPEER=>false,
        CURLOPT_HTTPHEADER=>['Accept: text/html,*/*','Accept-Language: en-US,en;q=0.5'],
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($body && $code < 400) ? $body : '';
}

function extract_best_email(string $html, string $url): ?string {
    $domain = preg_replace('/^www\./i','',parse_url($url,PHP_URL_HOST)?:'');
    $emails = [];

    // mailto links — highest confidence
    preg_match_all('/mailto:([a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})/i',$html,$m);
    foreach ($m[1] as $e) $emails[] = ['e'=>strtolower($e),'score'=>10];

    // Text emails
    preg_match_all('/\b([a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})\b/',$html,$m);
    $skip = ['example.com','sentry.io','wixpress.com','squarespace.com','wordpress.com',
             'amazonaws.com','googleapis.com','schema.org','w3.org','cloudflare.com',
             'yoursite.com','domain.com','email.com','youremail.com'];
    foreach ($m[1] as $e) {
        $e = strtolower($e);
        $ok = true;
        foreach ($skip as $s) { if (str_contains($e,$s)){$ok=false;break;} }
        if (!$ok) continue;
        $score = str_contains($e,$domain) ? 8 : 3;
        // Boost contact/info emails
        if (preg_match('/^(contact|info|hello|office|admin|legal|law)@/',$e)) $score += 3;
        $emails[] = ['e'=>$e,'score'=>$score];
    }

    if (empty($emails)) return null;
    usort($emails, fn($a,$b)=>$b['score']<=>$a['score']);
    return $emails[0]['e'];
}

function find_contact_url(string $html, string $base): ?string {
    preg_match_all('/href=["\']([^"\']*(?:contact|about|reach)[^"\']*)["\']/',$html,$m);
    foreach ($m[1] as $href) {
        if (str_starts_with($href,'http')) return $href;
        if (str_starts_with($href,'/')) return rtrim($base,'/').$href;
    }
    return null;
}

// ── Parse Google result ───────────────────────────────────
function parse_place(array $r, array $d, string $state): array {
    $city=$zip=$street=$num='';
    foreach ($d['address_components']??[] as $c) {
        $t=$c['types'];
        if (in_array('street_number',$t)) $num  = $c['long_name'];
        if (in_array('route',$t))         $street= $c['long_name'];
        if (in_array('locality',$t))      $city  = $c['long_name'];
        if (in_array('postal_code',$t))   $zip   = $c['long_name'];
    }
    $address = trim("$num $street");
    $name    = $d['name'] ?? $r['name'] ?? '';
    $first=''; $last=$name;
    if (preg_match('/^([A-Z][a-z]+)\s+([A-Z][a-z]+)(?:\s+(?:Law|Attorney|Lawyer|Esq|LLC|PLLC))?$/i',$name,$m)) {
        $first=$m[1]; $last=$m[2];
    } elseif (preg_match('/^([A-Z][a-z]+(?:\s+[A-Z]\.?)?)\s+([A-Z][a-z]+),?\s*(?:Esq|JD|Attorney)?\.?$/i',$name,$m)) {
        $first=trim($m[1]); $last=trim($m[2]);
    }
    return [
        'first_name'     => $first?:null,
        'last_name'      => $last,
        'bar_number'     => 'gp-'.($r['place_id']??uniqid()),
        'bar_state'      => $state,
        'bar_status'     => 'active',
        'phone'          => $d['formatted_phone_number']??null,
        'website'        => $d['website']??null,
        'address_line1'  => $address?:null,
        'city'           => $city?:null,
        'state'          => $state,
        'zip'            => $zip?:null,
        'google_place_id'=> $r['place_id']??null,
        'google_rating'  => $r['rating']??null,
        'google_reviews' => $r['user_ratings_total']??0,
        'photo_url'      => isset($r['photos'][0]['photo_reference'])
            ? 'https://maps.googleapis.com/maps/api/place/photo?maxwidth=400&photo_reference='.$r['photos'][0]['photo_reference'].'&key='.GOOGLE_KEY
            : null,
        'practice_areas' => ['Divorce','Child Custody','Alimony & Spousal Support'],
    ];
}

// ── DB helpers ────────────────────────────────────────────
function make_slug(string $f,string $l,string $c,string $s):string{
    return trim(preg_replace('/-+/','-',preg_replace('/[^a-z0-9]+/','-',strtolower("$f $l $c $s"))),'-');
}
function unique_slug(string $base):string{
    $slug=$base;$n=2;
    while(true){
        $st=db()->prepare("SELECT id FROM attorneys WHERE slug=?");
        $st->execute([$slug]);
        if(!$st->fetch())return $slug;
        $slug="$base-$n";$n++;
    }
}
function upsert_attorney(array $data):?int{
    if(!empty($data['bar_number'])){
        $st=db()->prepare("SELECT id FROM attorneys WHERE bar_number=?");
        $st->execute([$data['bar_number']]);
        if($row=$st->fetch()){
            db()->prepare("UPDATE attorneys SET
                phone=?,website=?,address_line1=?,city=?,state=?,zip=?,
                google_place_id=?,google_rating=?,google_reviews=?,photo_url=?,
                email=COALESCE(?,email),last_scraped=NOW() WHERE id=?")
               ->execute([
                $data['phone']??null,$data['website']??null,$data['address_line1']??null,
                $data['city']??null,$data['state']??null,$data['zip']??null,
                $data['google_place_id']??null,$data['google_rating']??null,
                $data['google_reviews']??0,$data['photo_url']??null,
                $data['email']??null,$row['id'],
            ]);
            return $row['id'];
        }
    }
    if(empty($data['last_name']))return null;
    $slug=unique_slug(make_slug($data['first_name']??'',$data['last_name'],$data['city']??'',$data['bar_state']));
    db()->prepare("INSERT INTO attorneys
        (first_name,last_name,slug,bar_number,bar_state,bar_status,phone,email,
         website,address_line1,city,state,zip,google_place_id,google_rating,
         google_reviews,photo_url,data_source,scrape_source,last_scraped)
        VALUES(?,?,?,?,?,'active',?,?,?,?,?,?,?,?,?,?,?,'bar_scrape','google_places',NOW())")
       ->execute([
        $data['first_name']??null,$data['last_name'],$slug,$data['bar_number'],
        $data['bar_state'],$data['phone']??null,$data['email']??null,
        $data['website']??null,$data['address_line1']??null,$data['city']??null,
        $data['state']??null,$data['zip']??null,$data['google_place_id']??null,
        $data['google_rating']??null,$data['google_reviews']??0,$data['photo_url']??null,
    ]);
    return (int)db()->lastInsertId();
}
function link_practices(int $aid,array $areas):void{
    foreach($areas as $n){
        $st=db()->prepare("SELECT id FROM practice_areas WHERE name LIKE ?");
        $st->execute(["%$n%"]);
        if($row=$st->fetch())
            db()->prepare("INSERT IGNORE INTO attorney_practices (attorney_id,practice_id,is_primary) VALUES(?,?,0)")
               ->execute([$aid,$row['id']]);
    }
}

// ── Output ────────────────────────────────────────────────
function out(string $msg,string $type='info'):void{
    $c=['info'=>'#6b7280','success'=>'#16a34a','error'=>'#dc2626','head'=>'#3b82f6','warn'=>'#d97706','email'=>'#a855f7'];
    echo "<div style='color:".($c[$type]??'#6b7280').";font-family:monospace;font-size:12px;padding:1px 0;line-height:1.5'>[".date('H:i:s')."] {$msg}</div>\n";
    flush();
}
function stat_row(int $saved,int $skipped,int $errors,int $emails):void{
    echo "<div style='background:#0f172a;border:1px solid #1e3a5f;border-radius:4px;padding:5px 12px;
          font-family:monospace;font-size:12px;margin:3px 0;display:flex;gap:20px'>
          <span style='color:#4ade80'>✓ {$saved} saved</span>
          <span style='color:#94a3b8'>↷ {$skipped} skipped</span>
          <span style='color:#f87171'>✗ {$errors} errors</span>
          <span style='color:#a855f7'>📧 {$emails} emails</span></div>\n";
    flush();
}

// ── Routing ───────────────────────────────────────────────
$action = $_GET['action'] ?? 'home';
$state  = strtoupper($_GET['state'] ?? 'CA');
$term   = $_GET['term']   ?? 'ALL';
$harvest_email_flag = ($_GET['harvest_email']??'1') === '1';
if (!isset($STATES[$state])) $state = 'CA';

?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>AttorneyFinder — Google Scraper v2</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#0f172a;color:#e2e8f0;padding:20px}
.wrap{max-width:960px;margin:0 auto}
h1{font-size:18px;font-weight:600;color:#f8fafc;margin-bottom:2px}
.sub{color:#64748b;font-size:12px;margin-bottom:20px}
.card{background:#1e293b;border:1px solid #334155;border-radius:10px;padding:18px 22px;margin-bottom:14px}
.card h2{font-size:14px;font-weight:600;color:#f1f5f9;margin-bottom:12px}
.grid4{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:16px}
.stat{background:#0f172a;border:1px solid #334155;border-radius:8px;padding:12px 14px}
.stat-label{font-size:10px;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:3px}
.stat-val{font-size:22px;font-weight:600;color:#f8fafc}
.state-grid{display:grid;grid-template-columns:repeat(10,1fr);gap:6px;margin-bottom:16px}
.sc{background:#0f172a;border:1px solid #334155;border-radius:6px;padding:8px 6px;text-align:center;
    text-decoration:none;display:block;transition:border-color .15s;cursor:pointer}
.sc:hover,.sc.active{border-color:#3b82f6}
.sc-abbr{font-size:13px;font-weight:700;color:#f8fafc}
.sc-cnt{font-size:10px;color:#4ade80;margin-top:2px}
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:7px;
     border:none;font-size:13px;font-weight:500;cursor:pointer;text-decoration:none;transition:opacity .15s}
.btn:hover{opacity:.85}
.bp{background:#3b82f6;color:#fff}.bs{background:#16a34a;color:#fff}
.bg{background:#1e293b;color:#94a3b8;border:1px solid #334155}
.bv{background:#7c3aed;color:#fff}
.log-box{background:#020617;border:1px solid #1e293b;border-radius:8px;padding:14px;
         min-height:300px;max-height:600px;overflow-y:auto}
table{width:100%;border-collapse:collapse;font-size:12px}
th{text-align:left;font-size:10px;color:#64748b;text-transform:uppercase;letter-spacing:.05em;padding:6px 8px;border-bottom:1px solid #334155}
td{padding:7px 8px;border-bottom:1px solid #1e293b;color:#cbd5e1}
.tag{background:#1e3a5f;color:#93c5fd;border:1px solid #1d4ed8;border-radius:4px;padding:3px 9px;font-size:11px;display:inline-block;margin:2px}
label{font-size:12px;color:#94a3b8;display:flex;align-items:center;gap:6px;cursor:pointer}
input[type=checkbox]{accent-color:#3b82f6}
</style>
</head>
<body>
<div class="wrap">
<h1>⚖️ AttorneyFinder — Google Places Scraper v2</h1>
<p class="sub">All 50 states · Email harvesting built in · Real attorney data from Google</p>

<?php
try { $db = db(); } catch(Exception $e) {
    echo "<div class='card' style='color:#f87171'>DB Error: {$e->getMessage()}</div>"; die();
}
$total   = db()->query("SELECT COUNT(*) FROM attorneys")->fetchColumn();
$today   = db()->query("SELECT COUNT(*) FROM attorneys WHERE DATE(last_scraped)=CURDATE()")->fetchColumn();
$cities  = db()->query("SELECT COUNT(DISTINCT city) FROM attorneys WHERE city IS NOT NULL")->fetchColumn();
$emails  = db()->query("SELECT COUNT(*) FROM attorneys WHERE email IS NOT NULL")->fetchColumn();
$st_rows = db()->query("SELECT bar_state,COUNT(*) c FROM attorneys WHERE city IS NOT NULL GROUP BY bar_state")->fetchAll();
$state_counts = array_column($st_rows,'c','bar_state');
?>

<div class="grid4">
  <div class="stat"><div class="stat-label">Total attorneys</div><div class="stat-val"><?=number_format($total)?></div></div>
  <div class="stat"><div class="stat-label">Added today</div><div class="stat-val"><?=number_format($today)?></div></div>
  <div class="stat"><div class="stat-label">Cities covered</div><div class="stat-val"><?=number_format($cities)?></div></div>
  <div class="stat"><div class="stat-label" style="color:#a855f7">📧 Emails harvested</div><div class="stat-val" style="color:#a855f7"><?=number_format($emails)?></div></div>
</div>

<!-- ALL 50 STATE CARDS -->
<div class="card">
  <h2>Select State</h2>
  <div class="state-grid">
    <?php foreach($STATES as $s=>$cities_arr): ?>
    <a href="?action=select&state=<?=$s?>" class="sc <?=$state===$s&&$action!=='home'?'active':''?>">
      <div class="sc-abbr"><?=$s?></div>
      <div class="sc-cnt"><?=$state_counts[$s]??0?></div>
    </a>
    <?php endforeach; ?>
  </div>
</div>

<?php if ($action === 'home'): ?>
<div class="card" style="color:#94a3b8;font-size:13px">
  👆 Click any state to configure and launch. Scraper pulls up to 60 results per search query and
  attempts to harvest contact emails from each attorney's website automatically.
</div>

<?php elseif ($action === 'select'):
  $state_name = $ALL_STATE_NAMES[$state]??$state;
  $cities_list = $STATES[$state];
?>
<div class="card">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px">
    <h2><?=$state_name?> — <?=count($cities_list)?> cities · <?=count($SEARCH_TERMS)?> terms</h2>
    <a href="?action=home" class="btn bg">← Back</a>
  </div>
  <div style="margin-bottom:10px">
    <?php foreach($cities_list as $c): ?><span class="tag"><?=$c?></span><?php endforeach; ?>
  </div>
  <div style="margin-bottom:14px">
    <?php foreach($SEARCH_TERMS as $t): ?><span class="tag" style="background:#1a2e05;color:#86efac;border-color:#166534"><?=$t?></span><?php endforeach; ?>
  </div>
  <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
    <a href="?action=scrape&state=<?=$state?>&term=ALL&harvest_email=1" class="btn bs">⚡ Full scrape + harvest emails</a>
    <a href="?action=scrape&state=<?=$state?>&term=divorce+attorney&harvest_email=1" class="btn bp">▶ Test — first city only</a>
    <a href="?action=scrape&state=<?=$state?>&term=ALL&harvest_email=0" class="btn bg">Quick — no email harvest</a>
  </div>
  <p style="font-size:11px;color:#64748b;margin-top:10px">
    Est. records: <?=number_format(count($cities_list)*count($SEARCH_TERMS)*40)?>
    · Email harvest visits each attorney website (+0.4s per attorney)
    · Estimated time with emails: <?=round(count($cities_list)*count($SEARCH_TERMS)*40*1.0/60,1)?> min
  </p>
</div>

<?php if(($state_counts[$state]??0)>0):
  $recent=db()->prepare("SELECT full_name,city,phone,email,google_rating,last_scraped FROM attorneys WHERE bar_state=? ORDER BY last_scraped DESC LIMIT 12");
  $recent->execute([$state]);$rows=$recent->fetchAll();
?>
<div class="card">
  <h2>Recent — <?=$state_name?></h2>
  <table>
    <tr><th>Name</th><th>City</th><th>Phone</th><th>Email</th><th>Rating</th><th>Added</th></tr>
    <?php foreach($rows as $r): ?>
    <tr>
      <td><?=htmlspecialchars($r['full_name']??'—')?></td>
      <td style="color:#94a3b8"><?=htmlspecialchars($r['city']??'—')?></td>
      <td style="color:#94a3b8"><?=htmlspecialchars($r['phone']??'—')?></td>
      <td style="color:#a855f7"><?=$r['email']?'📧 '.htmlspecialchars($r['email']):'—'?></td>
      <td><?=$r['google_rating']?'⭐ '.$r['google_rating']:'—'?></td>
      <td style="color:#475569"><?=$r['last_scraped']?date('g:ia',strtotime($r['last_scraped'])):'—'?></td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php endif; ?>

<?php elseif ($action === 'scrape'):
  $state_name  = $ALL_STATE_NAMES[$state]??$state;
  $run_term    = $_GET['term']??'ALL';
  $do_email    = ($_GET['harvest_email']??'1')==='1';
  $run_cities  = $STATES[$state];
  $run_terms   = $run_term==='ALL' ? $SEARCH_TERMS : [$run_term];
  // Test mode: first city only
  if ($run_term!=='ALL' && str_contains($run_term,'divorce')) $run_cities = [$run_cities[0]];
?>
<div class="card">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
    <h2>Scraping <?=$state_name?><?=$do_email?' + 📧 Email harvest':''?></h2>
    <div style="display:flex;gap:8px">
      <a href="?action=select&state=<?=$state?>" class="btn bg">← Back</a>
      <a href="?action=home" class="btn bg">⌂ Home</a>
    </div>
  </div>
  <?php if($do_email): ?>
  <div style="font-size:11px;color:#a855f7;margin-bottom:8px">📧 Email harvesting ON — visiting attorney websites to find contact emails</div>
  <?php endif; ?>
  <div class="log-box" id="log">
<?php
$saved=$errors=$skipped=$email_found=0;
$seen=[];

foreach($run_terms as $search_term){
    foreach($run_cities as $city){
        $query="{$search_term} {$city} {$state_name}";
        out("━━━ <strong style='color:#93c5fd'>{$query}</strong> ━━━",'head');
        $page_token=null; $page=0;
        do {
            if($page_token) sleep(2);
            $page++;
            $results=places_search($query,$page_token);
            if(!$results||($results['status']??'')==='REQUEST_DENIED'){
                out("✗ API: ".($results['error_message']??$results['status']??'unknown'),'error');
                break;
            }
            if(($results['status']??'')==='ZERO_RESULTS'){out("No results",'info');break;}
            $places=$results['results']??[];
            out("Page {$page}: ".count($places)." results");
            foreach($places as $place){
                $pid=$place['place_id']??null;
                if(!$pid||isset($seen[$pid])){$skipped++;continue;}
                $seen[$pid]=true;
                usleep(REQUEST_DELAY);
                $detail=place_details($pid);
                if(!$detail){out("✗ No details: ".($place['name']??'?'),'error');$errors++;continue;}
                try{
                    $data=parse_place($place,$detail,$state);
                    $areas=$data['practice_areas'];
                    unset($data['practice_areas']);

                    // ── EMAIL HARVEST ──
                    if($do_email && !empty($data['website'])){
                        usleep(WEB_DELAY);
                        $email=harvest_email($data['website']);
                        if($email){
                            $data['email']=$email;
                            $email_found++;
                        }
                    }

                    $aid=upsert_attorney($data);
                    if($aid){
                        link_practices($aid,$areas);
                        $n=trim(($data['first_name']??'').' '.($data['last_name']??''));
                        $r=$data['google_rating']?" ⭐{$data['google_rating']}":'';
                        $e=isset($data['email'])?" <span style='color:#a855f7'>📧 {$data['email']}</span>":'';
                        out("✓ {$n} — {$data['city']}{$r}{$e}",'success');
                        $saved++;
                    } else $skipped++;
                }catch(Exception $e){
                    out("✗ DB: ".$e->getMessage(),'error');$errors++;
                }
            }
            stat_row($saved,$skipped,$errors,$email_found);
            $page_token=$results['next_page_token']??null;
        } while($page_token&&$page<3);
        usleep(REQUEST_DELAY);
    }
}
$new_total=db()->prepare("SELECT COUNT(*) FROM attorneys WHERE bar_state=?");
$new_total->execute([$state]);$new_total=$new_total->fetchColumn();
$new_emails=db()->prepare("SELECT COUNT(*) FROM attorneys WHERE bar_state=? AND email IS NOT NULL");
$new_emails->execute([$state]);$new_emails=$new_emails->fetchColumn();
out("━━━ <strong style='color:#4ade80'>COMPLETE</strong> ━━━",'head');
out("Saved: <strong style='color:#4ade80'>{$saved}</strong> | Emails: <strong style='color:#a855f7'>{$email_found}</strong> | Skipped: {$skipped} | Errors: {$errors}",'head');
out("{$state_name} total: <strong style='color:#93c5fd'>".number_format($new_total)."</strong> attorneys · <strong style='color:#a855f7'>".number_format($new_emails)."</strong> with emails",'head');
?>
  </div>
</div>
<div style="display:flex;gap:10px;margin-top:8px;flex-wrap:wrap">
  <a href="?action=select&state=<?=$state?>" class="btn bg">← Back to <?=$state_name?></a>
  <a href="?action=home" class="btn bg">⌂ All states</a>
  <a href="?action=scrape&state=<?=$state?>&term=ALL&harvest_email=1" class="btn bs">⚡ Run again</a>
</div>
<?php endif; ?>

</div></body></html>
