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

$formNotice = '';
$formError  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['website'])) {
        // Honeypot field caught a bot — pretend success, do nothing.
        $formNotice = $c('form_success_message', "Thanks for reaching out! We'll get back to you soon.");
    } elseif (!check_csrf()) {
        $formError = 'Your session expired. Please try submitting again.';
    } else {
        $first   = trim($_POST['first_name'] ?? '');
        $last    = trim($_POST['last_name'] ?? '');
        $phone   = trim($_POST['phone'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if ($first === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $formError = 'Please fill in your name, a valid email address, and a message.';
        } else {
            $to      = $c('contact_email', 'companyname@gamil.com');
            $subject = 'New website enquiry from ' . $first . ' ' . $last;
            $body    = "Name: $first $last\nPhone: $phone\nEmail: $email\n\nMessage:\n$message";
            $headers = 'Reply-To: ' . $email . "\r\n" . 'X-Mailer: PHP/' . phpversion();
            @mail($to, $subject, $body, $headers);
            $formNotice = $c('form_success_message', "Thanks for reaching out! We'll get back to you soon.");
        }
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
<link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/css/style.css">
</head>
<body>

<!-- ===== Header ===== -->
<header class="site-header">
    <div class="wrap">
        <a class="brand" href="<?= e(BASE_URL) ?>/index.php">
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
                <li><a href="<?= e(BASE_URL) ?>/index.php">Home</a></li>
                <li><a href="<?= e(BASE_URL) ?>/about.php">About us</a></li>
                <li><a class="active" href="<?= e(BASE_URL) ?>/contact.php">Contact Us</a></li>
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

<!-- ===== Contact hero ===== -->
<section class="contact-hero">
    <div class="contact-hero-blob" data-parallax="0.15"></div>
    <div class="wrap">
        <div class="contact-hero-text">
            <p class="hero-eyebrow"><?= e($c('contact_eyebrow', 'GET IN TOUCH')) ?></p>
            <h1 class="contact-title"><?= e($c('contact_title', 'CONTACT US')) ?></h1>
            <p class="contact-intro"><?= e($c('contact_intro', 'Lorem ipsum dolor sit amet consectetur. Erat dui rhoncus consectetur tincidunt. Mi felis odio consectetur est.')) ?></p>
        </div>
    </div>
</section>

<!-- ===== Info cards ===== -->
<section class="contact-cards-section reveal">
    <div class="wrap">
        <div class="contact-cards">
            <div class="contact-card">
                <div class="card-icon">
                    <svg viewBox="0 0 24 24"><path d="M6.6 10.8c1.4 2.8 3.8 5.1 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1C10.6 21 3 13.4 3 4c0-.6.4-1 1-1h3.4c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.4 0 .8-.2 1l-2.2 2.2z"/></svg>
                </div>
                <h3><?= e($c('contact_phone', '(+391) 1234 8492')) ?></h3>
                <p><?= e($c('phone_card_text', 'Lorem ipsum dolor sit amet consectetur. Laoreet id lorem ut velit aliquam facilisi ut fermentum elit.')) ?></p>
            </div>
            <div class="contact-card is-highlight">
                <div class="card-icon">
                    <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2zm8 7L4 6.5V8l8 4.5L20 8V6.5L12 11z"/></svg>
                </div>
                <h3><?= e($c('contact_email', 'companyname@gamil.com')) ?></h3>
                <p><?= e($c('email_card_text', 'Lorem ipsum dolor sit amet consectetur. Odio ultricies dis parturient vulputate sit eleifend semper eu penatibus. Ut sed sagittis sagittis orci vestibulum morbi sagittis tellus dui. Amet sit vitae.')) ?></p>
            </div>
            <div class="contact-card">
                <div class="card-icon">
                    <svg viewBox="0 0 24 24"><path d="M12 2C7.6 2 4 5.6 4 10c0 6 8 12 8 12s8-6 8-12c0-4.4-3.6-8-8-8zm0 11a3 3 0 110-6 3 3 0 010 6z"/></svg>
                </div>
                <h3><?= e($c('address_label', 'London Eye, UK')) ?></h3>
                <p><?= e($c('address_card_text', 'Lorem ipsum dolor sit amet consectetur. Laoreet id lorem ut velit aliquam facilisi ut fermentum elit.')) ?></p>
            </div>
        </div>
    </div>
</section>

<!-- ===== Get in touch form ===== -->
<section class="contact-form-section reveal">
    <div class="wrap">
        <h2 class="form-heading"><?= e($c('form_heading', 'GET IN TOUCH')) ?></h2>

        <?php if ($formNotice): ?><div class="alert ok form-alert"><?= e($formNotice) ?></div><?php endif; ?>
        <?php if ($formError): ?><div class="alert error form-alert"><?= e($formError) ?></div><?php endif; ?>

        <form method="post" class="contact-form">
            <input type="hidden" name="csrf" value="<?= e($token) ?>">
            <input type="text" name="website" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">

            <div class="form-row">
                <input type="text" name="first_name" placeholder="First Name" required>
                <input type="text" name="last_name" placeholder="Last Name">
            </div>
            <div class="form-row">
                <input type="text" name="phone" placeholder="Phone Number">
                <input type="email" name="email" placeholder="Email Address" required>
            </div>
            <textarea name="message" rows="6" placeholder="Type here......" required></textarea>

            <button type="submit" class="btn-send"><?= e($c('form_button_label', 'Send Message')) ?> <span class="arrow">&rsaquo;</span></button>
        </form>

        <div class="contact-social">
            <a href="#" aria-label="Facebook"><svg viewBox="0 0 24 24"><path d="M13 22v-8h2.7l.4-3H13V9c0-.9.3-1.5 1.6-1.5H16V4.9c-.3 0-1.2-.1-2.3-.1-2.3 0-3.7 1.3-3.7 3.8V11H7v3h3v8h3z"/></svg></a>
            <a href="#" aria-label="Instagram"><svg viewBox="0 0 24 24"><path d="M12 2.2c3.2 0 3.6 0 4.9.1 3.3.1 4.8 1.7 4.9 4.9.1 1.3.1 1.6.1 4.8s0 3.6-.1 4.8c-.1 3.2-1.6 4.8-4.9 4.9-1.3.1-1.6.1-4.9.1s-3.6 0-4.8-.1c-3.3-.1-4.8-1.7-4.9-4.9C2.2 15.6 2.2 15.2 2.2 12s0-3.6.1-4.8C2.4 4 4 2.4 7.2 2.3 8.4 2.2 8.8 2.2 12 2.2zm0 3.2A6.6 6.6 0 105.4 12 6.6 6.6 0 0012 5.4zm0 10.9A4.3 4.3 0 1116.3 12 4.3 4.3 0 0112 16.3zm6.8-11.1a1.5 1.5 0 11-1.5-1.5 1.5 1.5 0 011.5 1.5z"/></svg></a>
            <a href="#" aria-label="Twitter"><svg viewBox="0 0 24 24"><path d="M22 5.9c-.7.3-1.5.5-2.3.6.8-.5 1.5-1.3 1.8-2.3-.8.5-1.7.8-2.6 1a4.1 4.1 0 00-7 3.7A11.6 11.6 0 013.2 4.5a4.1 4.1 0 001.3 5.5c-.7 0-1.3-.2-1.9-.5a4.1 4.1 0 003.3 4c-.6.2-1.2.2-1.8.1a4.1 4.1 0 003.8 2.9A8.3 8.3 0 012 18.3a11.6 11.6 0 006.3 1.8c7.5 0 11.7-6.3 11.7-11.7v-.5c.8-.6 1.5-1.3 2-2z"/></svg></a>
        </div>
    </div>
</section>

<!-- ===== Map ===== -->
<section class="contact-map reveal">
    <iframe
        src="https://www.google.com/maps?q=<?= urlencode($c('contact_address', 'London Eye, London, UK')) ?>&output=embed"
        loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Location map"></iframe>
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
