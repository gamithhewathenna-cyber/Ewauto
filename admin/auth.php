<?php
require_once __DIR__ . '/../includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * User roles. Administrators can do everything; content editors can edit
 * website content - page text and images (Home / About Us / Contact us),
 * the hero slider, bikes and their colours, and dealerships - plus their
 * own account. Users, Settings and the Dashboard stay administrator-only.
 */
const ROLES = [
    'admin'  => 'Administrator',
    'editor' => 'Content editor',
];

/**
 * Admin screens (by file name) each non-admin role may open. Anything not
 * listed is administrator-only, so new screens are locked down by default.
 */
const ROLE_PAGES = [
    'editor' => [
        'content.php', 'slides.php', 'bikes.php', 'bike_colors.php', 'dealers.php',
        'account.php', 'index.php', 'logout.php',
    ],
];

/**
 * The logged-in user as ['id', 'username', 'role'], or null. Re-read from
 * the database on each request, so a role change or deleted account takes
 * effect immediately rather than at next login.
 */
function current_admin(bool $refresh = false): ?array
{
    static $user = false;
    if ($user !== false && !$refresh) {
        return $user;
    }
    $user = null;

    $id = (int) ($_SESSION['admin']['id'] ?? 0);
    if ($id > 0) {
        $stmt = db()->prepare('SELECT * FROM admins WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $user = [
                'id'       => (int) $row['id'],
                'username' => $row['username'],
                // Before the `role` column exists every account is an admin.
                'role'     => isset(ROLES[$row['role'] ?? '']) ? $row['role'] : 'admin',
            ];
        } else {
            unset($_SESSION['admin']); // account was deleted
        }
    }
    return $user;
}

function is_admin_role(): bool
{
    $user = current_admin();
    return $user !== null && $user['role'] === 'admin';
}

/** Whether the current user may open the given admin screen (file name). */
function can_access_page(string $file): bool
{
    $user = current_admin();
    if (!$user) {
        return false;
    }
    if ($user['role'] === 'admin') {
        return true;
    }
    return in_array($file, ROLE_PAGES[$user['role']] ?? [], true);
}

/** Where each role lands after logging in. */
function home_page_for_role(): string
{
    return is_admin_role() ? 'dashboard.php' : 'content.php?page=home';
}

function require_login(): void
{
    if (!current_admin()) {
        header('Location: login.php');
        exit;
    }
}

// Simple CSRF token helpers
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function check_csrf(): bool
{
    return isset($_POST['csrf'], $_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], $_POST['csrf']);
}
