<?php
require_once __DIR__ . '/layout.php';
require_once __DIR__ . '/../includes/uploads.php';

const BRAND_IMAGE_SLOTS = [
    'logo_header' => 'Header logo',
    'logo_footer' => 'Footer logo',
    'favicon'     => 'Favicon',
];

$notice = '';
$error  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!check_csrf()) {
        $error = 'Session expired. Please try again.';
    } else {
        $action = $_POST['action'] ?? 'save_general';

        if ($action === 'save_general') {
            set_setting('maintenance_mode', isset($_POST['maintenance_mode']) ? '1' : '0');
            set_setting('maintenance_message', trim($_POST['maintenance_message'] ?? ''));
            set_setting('site_title', trim($_POST['site_title'] ?? ''));
            $notice = 'Settings saved.';
        } elseif ($action === 'save_image') {
            $slot = $_POST['slot'] ?? '';
            if (!isset(BRAND_IMAGE_SLOTS[$slot])) {
                $error = 'Unknown image slot.';
            } else {
                $stmt = db()->prepare('SELECT * FROM images WHERE slot = ? LIMIT 1');
                $stmt->execute([$slot]);
                $row = $stmt->fetch();

                if (!$row) {
                    $error = 'Image slot not found. Re-run the database setup script.';
                } else {
                    $newFilename = $row['filename'];
                    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                        $result = save_uploaded_image($_FILES['image'], $slot);
                        if (!$result['ok']) {
                            $error = $result['error'];
                        } else {
                            delete_uploaded_image($row['filename']);
                            $newFilename = $result['filename'];
                        }
                    }
                    if (!$error) {
                        db()->prepare('UPDATE images SET filename = ?, alt_text = ? WHERE slot = ?')
                            ->execute([$newFilename, trim($_POST['alt_text'] ?? $row['alt_text']), $slot]);
                        $notice = BRAND_IMAGE_SLOTS[$slot] . ' updated.';
                    }
                }
            }
        } elseif ($action === 'change_account') {
            $adminId = (int) current_admin()['id'];
            $stmt = db()->prepare('SELECT * FROM admins WHERE id = ?');
            $stmt->execute([$adminId]);
            $adminRow = $stmt->fetch();

            $currentPassword = $_POST['current_password'] ?? '';
            $newUsername     = trim($_POST['new_username'] ?? '');
            $newPassword     = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (!$adminRow || !password_verify($currentPassword, $adminRow['password_hash'])) {
                $error = 'Current password is incorrect.';
            } elseif ($newUsername === '') {
                $error = 'Username cannot be empty.';
            } elseif ($newPassword !== '' && strlen($newPassword) < 8) {
                $error = 'New password must be at least 8 characters.';
            } elseif ($newPassword !== '' && $newPassword !== $confirmPassword) {
                $error = 'New password and confirmation do not match.';
            } else {
                $dupe = db()->prepare('SELECT id FROM admins WHERE username = ? AND id != ?');
                $dupe->execute([$newUsername, $adminId]);
                if ($dupe->fetch()) {
                    $error = 'That username is already taken.';
                } else {
                    if ($newPassword !== '') {
                        db()->prepare('UPDATE admins SET username = ?, password_hash = ? WHERE id = ?')
                            ->execute([$newUsername, password_hash($newPassword, PASSWORD_DEFAULT), $adminId]);
                    } else {
                        db()->prepare('UPDATE admins SET username = ? WHERE id = ?')
                            ->execute([$newUsername, $adminId]);
                    }
                    $_SESSION['admin']['username'] = $newUsername;
                    $notice = 'Account details updated.';
                }
            }
        }
    }
}

$settings = all_settings();
$images   = all_images();
$token    = csrf_token();

admin_header('settings', 'Settings');
?>
<p class="admin-lead">Site-wide settings: maintenance mode, branding, and your admin account.</p>

<?php if ($notice): ?><div class="alert ok"><?= e($notice) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

<form method="post" class="settings-form">
    <input type="hidden" name="csrf" value="<?= e($token) ?>">
    <input type="hidden" name="action" value="save_general">

    <div class="card">
        <h3>Maintenance mode</h3>
        <label class="toggle-row">
            <input type="checkbox" name="maintenance_mode" value="1" <?= ($settings['maintenance_mode'] ?? '0') === '1' ? 'checked' : '' ?>>
            <span>Show a "be right back" page to every visitor except logged-in admins</span>
        </label>
        <label class="field">Maintenance message
            <textarea name="maintenance_message" rows="3"><?= e($settings['maintenance_message'] ?? '') ?></textarea>
        </label>
    </div>

    <div class="card">
        <h3>General</h3>
        <label class="field">Site title
            <input type="text" name="site_title" value="<?= e($settings['site_title'] ?? '') ?>">
        </label>
    </div>

    <button type="submit" class="btn-primary">Save settings</button>
</form>

<div class="card">
    <h3>Branding</h3>
    <p class="admin-lead" style="margin-bottom:16px;">Upload your logo, footer logo, and browser favicon (PNG or SVG recommended, square for the favicon).</p>
    <div class="image-grid image-grid-compact">
        <?php foreach (BRAND_IMAGE_SLOTS as $slot => $label): $img = $images[$slot] ?? null; ?>
            <div class="image-card">
                <div class="image-card-head">
                    <h4><?= e($label) ?></h4>
                    <code><?= e($slot) ?></code>
                </div>
                <div class="preview">
                    <?php if ($img && $img['filename']): ?>
                        <img src="<?= e(UPLOAD_URL . '/' . rawurlencode($img['filename'])) ?>" alt="<?= e($img['alt_text'] ?? '') ?>">
                    <?php else: ?>
                        <div class="preview-empty">No image yet</div>
                    <?php endif; ?>
                </div>
                <form method="post" enctype="multipart/form-data" class="image-form">
                    <input type="hidden" name="csrf" value="<?= e($token) ?>">
                    <input type="hidden" name="slot" value="<?= e($slot) ?>">
                    <input type="hidden" name="action" value="save_image">
                    <label class="field">Image file
                        <input type="file" name="image" accept="image/*">
                    </label>
                    <label class="field">Alt text
                        <input type="text" name="alt_text" value="<?= e($img['alt_text'] ?? '') ?>">
                    </label>
                    <div class="image-actions">
                        <button type="submit" class="btn-primary">Save</button>
                    </div>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="card">
    <h3>Admin account</h3>
    <p class="admin-lead" style="margin-bottom:16px;">Change your username and/or password. Your current password is required to confirm the change.</p>
    <form method="post" class="settings-form">
        <input type="hidden" name="csrf" value="<?= e($token) ?>">
        <input type="hidden" name="action" value="change_account">

        <label class="field">Username
            <input type="text" name="new_username" value="<?= e(current_admin()['username']) ?>" required>
        </label>
        <label class="field">Current password
            <input type="password" name="current_password" required>
        </label>
        <label class="field">New password <span style="font-weight:400;color:#999;">(leave blank to keep current)</span>
            <input type="password" name="new_password" autocomplete="new-password">
        </label>
        <label class="field">Confirm new password
            <input type="password" name="confirm_password" autocomplete="new-password">
        </label>

        <button type="submit" class="btn-primary">Update account</button>
    </form>
</div>
<?php
admin_footer();
