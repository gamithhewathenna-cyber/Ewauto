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

/**
 * @param array $subnav Optional in-page section links, each ['id' => anchor, 'label' => text].
 *                      Shown under the active sidebar item (and as a button row on phones).
 */
function admin_header(string $active, string $title, array $subnav = []): void
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
                <?php if ($key === $active && $subnav): ?>
                    <div class="admin-subnav" data-section-nav>
                        <?php foreach ($subnav as $link): ?>
                            <a href="#<?= e($link['id']) ?>"><?= e($link['label']) ?></a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
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
        <?php if ($subnav): ?>
            <nav class="section-jump" data-section-nav aria-label="Sections on this page">
                <?php foreach ($subnav as $link): ?>
                    <a href="#<?= e($link['id']) ?>"><?= e($link['label']) ?></a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>
    <?php
}

function admin_footer(): void
{
    ?>
    </main>
</div>
<script>
// Section links: highlight the section currently in view - the last one
// whose top has scrolled past a line near the top of the screen.
(function () {
    var links = Array.prototype.slice.call(document.querySelectorAll('[data-section-nav] a'));
    if (!links.length) return;

    var targets = [];
    links.forEach(function (a) {
        var el = document.getElementById(a.getAttribute('href').slice(1));
        if (el && targets.indexOf(el) === -1) targets.push(el);
    });
    if (!targets.length) return;

    var currentId = null;
    function update() {
        var line = 120; // px from the top of the viewport
        var id = targets[0].id;
        for (var i = 0; i < targets.length; i++) {
            if (targets[i].getBoundingClientRect().top <= line) id = targets[i].id;
        }
        // At the very bottom the last sections may never reach the line.
        if (window.scrollY > 0 && window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 2) {
            id = targets[targets.length - 1].id;
        }
        if (id === currentId) return;
        currentId = id;
        links.forEach(function (a) {
            var on = a.getAttribute('href') === '#' + id;
            a.classList.toggle('is-current', on);
            // Keep the active button visible in the phone button row.
            if (on && a.parentNode.classList.contains('section-jump') && a.parentNode.scrollWidth > a.parentNode.clientWidth) {
                a.parentNode.scrollLeft = a.offsetLeft - 16;
            }
        });
    }

    var ticking = false;
    window.addEventListener('scroll', function () {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(function () { ticking = false; update(); });
    }, { passive: true });
    window.addEventListener('resize', update);
    window.addEventListener('load', update);
    update();
})();
</script>
</body>
</html>
    <?php
}
