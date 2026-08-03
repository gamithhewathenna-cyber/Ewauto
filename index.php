<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/admin/auth.php';
require_once __DIR__ . '/includes/maintenance.php';

// Graceful DB handling — the page still renders with placeholders if the DB
// is unreachable, so the layout can be reviewed before setup is complete.
try {
    $images   = all_images();
    $content  = all_content();
    $slides   = all_slides(true);
    $products = all_products(true);
} catch (Throwable $ex) {
    $images = []; $content = []; $slides = []; $products = [];
}

try {
    maybe_show_maintenance_page();
} catch (Throwable $ex) {
    // DB unreachable — fall through and render with placeholders.
}

$c = static fn(string $key, string $fallback = '') => content($content, $key, $fallback);

// Only slides that actually have an image are shown; slides being drafted
// without an image yet don't break the carousel.
$slides = array_values(array_filter($slides, static fn($s) => !empty($s['filename'])));

$specDefaults = [
    $c('spec1_value', 'Lead-acid/Lithiut'),
    $c('spec2_value', '50/80km/h'),
    $c('spec3_value', '80/120km'),
    $c('spec4_value', '150kg'),
    $c('spec5_value', '1500/3000w'),
];
$firstSlide = $slides[0] ?? null;
$initialTitle = ($firstSlide && $firstSlide['heading'] !== '') ? $firstSlide['heading'] : $c('hero_title', 'DREAM');
$initialCopy  = ($firstSlide && $firstSlide['subheading'] !== '') ? $firstSlide['subheading'] : $c('hero_copy', 'Lorem ipsum dolor sit amet consectetur. Erat dui rhoncus consectetur tincidunt. Mi felis odio consectetur est.');
$initialHref  = ($firstSlide && $firstSlide['link_url'] !== '') ? $firstSlide['link_url'] : '#feature';
$initialSpecs = [];
for ($n = 1; $n <= 5; $n++) {
    $col = 'spec' . $n . '_value';
    $initialSpecs[$n] = ($firstSlide && $firstSlide[$col] !== '') ? $firstSlide[$col] : $specDefaults[$n - 1];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ZXTec — Let's Ride the Dream</title>
<meta name="description" content="ZXTec electric scooters and e-mobility. Worldwide reach, built to ride the dream.">
<?php if ($url = image_url($images, 'favicon')): ?><link rel="icon" href="<?= e($url) ?>"><?php endif; ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo:wght@500;600;700;800;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/css/style.css">
</head>
<body>

<!-- ===== Header ===== -->
<header class="site-header">
    <div class="wrap">
        <a class="brand" href="<?= e(BASE_URL) ?>/index.php">
            <?php if ($url = image_url($images, 'logo_header')): ?>
                <img src="<?= e($url) ?>" alt="<?= e(image_alt($images, 'logo_header', 'ZXTec')) ?>">
            <?php else: ?>
                <span class="brand-fallback"><span class="brand-mark">&#9883;</span>ZXTec</span>
            <?php endif; ?>
        </a>
        <nav class="nav" id="nav">
            <ul class="nav-links">
                <li><a class="active" href="#">Home</a></li>
                <li><a href="<?= e(BASE_URL) ?>/about.php">About us</a></li>
                <li><a href="<?= e(BASE_URL) ?>/contact.php">Contact Us</a></li>
            </ul>
            <div class="nav-social">
                <a href="#" aria-label="Call"><svg viewBox="0 0 24 24"><path d="M6.6 10.8c1.4 2.8 3.8 5.1 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1C10.6 21 3 13.4 3 4c0-.6.4-1 1-1h3.4c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.4 0 .8-.2 1l-2.2 2.2z"/></svg></a>
                <a href="mailto:<?= e($c('contact_email', 'companyname@gamil.com')) ?>" aria-label="Email"><svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2zm8 7L4 6.5V8l8 4.5L20 8V6.5L12 11z"/></svg></a>
                <a href="#" aria-label="Facebook"><svg viewBox="0 0 24 24"><path d="M13 22v-8h2.7l.4-3H13V9c0-.9.3-1.5 1.6-1.5H16V4.9c-.3 0-1.2-.1-2.3-.1-2.3 0-3.7 1.3-3.7 3.8V11H7v3h3v8h3z"/></svg></a>
                <a href="#" aria-label="Instagram"><svg viewBox="0 0 24 24"><path d="M12 2.2c3.2 0 3.6 0 4.9.1 3.3.1 4.8 1.7 4.9 4.9.1 1.3.1 1.6.1 4.8s0 3.6-.1 4.8c-.1 3.2-1.6 4.8-4.9 4.9-1.3.1-1.6.1-4.9.1s-3.6 0-4.8-.1c-3.3-.1-4.8-1.7-4.9-4.9C2.2 15.6 2.2 15.2 2.2 12s0-3.6.1-4.8C2.4 4 4 2.4 7.2 2.3 8.4 2.2 8.8 2.2 12 2.2zm0 3.2A6.6 6.6 0 105.4 12 6.6 6.6 0 0012 5.4zm0 10.9A4.3 4.3 0 1116.3 12 4.3 4.3 0 0112 16.3zm6.8-11.1a1.5 1.5 0 11-1.5-1.5 1.5 1.5 0 011.5 1.5z"/></svg></a>
                <a href="#" aria-label="Twitter"><svg viewBox="0 0 24 24"><path d="M22 5.9c-.7.3-1.5.5-2.3.6.8-.5 1.5-1.3 1.8-2.3-.8.5-1.7.8-2.6 1a4.1 4.1 0 00-7 3.7A11.6 11.6 0 013.2 4.5a4.1 4.1 0 001.3 5.5c-.7 0-1.3-.2-1.9-.5a4.1 4.1 0 003.3 4c-.6.2-1.2.2-1.8.1a4.1 4.1 0 003.8 2.9A8.3 8.3 0 012 18.3a11.6 11.6 0 006.3 1.8c7.5 0 11.7-6.3 11.7-11.7v-.5c.8-.6 1.5-1.3 2-2z"/></svg></a>
            </div>
            <button class="nav-toggle" id="navToggle" aria-label="Menu">
                <svg viewBox="0 0 24 24"><path d="M3 6h18v2H3zM3 11h18v2H3zM3 16h18v2H3z"/></svg>
            </button>
        </nav>
    </div>
</header>

<!-- ===== Hero ===== -->
<section class="hero" id="heroSlider" data-autoplay="6000">
    <div class="hero-blob"></div>

    <?php if (!empty($slides)): ?>
        <div class="hero-pager">
            <?php foreach ($slides as $i => $s): ?>
                <button aria-label="Slide <?= $i + 1 ?>" class="<?= $i === 0 ? 'is-active' : '' ?>"
                        data-index="<?= $i ?>"
                        data-heading="<?= e($s['heading']) ?>"
                        data-subheading="<?= e($s['subheading']) ?>"
                        data-link="<?= e($s['link_url']) ?>"
                        data-spec1="<?= e($s['spec1_value']) ?>"
                        data-spec2="<?= e($s['spec2_value']) ?>"
                        data-spec3="<?= e($s['spec3_value']) ?>"
                        data-spec4="<?= e($s['spec4_value']) ?>"
                        data-spec5="<?= e($s['spec5_value']) ?>"><?= $i + 1 ?></button>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="hero-pager">
            <button aria-label="Slide 1" class="is-active">1</button>
        </div>
    <?php endif; ?>

    <div class="wrap">
        <div class="hero-grid">
            <div class="hero-text">
                <p class="hero-eyebrow"><?= e($c('hero_eyebrow', "LET'S RIDE THE")) ?></p>
                <h1 class="hero-title" data-default="<?= e($c('hero_title', 'DREAM')) ?>"><?= e($initialTitle) ?></h1>
                <div class="hero-rule"><span class="dot"></span><span class="bar"></span></div>
                <p class="hero-copy" data-default="<?= e($c('hero_copy', 'Lorem ipsum dolor sit amet consectetur. Erat dui rhoncus consectetur tincidunt. Mi felis odio consectetur est.')) ?>"><?= e($initialCopy) ?></p>
                <div class="hero-cta">
                    <a href="<?= e($initialHref) ?>" class="btn" id="heroCta" data-default-href="#feature"><?= e($c('hero_cta_label', 'Learn more')) ?> <span class="arrow">&rsaquo;</span></a>
                </div>
            </div>
            <div class="hero-visual">
                <?php if (!empty($slides)): ?>
                    <?php foreach ($slides as $i => $s): ?>
                        <img class="hero-slide-img <?= $i === 0 ? 'is-active' : '' ?>" data-index="<?= $i ?>"
                             src="<?= e(UPLOAD_URL . '/' . rawurlencode($s['filename'])) ?>" alt="<?= e($s['alt_text']) ?>">
                    <?php endforeach; ?>
                <?php elseif ($url = image_url($images, 'hero_scooter')): ?>
                    <img src="<?= e($url) ?>" alt="<?= e(image_alt($images, 'hero_scooter', 'Electric scooter')) ?>">
                <?php else: ?>
                    <div class="placeholder">Hero scooter image</div>
                <?php endif; ?>
            </div>
        </div>
        <!-- spec strip -->
        <div class="spec-strip">
            <div class="spec"><div class="k"><?= e($c('spec1_label', 'Battery')) ?></div><div class="v" data-default="<?= e($specDefaults[0]) ?>"><?= e($initialSpecs[1]) ?></div></div>
            <div class="spec"><div class="k"><?= e($c('spec2_label', 'Max Speed')) ?></div><div class="v" data-default="<?= e($specDefaults[1]) ?>"><?= e($initialSpecs[2]) ?></div></div>
            <div class="spec"><div class="k"><?= e($c('spec3_label', 'Range')) ?></div><div class="v" data-default="<?= e($specDefaults[2]) ?>"><?= e($initialSpecs[3]) ?></div></div>
            <div class="spec"><div class="k"><?= e($c('spec4_label', 'Weight allow')) ?></div><div class="v" data-default="<?= e($specDefaults[3]) ?>"><?= e($initialSpecs[4]) ?></div></div>
            <div class="spec"><div class="k"><?= e($c('spec5_label', 'Motor')) ?></div><div class="v" data-default="<?= e($specDefaults[4]) ?>"><?= e($initialSpecs[5]) ?></div></div>
        </div>
    </div>
</section>

<!-- ===== Intro + lineup ===== -->
<section class="intro">
    <div class="wrap">
        <div class="intro-grid">
            <div class="intro-copy">
                <p><?= e($c('intro_para1', 'Lorem ipsum dolor sit amet consectetur. Vel eget a sem amet leo sollicitudin tellus. Amet nunc urna sed sociis viverra urna hendrerit fringilla. Varius nec id egestas arcu pretium elit egestas in amet. Elementum accumsan blandit purus duis lorem tincidunt at.')) ?></p>
                <p><?= e($c('intro_para2', 'Vel quam placerat nunc sed. Arcu porta pretium consequat id vestibulum nullam. Sit sit faucibus sodales aliquet enim pharetra urna imperdiet. Scelerisque enim in sed commodo odio. Non nisl vestibulum convallis non sapien mattis. Viverra congue et viverra.')) ?></p>
            </div>
            <h2 class="intro-head"><?= e($c('intro_heading', 'LOREM IPSUM DOLOR SIT AMET CONSECTETUR.')) ?></h2>
        </div>
    </div>
</section>
<section class="lineup">
    <div class="wrap">
        <?php if (!empty($products)): ?>
            <div class="product-grid">
                <?php foreach ($products as $p): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if ($p['filename']): ?>
                                <img src="<?= e(UPLOAD_URL . '/' . rawurlencode($p['filename'])) ?>" alt="<?= e($p['alt_text'] ?: $p['name']) ?>">
                            <?php else: ?>
                                <div class="placeholder">No image</div>
                            <?php endif; ?>
                        </div>
                        <div class="product-name"><?= e($p['name']) ?></div>
                        <?php if ($p['description']): ?><p class="product-desc"><?= e($p['description']) ?></p><?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif ($url = image_url($images, 'lineup_vehicles')): ?>
            <img src="<?= e($url) ?>" alt="<?= e(image_alt($images, 'lineup_vehicles', 'Vehicle lineup')) ?>">
        <?php else: ?>
            <div class="placeholder">Product lineup row</div>
        <?php endif; ?>
    </div>
</section>

<!-- ===== Kunpeng feature ===== -->
<section class="feature" id="feature">
    <div class="wrap">
        <div class="feature-grid">
            <div class="feature-text">
                <h2 class="feature-title"><?= e($c('feature_title', 'KUNPENG')) ?></h2>
                <p class="feature-sub"><?= e($c('feature_sub', 'Lorem ipsum dolor sit amet consectetur. Vel eget a sem.')) ?></p>
                <div class="feature-progress"><span class="knob"></span><span class="track"></span></div>
                <div class="feature-colors">
                    <div class="lbl">colors:</div>
                    <div class="swatches">
                        <span class="swatch" style="background:#8B3A3F"></span>
                        <span class="swatch" style="background:#3f7d4f"></span>
                        <span class="swatch" style="background:#e8c760"></span>
                        <span class="swatch" style="background:#161616"></span>
                    </div>
                </div>
                <div class="feature-specs">
                    <div><div class="k"><?= e($c('kfeature1_label', 'Max Speed')) ?></div><div class="v"><?= e($c('kfeature1_value', '50/80km/h')) ?></div></div>
                    <div><div class="k"><?= e($c('kfeature2_label', 'Range')) ?></div><div class="v"><?= e($c('kfeature2_value', '80/120km')) ?></div></div>
                    <div><div class="k"><?= e($c('kfeature3_label', 'Weight allow')) ?></div><div class="v"><?= e($c('kfeature3_value', '150kg')) ?></div></div>
                    <div><div class="k"><?= e($c('kfeature4_label', 'Motor')) ?></div><div class="v"><?= e($c('kfeature4_value', '1500/3000w')) ?></div></div>
                    <div><div class="k"><?= e($c('kfeature5_label', 'Battery')) ?></div><div class="v"><?= e($c('kfeature5_value', 'Lead-acid/Lithiut')) ?></div></div>
                </div>
            </div>
            <div class="feature-visual">
                <?php if ($url = image_url($images, 'kunpeng_scooter')): ?>
                    <img src="<?= e($url) ?>" alt="<?= e(image_alt($images, 'kunpeng_scooter', 'Kunpeng scooter')) ?>">
                <?php else: ?>
                    <div class="placeholder">Kunpeng scooter image</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="feature-nav">
        <button aria-label="Previous">&lsaquo;</button>
        <button aria-label="Next">&rsaquo;</button>
    </div>
</section>

<!-- ===== Worldwide reach ===== -->
<section class="world">
    <div class="wrap">
        <div class="world-head">
            <h2><?= e($c('world_heading_prefix', 'WE ARE')) ?> <span class="hl"><?= e($c('world_heading_highlight', 'WORLD WIDE REACH')) ?></span></h2>
            <p><?= e($c('world_copy', 'Lorem ipsum dolor sit amet consectetur. Vel eget a sem amet leo sollicitudin tellus. Amet nunc urna sed sociis viverra urna hendrerit fringilla.')) ?></p>
        </div>
        <div class="world-grid">
            <div class="world-left">
                <div class="testimonial">
                    <div class="testimonial-head">
                        <?php if ($url = image_url($images, 'testimonial_avatar')): ?>
                            <img src="<?= e($url) ?>" alt="<?= e(image_alt($images, 'testimonial_avatar', 'Customer')) ?>">
                        <?php else: ?>
                            <div class="placeholder"></div>
                        <?php endif; ?>
                        <div>
                            <div class="name"><?= e($c('testimonial_name', 'Lorem ipsum dolor')) ?></div>
                            <div class="role"><?= e($c('testimonial_role', 'Lorem ipsum')) ?></div>
                        </div>
                    </div>
                    <p><?= e($c('testimonial_quote', 'Lorem ipsum dolor sit amet consectetur. Nisl proin volutpat leo sed. Enim a rhoncus faucibus proin risus tincidunt. Proin mi nisl donec eu sociis nullam cursus rhoncus elit. Est eu ac iaculis iaculis consequat risus et. Ac molestie netus varius praesent.')) ?></p>
                    <div class="testimonial-nav">
                        <button aria-label="Previous">&lsaquo;</button>
                        <button aria-label="Next">&rsaquo;</button>
                    </div>
                </div>
            </div>
            <div class="world-map">
                <?php if ($url = image_url($images, 'world_map')): ?>
                    <img src="<?= e($url) ?>" alt="<?= e(image_alt($images, 'world_map', 'World map')) ?>">
                <?php else: ?>
                    <div class="placeholder">World map</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- ===== CTA + stats ===== -->
<section class="cta-band" id="contact">
    <div class="wrap">
        <div class="cta-card">
            <?php if ($url = image_url($images, 'cta_rider')): ?>
                <img class="cta-bg" src="<?= e($url) ?>" alt="<?= e(image_alt($images, 'cta_rider', 'Rider')) ?>">
            <?php endif; ?>
            <div class="cta-inner">
                <h2><?= e($c('cta_heading', 'LOREM IPSUM DOLOR SITUR.')) ?></h2>
                <p><?= e($c('cta_copy', 'Lorem ipsum dolor sit amet consectetur. Vel eget a sem amet leo sollicitudin tellus. Amet nunc urna sed sociis viverra urna hendrerit fringilla.')) ?></p>
                <a href="<?= e(BASE_URL) ?>/contact.php" class="btn btn-light"><?= e($c('cta_button_label', 'Contact us')) ?> <span class="arrow">&rsaquo;</span></a>
            </div>
        </div>
        <div class="stats">
            <div class="stat"><div class="num"><?= e($c('stat1_num', '100+')) ?></div><div class="cap"><?= e($c('stat1_cap', 'Countries and regions exports')) ?></div></div>
            <div class="stat"><div class="num"><?= e($c('stat2_num', '500+')) ?></div><div class="cap"><?= e($c('stat2_cap', 'Global distributors')) ?></div></div>
            <div class="stat"><div class="num"><?= e($c('stat3_num', '5')) ?></div><div class="cap"><?= e($c('stat3_cap', 'Production bases: Wuxi, Tianjin, Dongguan, Thailand, Indonesia')) ?></div></div>
            <div class="stat"><div class="num"><?= e($c('stat4_num', '3')) ?></div><div class="cap"><?= e($c('stat4_cap', 'Three branch offices: Shenzhen, Poland, United States')) ?></div></div>
        </div>
    </div>
</section>

<!-- ===== Footer ===== -->
<footer class="site-footer">
    <div class="wrap">
        <div class="footer-grid">
            <div class="footer-brand">
                <?php if ($url = image_url($images, 'logo_footer')): ?>
                    <img src="<?= e($url) ?>" alt="<?= e(image_alt($images, 'logo_footer', 'ZXTec')) ?>">
                <?php else: ?>
                    <span class="brand-fallback" style="color:#fff"><span class="brand-mark">&#9883;</span>ZXTec</span>
                <?php endif; ?>
                <p><?= e($c('footer_about', 'Lorem ipsum dolor sit amet consectetur. Pharetra at pretium fringilla nisl feugiat. Purus vel lectus faucibus non porttitor sit magna tincidunt tellus. Ut odio in vitae mollis tortor ultrices.')) ?></p>
            </div>
            <div class="footer-col">
                <h4>Link</h4>
                <ul>
                    <li><a href="#">Home</a></li>
                    <li><a href="<?= e(BASE_URL) ?>/about.php">About Us</a></li>
                    <li><a href="<?= e(BASE_URL) ?>/contact.php">Contact us</a></li>
                </ul>
            </div>
            <div class="footer-col footer-touch">
                <h4>Get in touch</h4>
                <div class="row"><div class="q">Need support?</div></div>
                <div class="row"><div class="a"><?= e($c('contact_email', 'companyname@gamil.com')) ?></div></div>
                <div class="row"><div class="q">Custom care?</div></div>
                <div class="row"><div class="a"><?= e($c('contact_phone', '(+391) 1234 8492')) ?></div></div>
            </div>
            <div class="footer-col">
                <div class="footer-social">
                    <a href="#"><svg viewBox="0 0 24 24"><path d="M13 22v-8h2.7l.4-3H13V9c0-.9.3-1.5 1.6-1.5H16V4.9c-.3 0-1.2-.1-2.3-.1-2.3 0-3.7 1.3-3.7 3.8V11H7v3h3v8h3z"/></svg> Facebook</a>
                    <a href="#"><svg viewBox="0 0 24 24"><path d="M12 2.2c3.2 0 3.6 0 4.9.1 3.3.1 4.8 1.7 4.9 4.9.1 1.3.1 1.6.1 4.8s0 3.6-.1 4.8c-.1 3.2-1.6 4.8-4.9 4.9-1.3.1-1.6.1-4.9.1s-3.6 0-4.8-.1c-3.3-.1-4.8-1.7-4.9-4.9C2.2 15.6 2.2 15.2 2.2 12s0-3.6.1-4.8C2.4 4 4 2.4 7.2 2.3 8.4 2.2 8.8 2.2 12 2.2zm0 3.2A6.6 6.6 0 105.4 12 6.6 6.6 0 0012 5.4zm0 10.9A4.3 4.3 0 1116.3 12 4.3 4.3 0 0112 16.3zm6.8-11.1a1.5 1.5 0 11-1.5-1.5 1.5 1.5 0 011.5 1.5z"/></svg> Instagram</a>
                    <a href="#"><svg viewBox="0 0 24 24"><path d="M22 5.9c-.7.3-1.5.5-2.3.6.8-.5 1.5-1.3 1.8-2.3-.8.5-1.7.8-2.6 1a4.1 4.1 0 00-7 3.7A11.6 11.6 0 013.2 4.5a4.1 4.1 0 001.3 5.5c-.7 0-1.3-.2-1.9-.5a4.1 4.1 0 003.3 4c-.6.2-1.2.2-1.8.1a4.1 4.1 0 003.8 2.9A8.3 8.3 0 012 18.3a11.6 11.6 0 006.3 1.8c7.5 0 11.7-6.3 11.7-11.7v-.5c.8-.6 1.5-1.3 2-2z"/></svg> Twitter</a>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <?= e($c('footer_bottom', 'ZXTec @2026, All Right reserved by Creativelements')) ?>
    </div>
</footer>

<script src="<?= e(BASE_URL) ?>/assets/js/main.js"></script>
</body>
</html>
