<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/admin/auth.php';
require_once __DIR__ . '/includes/maintenance.php';
require_once __DIR__ . '/includes/contact_form.php';

try {
    $images  = all_images();
    $content = all_content();
    $dealers = all_dealers(true);
} catch (Throwable $ex) {
    $images = []; $content = []; $dealers = [];
}

try {
    maybe_show_maintenance_page();
} catch (Throwable $ex) {
    // DB unreachable — fall through and render with placeholders.
}

$c = static fn(string $key, string $fallback = '') => content($content, $key, $fallback);

$formNotice = '';
$formError  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = handle_contact_submission($content);
    if ($result['ok']) {
        $formNotice = $result['message'];
    } else {
        $formError = $result['message'];
    }
}

$token = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ZXTec — Contact Us</title>
<meta name="description" content="Get in touch with ZXTec.">
<?php if ($url = image_url($images, 'favicon')): ?><link rel="icon" href="<?= e($url) ?>"><?php endif; ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo:wght@500;600;700;800;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/css/style.css?v=<?= (int) (@filemtime(__DIR__ . '/assets/css/style.css') ?: 1) ?>">
</head>
<body>

<!-- ===== Header ===== -->
<header class="site-header">
    <div class="wrap">
        <a class="brand" href="<?= e(BASE_URL) ?>/">
            <?php $logoDefaultUrl = image_url($images, 'logo_header'); $logoScrolledUrl = image_url($images, 'logo_footer'); ?>
            <?php if ($logoDefaultUrl && $logoScrolledUrl): ?>
                <img class="brand-logo-default" src="<?= e($logoDefaultUrl) ?>" alt="<?= e(image_alt($images, 'logo_header', 'ZXTec')) ?>">
                <img class="brand-logo-scrolled" src="<?= e($logoScrolledUrl) ?>" alt="<?= e(image_alt($images, 'logo_footer', 'ZXTec')) ?>">
            <?php elseif ($logoDefaultUrl): ?>
                <img src="<?= e($logoDefaultUrl) ?>" alt="<?= e(image_alt($images, 'logo_header', 'ZXTec')) ?>">
            <?php else: ?>
                <span class="brand-fallback"><span class="brand-mark">&#9883;</span>ZXTec</span>
            <?php endif; ?>
        </a>
        <nav class="nav" id="nav">
            <ul class="nav-links">
                <li><a href="<?= e(BASE_URL) ?>/">Home</a></li>
                <li><a href="<?= e(BASE_URL) ?>/about">About us</a></li>
                <li><a class="active" href="<?= e(BASE_URL) ?>/contact">Contact Us</a></li>
            </ul>
            <div class="nav-social">
                <a href="#" aria-label="Call"><svg viewBox="0 0 24 24"><path d="M6.6 10.8c1.4 2.8 3.8 5.1 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1C10.6 21 3 13.4 3 4c0-.6.4-1 1-1h3.4c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.4 0 .8-.2 1l-2.2 2.2z"/></svg></a>
                <a href="mailto:<?= e($c('contact_email', 'companyname@gamil.com')) ?>" aria-label="Email"><svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2zm8 7L4 6.5V8l8 4.5L20 8V6.5L12 11z"/></svg></a>
                <a href="#" aria-label="Facebook"><svg viewBox="0 0 24 24"><path d="M13 22v-8h2.7l.4-3H13V9c0-.9.3-1.5 1.6-1.5H16V4.9c-.3 0-1.2-.1-2.3-.1-2.3 0-3.7 1.3-3.7 3.8V11H7v3h3v8h3z"/></svg></a>
                <a href="#" aria-label="Instagram"><svg viewBox="0 0 24 24"><path d="M12 2.2c3.2 0 3.6 0 4.9.1 3.3.1 4.8 1.7 4.9 4.9.1 1.3.1 1.6.1 4.8s0 3.6-.1 4.8c-.1 3.2-1.6 4.8-4.9 4.9-1.3.1-1.6.1-4.9.1s-3.6 0-4.8-.1c-3.3-.1-4.8-1.7-4.9-4.9C2.2 15.6 2.2 15.2 2.2 12s0-3.6.1-4.8C2.4 4 4 2.4 7.2 2.3 8.4 2.2 8.8 2.2 12 2.2zm0 3.2A6.6 6.6 0 105.4 12 6.6 6.6 0 0012 5.4zm0 10.9A4.3 4.3 0 1116.3 12 4.3 4.3 0 0112 16.3zm6.8-11.1a1.5 1.5 0 11-1.5-1.5 1.5 1.5 0 011.5 1.5z"/></svg></a>
                <a href="#" aria-label="Twitter"><svg viewBox="0 0 24 24"><path d="M22 5.9c-.7.3-1.5.5-2.3.6.8-.5 1.5-1.3 1.8-2.3-.8.5-1.7.8-2.6 1a4.1 4.1 0 00-7 3.7A11.6 11.6 0 013.2 4.5a4.1 4.1 0 001.3 5.5c-.7 0-1.3-.2-1.9-.5a4.1 4.1 0 003.3 4c-.6.2-1.2.2-1.8.1a4.1 4.1 0 003.8 2.9A8.3 8.3 0 012 18.3a11.6 11.6 0 006.3 1.8c7.5 0 11.7-6.3 11.7-11.7v-.5c.8-.6 1.5-1.3 2-2z"/></svg></a>
            </div>
        </nav>
        <button class="nav-toggle" id="navToggle" aria-label="Menu">
            <svg viewBox="0 0 24 24"><path d="M3 6h18v2H3zM3 11h18v2H3zM3 16h18v2H3z"/></svg>
        </button>
    </div>
</header>

<!-- ===== Contact hero ===== -->
<section class="contact-hero">
    <?php if ($url = image_url($images, 'contact_hero_image')): ?>
        <img class="contact-hero-blob" data-parallax="0.15" src="<?= e($url) ?>" alt="<?= e(image_alt($images, 'contact_hero_image', '')) ?>">
    <?php else: ?>
        <div class="contact-hero-blob" data-parallax="0.15"></div>
    <?php endif; ?>
    <div class="wrap">
        <div class="contact-hero-text">
            <p class="hero-eyebrow"><?= e($c('contact_eyebrow', 'GET IN TOUCH')) ?></p>
            <h1 class="contact-title"><?= e($c('contact_title', 'CONTACT US')) ?></h1>
            <p class="contact-intro"><?= e($c('contact_intro', 'Lorem ipsum dolor sit amet consectetur. Erat dui rhoncus consectetur tincidunt. Mi felis odio consectetur est.')) ?></p>
        </div>
    </div>
</section>

<!-- ===== Get in touch form ===== -->
<section class="contact-form-section reveal">
    <div class="wrap">
        <div class="contact-form-grid">
            <div class="contact-form-left">
                <h2 class="form-heading"><?= e($c('form_heading', 'Send us a message')) ?></h2>
                <p class="form-subtext"><?= e($c('form_subtext', 'Do you have a question? A complaint? Or need any help? Feel free to contact us.')) ?></p>

                <?php if ($formNotice): ?><div class="alert ok form-alert"><?= e($formNotice) ?></div><?php endif; ?>
                <?php if ($formError): ?><div class="alert error form-alert"><?= e($formError) ?></div><?php endif; ?>

                <form method="post" class="contact-form">
                    <input type="hidden" name="csrf" value="<?= e($token) ?>">
                    <input type="text" name="website" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">

                    <div class="form-row">
                        <label class="form-field">First Name
                            <input type="text" name="first_name" placeholder="Enter your first name" required>
                        </label>
                        <label class="form-field">Last Name
                            <input type="text" name="last_name" placeholder="Enter your last name">
                        </label>
                    </div>
                    <div class="form-row">
                        <label class="form-field">Email
                            <input type="email" name="email" placeholder="Enter your email" required>
                        </label>
                        <label class="form-field">Contact Number
                            <input type="text" name="phone" placeholder="Enter your contact number">
                        </label>
                    </div>
                    <label class="form-field form-field-full">District
                        <?= district_select_html() ?>
                    </label>
                    <label class="form-field form-field-full">Message
                        <textarea name="message" rows="6" placeholder="Enter your message" required></textarea>
                    </label>

                    <div class="contact-form-actions">
                        <button type="submit" class="btn-send"><?= e($c('form_button_label', 'Send a Message')) ?> <span class="arrow">&rsaquo;</span></button>
                    </div>
                </form>
            </div>

            <div class="contact-side-card">
                <h3><?= e($c('contact_side_heading', "Hi! We are always here to help you.")) ?></h3>
                <div class="contact-side-items">
                    <div class="contact-side-item">
                        <span class="icon"><svg viewBox="0 0 24 24"><path d="M6.6 10.8c1.4 2.8 3.8 5.1 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1C10.6 21 3 13.4 3 4c0-.6.4-1 1-1h3.4c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.4 0 .8-.2 1l-2.2 2.2z"/></svg></span>
                        <div>
                            <div class="label">Hotline:</div>
                            <div class="value"><?= e($c('contact_phone', '(+391) 1234 8492')) ?></div>
                        </div>
                    </div>
                    <div class="contact-side-item">
                        <span class="icon"><svg viewBox="0 0 24 24"><path d="M4 4h16a2 2 0 012 2v10a2 2 0 01-2 2H9l-5 4V6a2 2 0 012-2z"/></svg></span>
                        <div>
                            <div class="label">SMS / WhatsApp:</div>
                            <div class="value"><?= e($c('whatsapp_phone', $c('contact_phone', '(+391) 1234 8492'))) ?></div>
                        </div>
                    </div>
                    <div class="contact-side-item">
                        <span class="icon"><svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2zm8 7L4 6.5V8l8 4.5L20 8V6.5L12 11z"/></svg></span>
                        <div>
                            <div class="label">Email:</div>
                            <div class="value"><?= e($c('contact_email', 'companyname@gamil.com')) ?></div>
                        </div>
                    </div>
                </div>
                <hr class="contact-side-divider">
                <div class="contact-side-connect">
                    <div class="lbl">Connect with us</div>
                    <div class="contact-side-social">
                        <a href="#" aria-label="Facebook"><svg viewBox="0 0 24 24"><path d="M13 22v-8h2.7l.4-3H13V9c0-.9.3-1.5 1.6-1.5H16V4.9c-.3 0-1.2-.1-2.3-.1-2.3 0-3.7 1.3-3.7 3.8V11H7v3h3v8h3z"/></svg></a>
                        <a href="#" aria-label="Instagram"><svg viewBox="0 0 24 24"><path d="M12 2.2c3.2 0 3.6 0 4.9.1 3.3.1 4.8 1.7 4.9 4.9.1 1.3.1 1.6.1 4.8s0 3.6-.1 4.8c-.1 3.2-1.6 4.8-4.9 4.9-1.3.1-1.6.1-4.9.1s-3.6 0-4.8-.1c-3.3-.1-4.8-1.7-4.9-4.9C2.2 15.6 2.2 15.2 2.2 12s0-3.6.1-4.8C2.4 4 4 2.4 7.2 2.3 8.4 2.2 8.8 2.2 12 2.2zm0 3.2A6.6 6.6 0 105.4 12 6.6 6.6 0 0012 5.4zm0 10.9A4.3 4.3 0 1116.3 12 4.3 4.3 0 0112 16.3zm6.8-11.1a1.5 1.5 0 11-1.5-1.5 1.5 1.5 0 011.5 1.5z"/></svg></a>
                        <a href="#" aria-label="Twitter"><svg viewBox="0 0 24 24"><path d="M22 5.9c-.7.3-1.5.5-2.3.6.8-.5 1.5-1.3 1.8-2.3-.8.5-1.7.8-2.6 1a4.1 4.1 0 00-7 3.7A11.6 11.6 0 013.2 4.5a4.1 4.1 0 001.3 5.5c-.7 0-1.3-.2-1.9-.5a4.1 4.1 0 003.3 4c-.6.2-1.2.2-1.8.1a4.1 4.1 0 003.8 2.9A8.3 8.3 0 012 18.3a11.6 11.6 0 006.3 1.8c7.5 0 11.7-6.3 11.7-11.7v-.5c.8-.6 1.5-1.3 2-2z"/></svg></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== Dealerships ===== -->
<?php if (!empty($dealers)): ?>
<section class="dealers-section reveal">
    <div class="wrap">
        <div class="dealers-head">
            <h2>Dealership Details</h2>
            <p><?= e($c('dealers_subtext', 'Find your nearest ZXTec dealer for sales, service, and test rides.')) ?></p>
        </div>
        <div class="dealers-grid">
            <?php foreach ($dealers as $d): ?>
                <div class="dealer-card">
                    <span class="dealer-icon"><svg viewBox="0 0 24 24"><path d="M12 2C8.1 2 5 5.1 5 9c0 5.2 7 13 7 13s7-7.8 7-13c0-3.9-3.1-7-7-7zm0 9.5A2.5 2.5 0 1114.5 9 2.5 2.5 0 0112 11.5z"/></svg></span>
                    <div class="dealer-body">
                        <h3><?= e($d['name']) ?></h3>
                        <?php if ($d['address']): ?><p class="dealer-address"><?= e($d['address']) ?></p><?php endif; ?>
                        <?php if ($d['phone']): ?><p class="dealer-phone"><?= e($d['phone']) ?></p><?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===== Map ===== -->
<section class="contact-map-section reveal">
    <div class="wrap">
        <div class="contact-map-caption">
            <h3><?= e($c('address_label', 'London Eye, UK')) ?></h3>
            <p><?= e($c('address_card_text', 'Lorem ipsum dolor sit amet consectetur. Laoreet id lorem ut velit aliquam facilisi ut fermentum elit.')) ?></p>
        </div>
    </div>
    <div class="contact-map">
        <iframe
            src="https://www.google.com/maps?q=<?= urlencode($c('contact_address', 'London Eye, London, UK')) ?>&output=embed"
            loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Location map"></iframe>
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
                    <li><a href="<?= e(BASE_URL) ?>/">Home</a></li>
                    <li><a href="<?= e(BASE_URL) ?>/about">About Us</a></li>
                    <li><a href="<?= e(BASE_URL) ?>/contact">Contact us</a></li>
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

<?php render_talk_to_team_widget($content, $token); ?>

<script src="<?= e(BASE_URL) ?>/assets/js/main.js?v=<?= (int) (@filemtime(__DIR__ . '/assets/js/main.js') ?: 1) ?>"></script>
</body>
</html>
