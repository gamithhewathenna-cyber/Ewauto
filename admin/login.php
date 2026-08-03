<?php
require_once __DIR__ . '/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    try {
        $stmt = db()->prepare('SELECT * FROM admins WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin'] = ['id' => $admin['id'], 'username' => $admin['username']];
            header('Location: index.php');
            exit;
        }
        $error = 'Wrong username or password.';
    } catch (Throwable $e) {
        $error = 'Cannot reach the database. Check your configuration.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ZXTec Admin — Sign in</title>
<link rel="stylesheet" href="admin.css">
</head>
<body class="login-page">
    <form class="login-card" method="post" autocomplete="off">
        <div class="login-brand"><span class="brand-mark">&#9883;</span> ZXTec Admin</div>
        <p class="login-sub">Sign in to manage site images.</p>
        <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
        <label>Username
            <input type="text" name="username" required autofocus>
        </label>
        <label>Password
            <input type="password" name="password" required>
        </label>
        <button type="submit" class="btn-primary">Sign in</button>
        <p class="login-hint">Default: <code>admin</code> / <code>admin123</code></p>
    </form>
</body>
</html>
