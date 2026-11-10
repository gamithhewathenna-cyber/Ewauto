<?php
require_once __DIR__ . '/layout.php';
require_once __DIR__ . '/../includes/uploads.php';
require_once __DIR__ . '/../includes/social.php';
require_once __DIR__ . '/../includes/seo.php';

const BRAND_IMAGE_SLOTS = [
    'logo_header' => ['label' => 'Header logo', 'size' => '240 × 60px, transparent PNG/SVG recommended'],
    'logo_footer' => ['label' => 'Footer logo', 'size' => '240 × 60px, transparent PNG/SVG recommended'],
    'favicon'     => ['label' => 'Favicon', 'size' => '512 × 512px, square PNG/SVG'],
];

$notice = '';
$error  = '';
$socialInput = []; // re-filled form values when a social link fails validation
$seoInput    = []; // same, for the Google & search form

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!check_csrf()) {
        $error = 'Session expired. Please try again.';
    } else {
        $action = $_POST['action'] ?? 'save_general';

        if ($action === 'save_general') {
            set_setting('maintenance_mode', isset($_POST['maintenance_mode']) ? '1' : '0');
            set_setting('maintenance_message', trim($_POST['maintenance_message'] ?? ''));
            set_setting('site_title', trim($_POST['site_title'] ?? ''));
            $notice = 'Settings saved.';
        } elseif ($action === 'save_social') {
            // Validate everything first so a single bad link saves nothing.
            $clean  = [];
            $errors = [];
            foreach (array_keys(social_platforms()) as $key) {
                $result = normalize_social_url($key, $_POST['social_' . $key] ?? '');
                $clean[$key] = $result['url'];
                if ($result['error']) {
                    $errors[] = $result['error'];
                }
            }
            if ($errors) {
                $error = implode(' ', $errors) . ' Nothing was saved.';
                $socialInput = $clean;
            } else {
                foreach ($clean as $key => $url) {
                    set_setting('social_' . $key, $url);
                }
                $notice = 'Social media links saved.';
            }
        } elseif ($action === 'save_seo') {
            $verifyRaw = trim($_POST['google_site_verification'] ?? '');
            $gaRaw     = trim($_POST['ga_measurement_id'] ?? '');
            $verify    = normalize_site_verification($verifyRaw);
            $gaId      = normalize_ga_id($gaRaw);

            if ($verifyRaw !== '' && $verify === '') {
                $error = 'The Google Search Console code doesn\'t look right - paste the whole <meta> tag or just the code inside content="...". Nothing was saved.';
                $seoInput = $_POST;
            } elseif ($gaRaw !== '' && $gaId === '') {
                $error = 'The Google Analytics ID should look like G-XXXXXXXXXX. Nothing was saved.';
                $seoInput = $_POST;
            } else {
                foreach (array_keys(seo_pages()) as $pageKey) {
                    set_setting('seo_' . $pageKey . '_title', trim($_POST['seo_' . $pageKey . '_title'] ?? ''));
                    set_setting('seo_' . $pageKey . '_description', trim($_POST['seo_' . $pageKey . '_description'] ?? ''));
                }
                set_setting('google_site_verification', $verify);
                set_setting('ga_measurement_id', $gaId);
                $notice = 'Google & search settings saved.';
            }
        } elseif ($action === 'save_image') {
            $slot = $_POST['slot'] ?? '';
            if (!isset(BRAND_IMAGE_SLOTS[$slot])) {
                $error = 'Unknown image slot.';
            } else {
                $stmt = db()->prepare('SELECT * FROM images WHERE slot = ? LIMIT 1');
                $stmt->execute([$slot]);
                $row = $stmt->fetch();

                if (!$row) {
                    $error = 'Image slot not found. Re-run the database setup script.';
                } else {
                    $newFilename = $row['filename'];
                    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                        $result = save_uploaded_image($_FILES['image'], $slot);
                        if (!$result['ok']) {
                            $error = $result['error'];
                        } else {
                            delete_uploaded_image($row['filename']);
                            $newFilename = $result['filename'];
                        }
                    }
                    if (!$error) {
                        db()->prepare('UPDATE images SET filename = ?, alt_text = ? WHERE slot = ?')
                            ->execute([$newFilename, trim($_POST['alt_text'] ?? $row['alt_text']), $slot]);
                        $notice = BRAND_IMAGE_SLOTS[$slot]['label'] . ' updated.';
                    }
                }
            }
        }
    }
}

$settings = all_settings();
$images   = all_images();
$token    = csrf_token();

admin_header('settings', 'Settings');
?>
<p class="admin-lead">Site-wide settings: maintenance mode, social media links, and branding.</p>

<?php if ($notice): ?><div class="alert ok"><?= e($notice) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

<form method="post" class="settings-form">
    <input type="hidden" name="csrf" value="<?= e($token) ?>">
    <input type="hidden" name="action" value="save_general">

    <div class="card">
        <h3>Maintenance mode</h3>
        <label class="toggle-row">
            <input type="checkbox" name="maintenance_mode" value="1" <?= ($settings['maintenance_mode'] ?? '0') === '1' ? 'checked' : '' ?>>
            <span>Show a "be right back" page to every visitor except logged-in admins</span>
        </label>
        <label class="field">Maintenance message
            <textarea name="maintenance_message" rows="3"><?= e($settings['maintenance_message'] ?? '') ?></textarea>
        </label>
    </div>

    <div class="card">
        <h3>General</h3>
        <label class="field">Site title
            <input type="text" name="site_title" value="<?= e($settings['site_title'] ?? '') ?>">
        </label>
    </div>

    <button type="submit" class="btn-primary">Save settings</button>
</form>

<div class="card">
    <h3>Social media links</h3>
    <p class="admin-lead">Shown as icons in the header, the footer, and the Contact page on every page of the site. Leave a field blank to hide that icon.</p>
    <form method="post" class="settings-form">
        <input type="hidden" name="csrf" value="<?= e($token) ?>">
        <input type="hidden" name="action" value="save_social">

        <?php foreach (social_platforms() as $key => $platform): ?>
            <label class="field"><?= e($platform['label']) ?>
                <input type="text" name="social_<?= e($key) ?>" inputmode="url"
                       value="<?= e($socialInput[$key] ?? ($settings['social_' . $key] ?? '')) ?>"
                       placeholder="<?= e($platform['placeholder']) ?>">
            </label>
        <?php endforeach; ?>

        <button type="submit" class="btn-primary">Save social links</button>
    </form>
</div>

<div class="card">
    <h3>Google &amp; search</h3>
    <p class="admin-lead">How each page appears in Google results and when shared on Facebook / WhatsApp. Leave a field empty to use the default shown in grey. Aim for titles under 60 characters and descriptions under 160.</p>
    <form method="post" class="settings-form">
        <input type="hidden" name="csrf" value="<?= e($token) ?>">
        <input type="hidden" name="action" value="save_seo">

        <?php foreach (seo_pages() as $pageKey => $page): ?>
            <h4 class="spec-group-title span-all"><?= e($page['label']) ?> page <span class="field-hint">(<?= e(SITE_URL . $page['path']) ?>)</span></h4>
            <label class="field">Title in Google
                <input type="text" name="seo_<?= e($pageKey) ?>_title" maxlength="70"
                       value="<?= e($seoInput['seo_' . $pageKey . '_title'] ?? ($settings['seo_' . $pageKey . '_title'] ?? '')) ?>"
                       placeholder="<?= e($page['title']) ?>">
            </label>
            <label class="field">Description in Google
                <textarea name="seo_<?= e($pageKey) ?>_description" rows="2" maxlength="170"
                          placeholder="<?= e($page['description']) ?>"><?= e($seoInput['seo_' . $pageKey . '_description'] ?? ($settings['seo_' . $pageKey . '_description'] ?? '')) ?></textarea>
            </label>
        <?php endforeach; ?>

        <h4 class="spec-group-title span-all">Google tools</h4>
        <label class="field">Google Search Console verification <span class="field-hint">(paste the &lt;meta&gt; tag Google gives you)</span>
            <input type="text" name="google_site_verification"
                   value="<?= e($seoInput['google_site_verification'] ?? ($settings['google_site_verification'] ?? '')) ?>"
                   placeholder='<meta name="google-site-verification" content="...">'>
        </label>
        <label class="field">Google Analytics measurement ID <span class="field-hint">(optional)</span>
            <input type="text" name="ga_measurement_id"
                   value="<?= e($seoInput['ga_measurement_id'] ?? ($settings['ga_measurement_id'] ?? '')) ?>"
                   placeholder="G-XXXXXXXXXX">
        </label>

        <button type="submit" class="btn-primary">Save Google settings</button>
    </form>
</div>

<div class="card">
    <h3>Branding</h3>
    <p class="admin-lead">Upload your logo, footer logo, and browser favicon (PNG or SVG recommended, square for the favicon).</p>
    <div class="image-grid image-grid-compact">
        <?php foreach (BRAND_IMAGE_SLOTS as $slot => $slotMeta): $img = $images[$slot] ?? null; ?>
            <div class="image-card">
                <div class="image-card-head">
                    <h4><?= e($slotMeta['label']) ?></h4>
                    <code><?= e($slot) ?></code>
                </div>
                <p class="size-hint">Recommended size: <?= e($slotMeta['size']) ?></p>
                <div class="preview">
                    <?php if ($img && $img['filename']): ?>
                        <img src="<?= e(UPLOAD_URL . '/' . rawurlencode($img['filename'])) ?>" alt="<?= e($img['alt_text'] ?? '') ?>">
                    <?php else: ?>
                        <div class="preview-empty">No image yet</div>
                    <?php endif; ?>
                </div>
                <form method="post" enctype="multipart/form-data" class="image-form">
                    <input type="hidden" name="csrf" value="<?= e($token) ?>">
                    <input type="hidden" name="slot" value="<?= e($slot) ?>">
                    <input type="hidden" name="action" value="save_image">
                    <label class="field">Image file
                        <input type="file" name="image" accept="image/*">
                    </label>
                    <label class="field">Alt text
                        <input type="text" name="alt_text" value="<?= e($img['alt_text'] ?? '') ?>">
                    </label>
                    <div class="image-actions">
                        <button type="submit" class="btn-primary">Save</button>
                    </div>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="card">
    <h3>Accounts</h3>
    <p class="admin-lead">Your own username and password are under <a href="account.php" class="link">My account</a>. To add people or change what they can do, go to <a href="users.php" class="link">Users</a>.</p>
</div>
<?php
admin_footer();
