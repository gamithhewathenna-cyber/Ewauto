<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/admin/auth.php';
require_once __DIR__ . '/includes/maintenance.php';

try {
    $images  = all_images();
    $content = all_content();
} catch (Throwable $ex) {
    $images = []; $content = [];
}

try {
    maybe_show_maintenance_page();
} catch (Throwable $ex) {
    // DB unreachable — fall through and render with placeholders.
}

$c = static fn(string $key, string $fallback = '') => content($content, $key, $fallback);

$whatWeDoItems = [
    $c('whatwedo_item1', 'Lorem ipsum dolor sit amet consectetur.'),
    $c('whatwedo_item2', 'Lorem ipsum dolor sit amet consectetur.'),
    $c('whatwedo_item3', 'Lorem ipsum dolor sit amet consectetur.'),
    $c('whatwedo_item4', 'Lorem ipsum dolor sit amet consectetur.'),
    $c('whatwedo_item5', 'Lorem ipsum dolor sit amet consectetur.'),
    $c('whatwedo_item6', 'Lorem ipsum dolor sit amet consectetur.'),
];

$team = [
    ['slot' => 'about_team_1', 'title' => $c('team_member1_title', 'vitae nulla nisi tellus gravida.'), 'text' => $c('team_member1_text', 'Eget dolor vulputate malesuada sed morbi sed.')],
    ['slot' => 'about_team_2', 'title' => $c('team_member2_title', 'vitae ellus gravida.'), 'text' => $c('team_member2_text', 'Eget dolor vulputate malesuada sed morbi sed.')],
    ['slot' => 'about_team_3', 'title' => $c('team_member3_title', 'vitae nisi tellus gravidaet'), 'text' => $c('team_member3_text', 'Dolor vulputate malesuada sed morbi sed.')],
    ['slot' => 'about_team_4', 'title' => $c('team_member4_title', 'nulla nisi gravida'), 'text' => $c('team_member4_text', 'Dolor vulputate malesuada sed morbi sed.')],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ZXTec — About Us</title>
<meta name="description" content="About ZXTec — who we are, our story, and what we do.">
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
                <li><a href="<?= e(BASE_URL) ?>/index.php">Home</a></li>
                <li><a class="active" href="<?= e(BASE_URL) ?>/about.php">About us</a></li>
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

<!-- ===== About hero ===== -->
<section class="contact-hero">
    <div class="contact-hero-blob"></div>
    <div class="wrap">
        <p class="hero-eyebrow"><?= e($c('about_eyebrow', 'WHO WE ARE')) ?></p>
        <h1 class="contact-title"><?= e($c('about_title', 'ABOUT US')) ?></h1>
        <p class="contact-intro"><?= e($c('about_intro', 'Lorem ipsum dolor sit amet consectetur. Erat dui rhoncus consectetur tincidunt. Mi felis odio consectetur est.')) ?></p>
    </div>
</section>

<!-- ===== Our Story ===== -->
<section class="story-section">
    <div class="wrap">
        <div class="story-grid">
            <div class="story-image">
                <?php if ($url = image_url($images, 'about_story_image')): ?>
                    <img src="<?= e($url) ?>" alt="<?= e(image_alt($images, 'about_story_image', 'Our story')) ?>">
                <?php else: ?>
                    <div class="placeholder">Story photo</div>
                <?php endif; ?>
            </div>
            <div class="story-copy">
                <h2><?= e($c('story_heading', 'OUR STORY')) ?></h2>
                <p><?= e($c('story_para1', 'Lorem ipsum dolor sit amet consectetur. Vel eget a sem amet leo sollicitudin tellus. Amet nunc urna sed sociis viverra urna hendrerit fringilla. Varius nec id egestas arcu pretium elit egestas in amet. Elementum accumsan blandit purus duis lorem tincidunt at.')) ?></p>
                <p><?= e($c('story_para2', 'Vel quam placerat nunc sed. Arcu porta pretium consequat id vestibulum nullam. Sit sit faucibus sodales aliquet enim pharetra urna imperdiet. Scelerisque enim in sed commodo odio. Non nisl vestibulum convallis non sapien mattis. Viverra congue et viverra.')) ?></p>
            </div>
        </div>
    </div>
</section>

<!-- ===== Stats band ===== -->
<section class="about-stats-band">
    <?php if ($url = image_url($images, 'about_stats_bg')): ?>
        <img class="about-stats-bg" src="<?= e($url) ?>" alt="">
    <?php endif; ?>
    <div class="wrap about-stats-row">
        <div class="about-stat"><div class="num"><?= e($c('about_stat1_num', '100+')) ?></div><div class="cap"><?= e($c('about_stat1_cap', 'Countries and regions exports')) ?></div></div>
        <div class="about-stat"><div class="num"><?= e($c('about_stat2_num', '500+')) ?></div><div class="cap"><?= e($c('about_stat2_cap', 'Global distributors')) ?></div></div>
        <div class="about-stat"><div class="num"><?= e($c('about_stat3_num', '5')) ?></div><div class="cap"><?= e($c('about_stat3_cap', 'Production bases: Wuxi, Tianjin, Dongguan, Thailand, Indonesia')) ?></div></div>
        <div class="about-stat"><div class="num"><?= e($c('about_stat4_num', '3')) ?></div><div class="cap"><?= e($c('about_stat4_cap', 'Three branch offices: Shenzhen, Poland, United States')) ?></div></div>
    </div>
</section>

<!-- ===== What We Do ===== -->
<section class="whatwedo-section">
    <div class="wrap">
        <h2 class="whatwedo-heading"><?= e($c('whatwedo_heading', 'WHAT WE DO')) ?></h2>
        <div class="whatwedo-grid">
            <div class="whatwedo-image">
                <?php if ($url = image_url($images, 'about_whatwedo_image')): ?>
                    <img src="<?= e($url) ?>" alt="<?= e(image_alt($images, 'about_whatwedo_image', 'What we do')) ?>">
                <?php else: ?>
                    <div class="placeholder">Photo</div>
                <?php endif; ?>
            </div>
            <div class="whatwedo-items">
                <?php foreach ($whatWeDoItems as $item): ?>
                    <div class="whatwedo-item">
                        <span class="whatwedo-icon">
                            <svg viewBox="0 0 24 24"><path d="M12 2l3 6 6 .9-4.5 4.3 1 6-5.5-3-5.5 3 1-6L3 8.9 9 8z"/></svg>
                        </span>
                        <p><?= e($item) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- ===== Vision & Mission ===== -->
<section class="vision-mission-section">
    <div class="wrap">
        <div class="vision-mission">
            <div class="vm-card vm-vision">
                <h3><?= e($c('vision_heading', 'VISION')) ?></h3>
                <p><?= e($c('vision_text', 'Lorem ipsum dolor sit amet consectetur. Massa donec congue vitae nulla nisi tellus gravida. Eget dolor vulputate malesuada sed morbi sed. Ipsum massa quam elit at ultricies vestibulum. Sagittis etiam risus sagittis sed morbi aliquet integer nunc nibh.')) ?></p>
            </div>
            <div class="vm-card vm-mission">
                <h3><?= e($c('mission_heading', 'MISSION')) ?></h3>
                <p><?= e($c('mission_text', 'Lorem ipsum dolor sit amet consectetur. Massa donec congue vitae nulla nisi tellus gravida. Eget dolor vulputate malesuada sed morbi sed. Ipsum massa quam elit at ultricies vestibulum. Sagittis etiam risus sagittis sed morbi aliquet integer nunc nibh.')) ?></p>
            </div>
        </div>
        <div class="vm-images">
            <?php if ($url = image_url($images, 'about_vision_image_1')): ?>
                <img class="vm-image-1" src="<?= e($url) ?>" alt="<?= e(image_alt($images, 'about_vision_image_1', 'Vehicle')) ?>">
            <?php endif; ?>
            <?php if ($url = image_url($images, 'about_vision_image_2')): ?>
                <img class="vm-image-2" src="<?= e($url) ?>" alt="<?= e(image_alt($images, 'about_vision_image_2', 'Vehicle')) ?>">
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ===== Team / values ===== -->
<section class="team-section">
    <div class="team-grid">
        <?php foreach ($team as $member): ?>
            <div class="team-card">
                <?php if ($url = image_url($images, $member['slot'])): ?>
                    <img src="<?= e($url) ?>" alt="<?= e(image_alt($images, $member['slot'], 'Team member')) ?>">
                <?php else: ?>
                    <div class="placeholder">Photo</div>
                <?php endif; ?>
                <div class="team-caption">
                    <strong><?= e($member['title']) ?></strong>
                    <span><?= e($member['text']) ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ===== Bottom CTA + world map ===== -->
<section class="about-cta-section">
    <div class="wrap">
        <div class="about-cta-grid">
            <div class="about-cta-copy">
                <h2><?= e($c('about_cta_heading', 'LOREM IPSUM DOLOR SITUR.')) ?></h2>
                <p><?= e($c('about_cta_text', 'Lorem ipsum dolor sit amet consectetur. Vel eget a sem amet leo sollicitudin tellus. Amet nunc urna sed sociis viverra urna hendrerit fringilla.')) ?></p>
                <a href="<?= e(BASE_URL) ?>/contact.php" class="btn"><?= e($c('about_cta_button_label', 'Contact us')) ?> <span class="arrow">&rsaquo;</span></a>
            </div>
            <div class="about-cta-map">
                <?php if ($url = image_url($images, 'about_world_map')): ?>
                    <img src="<?= e($url) ?>" alt="<?= e(image_alt($images, 'about_world_map', 'World map')) ?>">
                <?php else: ?>
                    <div class="placeholder">World map</div>
                <?php endif; ?>
            </div>
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
                    <li><a href="<?= e(BASE_URL) ?>/index.php">Home</a></li>
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
