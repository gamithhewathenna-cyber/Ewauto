<?php
/**
 * sitemap.xml for Google (served at /sitemap.xml via .htaccess).
 * Built live from the database: every public page, when it last changed,
 * and the photos on it (bike colours, slider, page images) so they can show
 * up in Google Images. New pages: add them to seo_pages() in includes/seo.php.
 */
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/seo.php';

// Image slots that appear on each page.
const SITEMAP_PAGE_IMAGES = [
    'home'    => ['hero_scooter', 'lineup_vehicles', 'world_map', 'testimonial_avatar', 'cta_rider'],
    'about'   => ['about_hero_image', 'about_story_image', 'about_stats_bg', 'about_whatwedo_image',
                  'about_vision_image_1', 'about_vision_image_2', 'about_banner_image', 'about_world_map'],
    'contact' => ['contact_hero_image'],
];

/** Newest of a list of date strings / unix times, as W3C date (or null). */
function newest_date(array $dates): ?string
{
    $times = array_filter(array_map(static fn($d) => is_int($d) ? $d : strtotime((string) $d), $dates));
    return $times ? gmdate('Y-m-d', max($times)) : null;
}

$pages = [];
try {
    $images        = all_images();
    $contentEdited = get_setting('content_updated_at');

    foreach (seo_pages() as $key => $page) {
        $file  = __DIR__ . '/' . ($key === 'home' ? 'index' : $key) . '.php';
        $dates = [@filemtime($file) ?: null, $contentEdited];
        $pics  = [];

        foreach (SITEMAP_PAGE_IMAGES[$key] ?? [] as $slot) {
            if (!empty($images[$slot]['filename'])) {
                $pics[]  = image_url($images, $slot);
                $dates[] = $images[$slot]['updated_at'] ?? null;
            }
        }

        if ($key === 'home') {
            foreach (all_slides(true) as $s) {
                if ($s['filename']) {
                    $pics[]  = UPLOAD_URL . '/' . rawurlencode($s['filename']);
                    $dates[] = $s['created_at'];
                }
            }
            foreach (all_bikes(true) as $b) {
                $dates[] = $b['created_at'];
                foreach (bike_colors((int) $b['id']) as $col) {
                    if ($col['filename']) {
                        $pics[]  = UPLOAD_URL . '/' . rawurlencode($col['filename']);
                        $dates[] = $col['created_at'];
                    }
                }
            }
        }
        if ($key === 'home' || $key === 'contact') {
            if (dealers_section_enabled()) {
                $dates[] = db()->query('SELECT MAX(created_at) FROM dealers WHERE active = 1')->fetchColumn();
            }
        }

        $pages[] = [
            'loc'     => SITE_URL . $page['path'],
            'lastmod' => newest_date($dates),
            'images'  => array_values(array_unique(array_map('absolute_url', $pics))),
        ];
    }
} catch (Throwable $ex) {
    // Database unreachable: still list the pages so Google can crawl them.
    $pages = array_map(static fn($p) => ['loc' => SITE_URL . $p['path'], 'lastmod' => null, 'images' => []], array_values(seo_pages()));
}

header('Content-Type: application/xml; charset=UTF-8');
header('X-Robots-Tag: noindex'); // the sitemap itself shouldn't appear in search results
$x = static fn(string $s) => htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');

echo '<?xml version="1.0" encoding="UTF-8"?>', "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">', "\n";
foreach ($pages as $p) {
    echo "  <url>\n";
    echo '    <loc>', $x($p['loc']), "</loc>\n";
    if ($p['lastmod']) {
        echo '    <lastmod>', $p['lastmod'], "</lastmod>\n";
    }
    foreach ($p['images'] as $img) {
        echo '    <image:image><image:loc>', $x($img), "</image:loc></image:image>\n";
    }
    echo "  </url>\n";
}
echo "</urlset>\n";
