<?php
require_once __DIR__ . '/layout.php';

$slideCount   = (int) db()->query('SELECT COUNT(*) FROM slides')->fetchColumn();
$productCount = (int) db()->query('SELECT COUNT(*) FROM products')->fetchColumn();
$maintenance  = is_maintenance_mode();

admin_header('dashboard', 'Dashboard');
?>
<p class="admin-lead">Quick overview of your site.</p>

<div class="alert <?= $maintenance ? 'error' : 'ok' ?>">
    Maintenance mode is currently <strong><?= $maintenance ? 'ON — visitors see the "be right back" page' : 'OFF — the site is live' ?></strong>.
    <a href="settings.php" class="link">Change it &rarr;</a>
</div>

<div class="stat-grid">
    <a href="slides.php" class="stat-card">
        <div class="num"><?= $slideCount ?></div>
        <div class="lbl">Slider images</div>
    </a>
    <a href="products.php" class="stat-card">
        <div class="num"><?= $productCount ?></div>
        <div class="lbl">Products</div>
    </a>
    <a href="settings.php" class="stat-card">
        <div class="num">&#9881;</div>
        <div class="lbl">Site settings</div>
    </a>
</div>

<div class="card">
    <h3>Page content</h3>
    <p class="admin-lead" style="margin-bottom:16px;">Jump straight to the editable text and images for each page.</p>
    <div class="tab-nav">
        <a href="content.php?page=home">Home Content</a>
        <a href="content.php?page=about">About Content</a>
        <a href="content.php?page=contact">Contact Us Content</a>
    </div>
</div>
<?php
admin_footer();
