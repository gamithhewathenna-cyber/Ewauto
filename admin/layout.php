<?php
require_once __DIR__ . '/auth.php';
require_login();

const ADMIN_NAV = [
    'dashboard' => ['label' => 'Dashboard',           'href' => 'dashboard.php'],
    'content'   => ['label' => 'Page Content Change',  'href' => 'content.php'],
    'slides'    => ['label' => 'Slider Images',        'href' => 'slides.php'],
    'products'  => ['label' => 'Product Images',       'href' => 'products.php'],
    'settings'  => ['label' => 'Settings',             'href' => 'settings.php'],
];

function admin_header(string $active, string $title): void
{
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ZXTec Admin — <?= e($title) ?></title>
<link rel="stylesheet" href="admin.css">
</head>
<body>
<div class="admin-shell">
    <aside class="admin-side">
        <div class="admin-brand"><span class="brand-mark">&#9883;</span> ZXTec Admin</div>
        <nav class="admin-nav">
            <?php foreach (ADMIN_NAV as $key => $item): ?>
                <a href="<?= e($item['href']) ?>" class="<?= $key === $active ? 'is-active' : '' ?>"><?= e($item['label']) ?></a>
            <?php endforeach; ?>
        </nav>
        <div class="admin-side-foot">
            <a href="../index.php" target="_blank" class="link">View site &nearr;</a>
            <span class="who">Hi, <?= e(current_admin()['username']) ?></span>
            <a href="logout.php" class="btn-ghost">Log out</a>
        </div>
    </aside>
    <main class="admin-main">
        <h1><?= e($title) ?></h1>
    <?php
}

function admin_footer(): void
{
    ?>
    </main>
</div>
</body>
</html>
    <?php
}
