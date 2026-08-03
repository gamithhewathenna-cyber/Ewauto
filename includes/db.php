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
