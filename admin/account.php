<?php
// "My account": every logged-in user (any role) can change their own
// username and password here.
require_once __DIR__ . '/layout.php';

$notice = '';
$error  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!check_csrf()) {
        $error = 'Session expired. Please try again.';
    } else {
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
                current_admin(true); // refresh the cached user so the sidebar shows the new name
                $notice = 'Account details updated.';
            }
        }
    }
}

$token = csrf_token();
$me = current_admin();

admin_header('account', 'My account');
?>
<p class="admin-lead">Signed in as <strong><?= e($me['username']) ?></strong> (<?= e(ROLES[$me['role']]) ?>). Change your username and/or password below - your current password is required to confirm.</p>

<?php if ($notice): ?><div class="alert ok"><?= e($notice) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

<div class="card">
    <h3>Login details</h3>
    <form method="post" class="settings-form">
        <input type="hidden" name="csrf" value="<?= e($token) ?>">

        <label class="field">Username
            <input type="text" name="new_username" value="<?= e($me['username']) ?>" required autocomplete="username">
        </label>
        <label class="field">Current password
            <input type="password" name="current_password" required autocomplete="current-password">
        </label>
        <label class="field">New password <span class="field-hint">(leave blank to keep current)</span>
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
