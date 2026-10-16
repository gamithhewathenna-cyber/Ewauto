# EW Auto website

Brand name shown on the site is "EW Auto" (formerly "ZXTec" — the repo folder still uses `zxtec`).

Production: https://ewautomobile.com/ — plain PHP + MySQL, no framework, no build step, no package manager, no tests. Deployed to shared Apache hosting (cPanel-style DB names `tcplckfa_*`).

## Stack / conventions
- PHP 7.4+ (arrow fns used), PDO MySQL via `db()` singleton in `includes/db.php`. Always use prepared statements.
- Escape all output with `e()`. Pages use `$c = fn($key, $fallback) => content($content, $key, $fallback)` — every piece of copy has a hard-coded fallback so the page renders even with an empty/unreachable DB (DB calls are wrapped in `try/catch Throwable`).
- Front end: one stylesheet `assets/css/style.css` (CSS vars on `:root`: main `--green #16A05D` (+`-dk`/`-soft`), accent `--accent #F15A24` (orange), `--ink #111315`, `--mist #F5F7F6`, `--mute #7C8587`; fonts Archivo/Inter. Use the vars, never hard-coded brand hex), one script `assets/js/main.js` (vanilla IIFEs: tilt, parallax, sticky header, `.reveal` scroll-in, mobile nav, hero slider, bike carousel, modals, AJAX contact form). CSS is cache-busted with `?v=filemtime`.
- Header/footer markup is duplicated in `index.php`, `about.php`, `contact.php` (no shared partial) — nav/footer changes must be made in all three.
- Clean URLs via `.htaccess`: `/about`, `/contact` → `.php`; `.php` URLs 301 to clean ones. New public pages need rules added there.
- Uploads go to `uploads/` (not in git; lives on the server only). Use `save_uploaded_image()` / `delete_uploaded_image()` from `includes/uploads.php`.

## Public pages
- `index.php` — hero slider (`slides` table, falls back to `hero_scooter` image), intro/lineup, bikes carousel (`bikes` + `bike_colors`; only bikes with ≥1 colour image show), world reach + testimonial, dealers marquee, CTA/stats, footer, bike spec modal.
- `about.php` — story, stats, what we do, vision/mission, banner, CTA.
- `contact.php` — enquiry form, dealers grid, Google Maps iframe from `contact_address`.
- `contact-submit.php` — JSON endpoint for the "Book a Test Ride" popup on every page. Both forms share `handle_contact_submission()` in `includes/contact_form.php` (honeypot `website`, CSRF, PHP `mail()` to `contact_email`, Sri Lankan districts dropdown).
- `includes/social.php` — social media links (settings keys `social_<platform>`, edited in Admin > Settings). `social_platforms()` holds labels + SVG icons; pages call `$socials = social_links()` and `render_social_links($socials[, true])` in header, footer, and contact sidebar. Unset platforms are hidden.
- `includes/maintenance.php` — `maybe_show_maintenance_page()` returns 503 to non-admins when setting `maintenance_mode = 1`.

## Admin (`/admin`, session login, CSRF on every POST)
`layout.php` provides `admin_header()/admin_footer()` + `ADMIN_NAV` (add new screens there; an optional `group` key puts items under a small sidebar heading). Screens: `dashboard`, `content` (page text + image slots; one sidebar item per page - Home / About Us / Contact us - via `content.php?page=home|about|contact`, active key `content_<page>`), `slides`, `bikes` (+ `bike_colors.php` per bike), `dealers`, `settings` (maintenance, site title, favicon/logos, account). Each screen is a single file: POST handler keyed on `action` (`add`/`update`/`delete`/`save_*`) at top, HTML below.

## Data model (`database.sql`)
- `content` / `settings` — key/value. **To add editable copy:** add the field to `includes/content_fields.php` (shows up in admin automatically), then read it in the page with `$c('key', 'fallback')`.
- `images` — named slots (`logo_header`, `favicon`, `about_*`, `contact_hero_image`, …), read via `image_url($images, 'slot')`. New slot = insert a row + reference it (and optionally list it in `content_fields.php`).
- `slides`, `dealers`, `bikes`, `bike_colors` — ordered by `sort_order`, filtered by `active`. Bike spec columns are defined once in `bike_spec_groups()` (db.php); adding a spec = new column in SQL + entry there.
- `products` table exists but is unused.
- Schema changes: append idempotent `ALTER TABLE ... ADD COLUMN IF NOT EXISTS` to `database.sql`, and apply manually on the live DB (no migration tool).

## Known issues
- `includes/config.php` contains real production DB credentials and is committed to git; there is no `.gitignore`. Prefer env vars / an untracked config.
- `README.md` is outdated (describes the original 8-slot image manager and `zxtec` DB).
- Contact mail fallback address is the placeholder `companyname@gamil.com`.

## Local run
`php -S localhost:8080` from the repo root (clean URLs won't work without Apache; use `about.php` etc.). Needs a local MySQL loaded from `database.sql` and `DB_*` env vars. Default seeded admin: `admin` / `admin123`.
