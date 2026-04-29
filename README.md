#  Attorney Directory Scraper

A PHP-based admin toolset that pulls attorney data from the **Google Places API** across all 50 states, harvests contact emails from each attorney's website, and stores everything in MySQL. Powers a public-facing attorney directory focused on divorce and family law.

---

## What it does

- Searches Google Places using 60+ family law keywords × cities in every state
- Pulls name, address, phone, website, rating, and review count per attorney
- Visits each attorney's website to find a contact email (checks homepage + `/contact`)
- Deduplicates by `google_place_id` and upserts on re-runs
- Tags each record with relevant practice areas (custody, alimony, paternity, etc.)

---

## Tech stack

- **PHP 8+** — scraper, email harvester, HTTP Basic Auth gate
- **MySQL / MariaDB** — 3-table schema (attorneys, practice_areas, attorney_practices)
- **Google Places API** — Text Search + Place Details
- **cURL** — website fetching for email harvesting

---

## Admin files

| File | URL |
|------|-----|
| `admin/scraper.php` | Main scraper UI — pick a state and run |
| `admin/email_harvester.php` | Backfill emails on existing records |
| `admin/auth.php` | HTTP Basic Auth (included by both above) |

All admin routes are protected by HTTP Basic Auth.

---

## Quick start

```bash
# 1. Import the 3-table schema
mysql -u root -p attorneys < scraper_schema.sql

# 2. Create the DB user
mysql -u root -p -e "
  CREATE USER 'divorce'@'localhost' IDENTIFIED BY 'YOUR_PASSWORD';
  GRANT ALL PRIVILEGES ON attorneys.* TO 'divorce'@'localhost';
  FLUSH PRIVILEGES;
"

# 3. Add your credentials to the three admin files
#    admin/auth.php     → $admin_user / $admin_pass
#    admin/scraper.php  → DB_PASS, GOOGLE_KEY
#    admin/email_harvester.php → DB_PASS
```

Then open `https://your-site.com/admin/scraper.php` and click a state.

Full setup instructions: [SCRAPER_SETUP.md](SCRAPER_SETUP.md)

---

## Google Places API

Enable these in Google Cloud Console:
- Places API (Text Search)
- Places API (Place Details)

Restrict the key to your server's IP. Set a monthly billing cap before running full 50-state scrapes.

---

## Security

- Credentials are **not** committed — replace all placeholder values before deploying
- `scraper_schema.sql` contains no data, only schema — safe to commit
- Move `dump.sql` (if present) above web root or delete after import
- `admin/` directory should have `Options -Indexes` to prevent listing
