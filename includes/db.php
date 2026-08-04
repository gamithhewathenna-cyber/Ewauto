<?php
require_once __DIR__ . '/config.php';

/**
 * Return a shared PDO connection.
 */
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        if (DB_SOCKET !== '') {
            $dsn = 'mysql:unix_socket=' . DB_SOCKET . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        } else {
            $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        }
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

/**
 * Fetch all image rows keyed by slot.
 */
function all_images(): array
{
    $rows = db()->query('SELECT * FROM images ORDER BY id')->fetchAll();
    $out = [];
    foreach ($rows as $r) {
        $out[$r['slot']] = $r;
    }
    return $out;
}

/**
 * Return the public URL for an image slot, or a placeholder if none uploaded.
 *
 * @param array  $images  the map from all_images()
 * @param string $slot    slot key
 * @param string $fallback path (relative to BASE_URL) used when no image set
 */
function image_url(array $images, string $slot, string $fallback = ''): string
{
    if (!empty($images[$slot]['filename'])) {
        return UPLOAD_URL . '/' . rawurlencode($images[$slot]['filename']);
    }
    return $fallback ? (BASE_URL . '/' . ltrim($fallback, '/')) : '';
}

/**
 * Return alt text for a slot.
 */
function image_alt(array $images, string $slot, string $fallback = ''): string
{
    return $images[$slot]['alt_text'] ?? $fallback;
}

/**
 * Escape helper.
 */
function e(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

/**
 * ---- Settings (key/value) ----
 */
function all_settings(): array
{
    $rows = db()->query('SELECT * FROM settings')->fetchAll();
    $out = [];
    foreach ($rows as $r) {
        $out[$r['setting_key']] = $r['setting_value'];
    }
    return $out;
}

function get_setting(string $key, string $fallback = ''): string
{
    static $cache = null;
    if ($cache === null) {
        $cache = all_settings();
    }
    return $cache[$key] ?? $fallback;
}

function set_setting(string $key, string $value): void
{
    db()->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)')
        ->execute([$key, $value]);
}

function is_maintenance_mode(): bool
{
    return get_setting('maintenance_mode', '0') === '1';
}

/**
 * ---- Page content (key/value) ----
 */
function all_content(): array
{
    $rows = db()->query('SELECT * FROM content')->fetchAll();
    $out = [];
    foreach ($rows as $r) {
        $out[$r['content_key']] = $r['content_value'];
    }
    return $out;
}

function content(array $content, string $key, string $fallback = ''): string
{
    return ($content[$key] ?? '') !== '' ? $content[$key] : $fallback;
}

function set_content(string $key, string $value): void
{
    db()->prepare('INSERT INTO content (content_key, content_value) VALUES (?, ?)
        ON DUPLICATE KEY UPDATE content_value = VALUES(content_value)')
        ->execute([$key, $value]);
}

/**
 * ---- Hero slider ----
 */
function all_slides(bool $onlyActive = false): array
{
    $sql = 'SELECT * FROM slides' . ($onlyActive ? ' WHERE active = 1' : '') . ' ORDER BY sort_order ASC, id ASC';
    return db()->query($sql)->fetchAll();
}

/**
 * ---- Bikes (shown in the homepage bike carousel) ----
 */
function all_bikes(bool $onlyActive = false): array
{
    $sql = 'SELECT * FROM bikes' . ($onlyActive ? ' WHERE active = 1' : '') . ' ORDER BY sort_order ASC, id ASC';
    return db()->query($sql)->fetchAll();
}

function bike_colors(int $bikeId): array
{
    $stmt = db()->prepare('SELECT * FROM bike_colors WHERE bike_id = ? ORDER BY sort_order ASC, id ASC');
    $stmt->execute([$bikeId]);
    return $stmt->fetchAll();
}

/**
 * Fixed bike specification sheet, grouped by category. Column key => display
 * label. Shared between the Bikes admin form and the homepage bike carousel.
 */
function bike_spec_groups(): array
{
    return [
        'Dimension' => [
            'dim_length'    => 'Length',
            'dim_width'     => 'Width',
            'dim_height'    => 'Height',
            'dim_wheelbase' => 'Wheelbase',
        ],
        'Motor' => [
            'motor_type'           => 'Motor Type',
            'motor_rated_power'    => 'Rated Power',
            'motor_climbing_angle' => 'Climbing Angle',
        ],
        'Electricals' => [
            'battery_type'     => 'Battery Type',
            'battery_capacity' => 'Battery Capacity',
            'charging_time'    => 'Charging Time',
            'max_range'        => 'Max Range (km)',
            'dashboard'        => 'Dashboard',
        ],
        'Tires & Brakes' => [
            'tire_size'        => 'Tire Size',
            'shock_absorption' => 'Shock Absorption',
            'brake_fr'         => 'Brake (F&R)',
        ],
    ];
}

/**
 * Flat list of every bike spec column key (no grouping) — handy for
 * building INSERT/UPDATE column lists.
 */
function bike_spec_columns(): array
{
    $cols = [];
    foreach (bike_spec_groups() as $fields) {
        $cols = array_merge($cols, array_keys($fields));
    }
    return $cols;
}

/**
 * Turn a bike name into a unique URL slug, avoiding collisions with
 * existing bikes (optionally excluding one id, for edits).
 */
function unique_bike_slug(string $name, ?int $excludeId = null): string
{
    $base = strtolower(trim($name));
    $base = preg_replace('/[^a-z0-9]+/', '-', $base);
    $base = trim($base, '-');
    if ($base === '') {
        $base = 'bike';
    }

    $slug = $base;
    $i = 2;
    while (true) {
        $sql = 'SELECT id FROM bikes WHERE slug = ?' . ($excludeId ? ' AND id != ?' : '');
        $stmt = db()->prepare($sql);
        $stmt->execute($excludeId ? [$slug, $excludeId] : [$slug]);
        if (!$stmt->fetch()) {
            return $slug;
        }
        $slug = $base . '-' . $i;
        $i++;
    }
}
