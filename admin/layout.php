<?php
require_once __DIR__ . '/auth.php';
require_login();

// Items with a 'group' key get a small heading above them in the sidebar
// (shown once, before the first item of that group).
const ADMIN_NAV = [
    'dashboard'       => ['label' => 'Dashboard',     'href' => 'dashboard.php'],
    'content_home'    => ['label' => 'Home',          'href' => 'content.php?page=home',    'group' => 'Page content'],
    'content_about'   => ['label' => 'About Us',      'href' => 'content.php?page=about',   'group' => 'Page content'],
    'content_contact' => ['label' => 'Contact us',    'href' => 'content.php?page=contact', 'group' => 'Page content'],
    'slides'    => ['label' => 'Slider Images',        'href' => 'slides.php'],
    'bikes'     => ['label' => 'Bikes',                'href' => 'bikes.php'],
    'dealers'   => ['label' => 'Dealerships',          'href' => 'dealers.php'],
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
<title>EW Auto Admin - <?= e($title) ?></title>
<link rel="stylesheet" href="admin.css?v=<?= (int) (@filemtime(__DIR__ . '/admin.css') ?: 1) ?>">
</head>
<body>
<div class="admin-shell">
    <aside class="admin-side">
        <div class="admin-brand"><span class="brand-mark">&#9883;</span> EW Auto Admin</div>
        <nav class="admin-nav">
            <?php $lastGroup = null; foreach (ADMIN_NAV as $key => $item): $group = $item['group'] ?? null; ?>
                <?php if ($group !== null && $group !== $lastGroup): ?>
                    <span class="admin-nav-group"><?= e($group) ?></span>
                <?php endif; $lastGroup = $group; ?>
                <a href="<?= e($item['href']) ?>" class="<?= trim(($key === $active ? 'is-active ' : '') . ($group !== null ? 'is-sub' : '')) ?>"><?= e($item['label']) ?></a>
            <?php endforeach; ?>
        </nav>
        <div class="admin-side-foot">
            <a href="../" target="_blank" class="link">View site &nearr;</a>
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
