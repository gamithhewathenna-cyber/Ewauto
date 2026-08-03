<?php
/**
 * Database configuration.
 * Update these values to match your MySQL server.
 */
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'zxtec');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
// Optional: path to a MySQL unix socket. Leave '' to connect over host/port.
define('DB_SOCKET', getenv('DB_SOCKET') ?: '');
define('DB_PORT', getenv('DB_PORT') ?: '3306');

// Absolute URL/path base. Leave '' if the site is at the web root.
define('BASE_URL', getenv('BASE_URL') ?: '');

// Where uploaded images are stored (relative to project root).
define('UPLOAD_DIR', __DIR__ . '/../uploads');
define('UPLOAD_URL', BASE_URL . '/uploads');

// Allowed upload types
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml']);
define('MAX_UPLOAD_BYTES', 6 * 1024 * 1024); // 6 MB
