<?php
/**
 * Search-engine and social-sharing tags for the public pages: title,
 * description, canonical URL, Open Graph / Twitter card, organisation
 * structured data (JSON-LD), plus optional Google Search Console
 * verification and Google Analytics 4. Titles, descriptions and the Google
 * codes are editable in Admin > Settings (setting keys seo_*).
 */

/** The indexable public pages, with default titles/descriptions. */
function seo_pages(): array
{
    return [
        'home' => [
            'label'       => 'Home',
            'path'        => '/',
            'title'       => 'EW Auto | Electric Scooters & E-Bikes in Sri Lanka',
            'description' => 'EW Auto electric scooters, e-bikes and e-mobility in Sri Lanka. Explore our bike range, find your nearest dealer and book a test ride.',
        ],
        'about' => [
            'label'       => 'About Us',
            'path'        => '/about',
            'title'       => 'About Us | EW Auto',
            'description' => 'Who EW Auto is, our story, and the electric scooters, e-bikes and mobility solutions we bring to our customers.',
        ],
        'contact' => [
            'label'       => 'Contact Us',
            'path'        => '/contact',
            'title'       => 'Contact Us | EW Auto',
            'description' => 'Contact EW Auto: find your nearest dealer, call or message our team, or book a test ride.',
        ],
    ];
}

/** Turn a site-relative path (e.g. /uploads/x.png) into a full https:// URL. */
function absolute_url(string $path): string
{
    if ($path === '' || preg_match('#^https?://#i', $path)) {
        return $path;
    }
    return SITE_URL . '/' . ltrim($path, '/');
}

/** Accepts the bare code or the whole <meta ...> tag Google gives you. */
function normalize_site_verification(string $value): string
{
    $value = trim($value);
    if (preg_match('/content\s*=\s*["\']([^"\']+)["\']/i', $value, $m)) {
        $value = $m[1];
    }
    return preg_match('/^[A-Za-z0-9_\-]{10,100}$/', $value) ? $value : '';
}

/** GA4 measurement IDs look like G-XXXXXXXXXX. Returns '' if invalid. */
function normalize_ga_id(string $value): string
{
    $value = strtoupper(trim($value));
    return preg_match('/^G-[A-Z0-9]{4,20}$/', $value) ? $value : '';
}

/**
 * Print everything that belongs in <head> for SEO. $ogImage is a
 * site-relative image path used for link previews (falls back to the logo).
 */
function render_seo_head(string $pageKey, array $images, array $content, string $ogImage = ''): void
{
    $page = seo_pages()[$pageKey];
    try {
        $title       = get_setting('seo_' . $pageKey . '_title') ?: $page['title'];
        $description = get_setting('seo_' . $pageKey . '_description') ?: $page['description'];
        $verify      = get_setting('google_site_verification');
        $gaId        = get_setting('ga_measurement_id');
    } catch (Throwable $ex) { // DB unreachable - fall back to defaults
        $title = $page['title']; $description = $page['description']; $verify = ''; $gaId = '';
    }
    $canonical = SITE_URL . $page['path'];
    $logo      = absolute_url(image_url($images, 'logo_header'));
    $shareImg  = absolute_url($ogImage) ?: $logo;
    ?>
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($description) ?>">
<link rel="canonical" href="<?= e($canonical) ?>">
<meta property="og:type" content="website">
<meta property="og:site_name" content="EW Auto">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($description) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<?php if ($shareImg): ?>
<meta property="og:image" content="<?= e($shareImg) ?>">
<?php endif; ?>
<meta name="twitter:card" content="<?= $shareImg ? 'summary_large_image' : 'summary' ?>">
<?php if ($verify): ?>
<meta name="google-site-verification" content="<?= e($verify) ?>">
<?php endif; ?>
<?php
    // Organisation details for Google (Home page only): name, logo,
    // contact and social profiles - helps Google show the business properly.
    if ($pageKey === 'home') {
        $org = [
            '@context' => 'https://schema.org',
            '@type'    => 'Organization',
            'name'     => 'EW Auto',
            'url'      => SITE_URL . '/',
        ];
        if ($logo) {
            $org['logo'] = $logo;
        }
        $email = content($content, 'contact_email');
        $phone = content($content, 'contact_phone');
        if ($email || $phone) {
            $contactPoint = ['@type' => 'ContactPoint', 'contactType' => 'customer service'];
            if ($phone) { $contactPoint['telephone'] = $phone; }
            if ($email) { $contactPoint['email'] = $email; }
            $org['contactPoint'] = [$contactPoint];
        }
        $sameAs = array_column(social_links(), 'url');
        if ($sameAs) {
            $org['sameAs'] = $sameAs;
        }
        echo '<script type="application/ld+json">'
            . json_encode($org, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP)
            . "</script>\n";
    }

    if ($gaId): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($gaId) ?>"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', <?= json_encode($gaId) ?>);
</script>
<?php endif;
}
