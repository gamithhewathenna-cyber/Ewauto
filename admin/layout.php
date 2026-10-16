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
 *                      Shown as a sticky horizontal tab bar at the top of the page.
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
// Section tabs: show one section at a time. Clicking a tab swaps the
// visible section (no scrolling). The URL hash (#tab=<section id>) remembers
// the open tab, so after Save (forms post back to #tab=...) the same tab
// reopens. "#tab=" deliberately matches no element, so the browser doesn't
// jump down the page. Without JS every section stays visible.
(function () {
    var nav = document.querySelector('[data-section-nav]');
    if (!nav) return;

    var tabs = [];
    Array.prototype.slice.call(nav.querySelectorAll('a')).forEach(function (a) {
        var panel = document.getElementById(a.getAttribute('href').slice(1));
        if (panel) tabs.push({ link: a, panel: panel });
    });
    if (!tabs.length) return;

    nav.setAttribute('role', 'tablist');
    tabs.forEach(function (t) {
        t.link.setAttribute('role', 'tab');
        t.panel.setAttribute('role', 'tabpanel');
    });

    function show(id) {
        var match = tabs.some(function (t) { return t.panel.id === id; });
        if (!match) id = tabs[0].panel.id;
        tabs.forEach(function (t) {
            var on = t.panel.id === id;
            t.panel.hidden = !on;
            t.link.classList.toggle('is-current', on);
            t.link.setAttribute('aria-selected', on ? 'true' : 'false');
            // Keep the active tab visible when the tab row scrolls sideways.
            if (on && nav.scrollWidth > nav.clientWidth) {
                nav.scrollLeft = t.link.offsetLeft - 16;
            }
        });
    }

    tabs.forEach(function (t) {
        t.link.addEventListener('click', function (e) {
            e.preventDefault();
            show(t.panel.id);
            if (history.replaceState) history.replaceState(null, '', '#tab=' + t.panel.id);
        });
    });

    show(location.hash.replace(/^#(tab=)?/, ''));
})();
</script>
</body>
</html>
    <?php
}
