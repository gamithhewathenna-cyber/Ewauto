<?php
// Users & roles (administrators only - enforced by layout.php via ROLE_PAGES).
require_once __DIR__ . '/layout.php';

const ROLE_DESCRIPTIONS = [
    'admin'  => 'Full access to everything, including users and settings.',
    'editor' => 'Can only edit page text and upload images on the Home, About Us and Contact us pages.',
];

$notice = '';
$error  = '';
$me = current_admin();

// The `role` column is added by the database update - check it exists so we
// can explain what to run instead of failing with an SQL error.
try {
    db()->query('SELECT role FROM admins LIMIT 1');
    $rolesReady = true;
} catch (Throwable $ex) {
    $rolesReady = false;
}

function admin_count(): int
{
    return (int) db()->query("SELECT COUNT(*) FROM admins WHERE role = 'admin'")->fetchColumn();
}

function password_problem(string $password, string $confirm): ?string
{
    if (strlen($password) < 8) {
        return 'Password must be at least 8 characters.';
    }
    if ($password !== $confirm) {
        return 'Password and confirmation do not match.';
    }
    return null;
}

if ($rolesReady && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!check_csrf()) {
        $error = 'Session expired. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'add') {
            $username = trim($_POST['username'] ?? '');
            $role     = $_POST['role'] ?? '';
            $password = $_POST['password'] ?? '';

            if ($username === '') {
                $error = 'Please enter a username.';
            } elseif (!isset(ROLES[$role])) {
                $error = 'Please choose a role.';
            } elseif ($problem = password_problem($password, $_POST['confirm_password'] ?? '')) {
                $error = $problem;
            } else {
                $dupe = db()->prepare('SELECT id FROM admins WHERE username = ?');
                $dupe->execute([$username]);
                if ($dupe->fetch()) {
                    $error = 'That username is already taken.';
                } else {
                    db()->prepare('INSERT INTO admins (username, password_hash, role) VALUES (?, ?, ?)')
                        ->execute([$username, password_hash($password, PASSWORD_DEFAULT), $role]);
                    $notice = 'User "' . $username . '" created as ' . ROLES[$role] . '.';
                }
            }
        } elseif ($action === 'update') {
            $id   = (int) ($_POST['id'] ?? 0);
            $role = $_POST['role'] ?? '';
            $stmt = db()->prepare('SELECT * FROM admins WHERE id = ?');
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            $newPassword = $_POST['new_password'] ?? '';

            if (!$row) {
                $error = 'User not found.';
            } elseif (!isset(ROLES[$role])) {
                $error = 'Please choose a role.';
            } elseif ($id === $me['id'] && $role !== $row['role']) {
                $error = "You can't change your own role. Ask another administrator.";
            } elseif ($row['role'] === 'admin' && $role !== 'admin' && admin_count() <= 1) {
                $error = 'There must always be at least one administrator.';
            } elseif ($newPassword !== '' && ($problem = password_problem($newPassword, $_POST['confirm_password'] ?? ''))) {
                $error = $problem;
            } else {
                if ($newPassword !== '') {
                    db()->prepare('UPDATE admins SET role = ?, password_hash = ? WHERE id = ?')
                        ->execute([$role, password_hash($newPassword, PASSWORD_DEFAULT), $id]);
                    $notice = 'User "' . $row['username'] . '" updated, with a new password.';
                } else {
                    db()->prepare('UPDATE admins SET role = ? WHERE id = ?')->execute([$role, $id]);
                    $notice = 'User "' . $row['username'] . '" updated.';
                }
            }
        } elseif ($action === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            $stmt = db()->prepare('SELECT * FROM admins WHERE id = ?');
            $stmt->execute([$id]);
            $row = $stmt->fetch();

            if (!$row) {
                $error = 'User not found.';
            } elseif ($id === $me['id']) {
                $error = "You can't delete your own account.";
            } elseif ($row['role'] === 'admin' && admin_count() <= 1) {
                $error = 'There must always be at least one administrator.';
            } else {
                db()->prepare('DELETE FROM admins WHERE id = ?')->execute([$id]);
                $notice = 'User "' . $row['username'] . '" deleted.';
            }
        }
    }
}

$users = $rolesReady ? db()->query('SELECT id, username, role, created_at FROM admins ORDER BY role, username')->fetchAll() : [];
$token = csrf_token();

admin_header('users', 'Users');
?>
<p class="admin-lead">Create logins for other people and choose what each one can do.</p>

<?php if ($notice): ?><div class="alert ok"><?= e($notice) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

<?php if (!$rolesReady): ?>
    <div class="alert error">
        User roles need a one-time database update. In phpMyAdmin, open the SQL tab for your database and run:
        <pre class="sql-snippet">ALTER TABLE admins ADD COLUMN role VARCHAR(16) NOT NULL DEFAULT 'admin';</pre>
        Existing accounts stay administrators. Then reload this page.
    </div>
<?php else: ?>

<div class="card">
    <h3>Roles</h3>
    <div class="role-list">
        <?php foreach (ROLES as $roleKey => $roleLabel): ?>
            <div><span class="role-tag role-<?= e($roleKey) ?>"><?= e($roleLabel) ?></span> <?= e(ROLE_DESCRIPTIONS[$roleKey]) ?></div>
        <?php endforeach; ?>
    </div>
</div>

<div class="image-grid">
    <?php foreach ($users as $u): $isMe = (int) $u['id'] === $me['id']; ?>
        <div class="image-card">
            <div class="image-card-head">
                <h3><?= e($u['username']) ?><?= $isMe ? ' <span class="field-hint">(you)</span>' : '' ?></h3>
                <span class="role-tag role-<?= e($u['role']) ?>"><?= e(ROLES[$u['role']] ?? $u['role']) ?></span>
            </div>
            <form method="post" class="image-form">
                <input type="hidden" name="csrf" value="<?= e($token) ?>">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                <label class="field">Role
                    <select name="role" <?= $isMe ? 'disabled' : '' ?>>
                        <?php foreach (ROLES as $roleKey => $roleLabel): ?>
                            <option value="<?= e($roleKey) ?>" <?= $u['role'] === $roleKey ? 'selected' : '' ?>><?= e($roleLabel) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($isMe): ?>
                        <input type="hidden" name="role" value="<?= e($u['role']) ?>">
                    <?php endif; ?>
                </label>
                <label class="field">New password <span class="field-hint">(leave blank to keep current)</span>
                    <input type="password" name="new_password" autocomplete="new-password">
                </label>
                <label class="field">Confirm new password
                    <input type="password" name="confirm_password" autocomplete="new-password">
                </label>
                <div class="image-actions">
                    <button type="submit" class="btn-primary">Save</button>
                    <?php if (!$isMe): ?>
                        <button type="submit" name="action" value="delete" class="btn-danger"
                                onclick="return confirm(<?= e(json_encode('Delete the user "' . $u['username'] . '"? They will no longer be able to log in.', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)) ?>);">Delete</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    <?php endforeach; ?>

    <div class="image-card">
        <div class="image-card-head"><h3>Add new user</h3></div>
        <form method="post" class="image-form">
            <input type="hidden" name="csrf" value="<?= e($token) ?>">
            <input type="hidden" name="action" value="add">
            <label class="field">Username
                <input type="text" name="username" required autocomplete="off">
            </label>
            <label class="field">Role
                <select name="role" required>
                    <?php foreach (ROLES as $roleKey => $roleLabel): ?>
                        <option value="<?= e($roleKey) ?>" <?= $roleKey === 'editor' ? 'selected' : '' ?>><?= e($roleLabel) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="field">Password <span class="field-hint">(at least 8 characters)</span>
                <input type="password" name="password" required minlength="8" autocomplete="new-password">
            </label>
            <label class="field">Confirm password
                <input type="password" name="confirm_password" required minlength="8" autocomplete="new-password">
            </label>
            <div class="image-actions">
                <button type="submit" class="btn-primary">Create user</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>
<?php
admin_footer();
