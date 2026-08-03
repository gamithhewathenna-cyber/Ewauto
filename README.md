# ZXTec — E-Mobility Website

A responsive landing page for ZXTec electric scooters, built with **PHP, MySQL,
HTML5 and CSS3**. Every image on the site is managed from a password-protected
admin panel, so content can be updated without touching code.

---

## What's inside

```
zxtec/
├── index.php              Front-end landing page (all sections)
├── database.sql           MySQL schema + seed data
├── includes/
│   ├── config.php         DB credentials & upload settings
│   └── db.php             DB connection + image helpers
├── admin/
│   ├── login.php          Admin sign-in
│   ├── logout.php         Sign out
│   ├── index.php          Image manager dashboard
│   ├── auth.php           Session / CSRF helpers
│   └── admin.css          Admin panel styles
├── assets/
│   ├── css/style.css      Front-end styles (responsive)
│   └── js/main.js         Mobile nav
└── uploads/               Uploaded images live here (writable)
```

---

## Requirements

- PHP 7.4+ (tested on PHP 8.3) with the `pdo_mysql` and `fileinfo` extensions
- MySQL 5.7+ / MariaDB 10+
- A web server (Apache, Nginx) — or PHP's built-in server for local testing

---

## Setup

### 1. Create the database

```bash
mysql -u root -p < database.sql
```

This creates the `zxtec` database, the `images` and `admins` tables, seeds the
eight image slots, and creates a default admin account.

### 2. Configure the connection

Edit `includes/config.php` (or set the matching environment variables):

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'zxtec');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
// If your MySQL uses a unix socket instead of host/port, set:
// define('DB_SOCKET', '/var/run/mysqld/mysqld.sock');
```

If the site lives in a sub-folder (e.g. `example.com/zxtec/`), set `BASE_URL`
to `/zxtec`. Leave it as `''` when the site is at the web root.

### 3. Make uploads writable

```bash
chmod 775 uploads
```

### 4. Run it

Point your web server's document root at the `zxtec/` folder, or for a quick
local test:

```bash
php -S localhost:8080
```

Then open <http://localhost:8080/index.php>.

---

## Admin panel

Visit `/admin/login.php`.

**Default login:** `admin` / `admin123` — change this immediately.

From the dashboard you can, for every image slot: upload a new image, replace an
existing one, edit its alt text, or remove it. Changes appear on the live site
right away. Slots without an image show a labelled placeholder on the front end
(the logo falls back to a text wordmark).

### Changing the admin password

Generate a new hash and update the row:

```bash
php -r "echo password_hash('YOUR_NEW_PASSWORD', PASSWORD_BCRYPT), PHP_EOL;"
```

```sql
UPDATE admins SET password_hash = 'PASTE_HASH_HERE' WHERE username = 'admin';
```

---

## Image slots

| Slot key            | Where it appears                     |
|---------------------|--------------------------------------|
| `logo_header`       | Header logo                          |
| `logo_footer`       | Footer logo                          |
| `hero_scooter`      | Main hero banner scooter             |
| `lineup_vehicles`   | Product line-up row                  |
| `kunpeng_scooter`   | Kunpeng feature section              |
| `world_map`         | "World Wide Reach" map               |
| `testimonial_avatar`| Testimonial customer photo           |
| `cta_rider`         | Call-to-action banner background     |

To add more manageable images later, insert a new row into the `images` table
with a unique `slot`, then reference it in the template with
`image_url($images, 'your_slot')`.

---

## Security notes

- Passwords are stored as bcrypt hashes; login is rate-limit-friendly and uses
  session regeneration.
- Admin actions are protected with CSRF tokens.
- Uploads are validated by real MIME type and size, and the `uploads/.htaccess`
  disables script execution in that folder (Apache). On Nginx, add an
  equivalent rule to stop PHP execution under `/uploads`.
- Change the default admin password before going live.

---

## Notes

The design supplied used placeholder ("lorem ipsum") copy, which is kept as-is.
Replace the text directly in `index.php` when final copy is ready.
