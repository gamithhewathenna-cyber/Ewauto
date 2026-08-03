<?php
require_once __DIR__ . '/layout.php';

$notice = '';
$error  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!check_csrf()) {
        $error = 'Session expired. Please try again.';
    } else {
        set_setting('maintenance_mode', isset($_POST['maintenance_mode']) ? '1' : '0');
        set_setting('maintenance_message', trim($_POST['maintenance_message'] ?? ''));
        set_setting('site_title', trim($_POST['site_title'] ?? ''));
        $notice = 'Settings saved.';
    }
}

$settings = all_settings();
$token = csrf_token();

admin_header('settings', 'Settings');
?>
<p class="admin-lead">Site-wide settings, including maintenance mode.</p>

<?php if ($notice): ?><div class="alert ok"><?= e($notice) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

<form method="post" class="settings-form">
    <input type="hidden" name="csrf" value="<?= e($token) ?>">

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
<?php
admin_footer();
