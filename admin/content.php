<?php
require_once __DIR__ . '/layout.php';
require_once __DIR__ . '/../includes/content_fields.php';
require_once __DIR__ . '/../includes/uploads.php';

$sections = content_field_sections();
$notice = '';
$error  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!check_csrf()) {
        $error = 'Session expired. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'save_text') {
            foreach ($sections as $section) {
                foreach (content_section_fields($section) as $key => $meta) {
                    if (isset($_POST[$key])) {
                        set_content($key, trim($_POST[$key]));
                    }
                }
            }
            $notice = 'Page text saved.';
        } elseif ($action === 'save_group') {
            // A "group" card saves its image (if any) and its text fields
            // together in one submit.
            $slot = $_POST['slot'] ?? '';

            if ($slot !== '') {
                $stmt = db()->prepare('SELECT * FROM images WHERE slot = ? LIMIT 1');
                $stmt->execute([$slot]);
                $row = $stmt->fetch();

                if (!$row) {
                    $error = 'Unknown image slot.';
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
                            ->execute([$newFilename, trim($_POST['alt_text'] ?? ''), $slot]);
                    }
                }
            }

            if (!$error) {
                $fieldKeys = array_filter(explode(',', $_POST['field_keys'] ?? ''));
                foreach ($fieldKeys as $key) {
                    if (isset($_POST[$key])) {
                        set_content($key, trim($_POST[$key]));
                    }
                }
                $notice = 'Saved.';
            }
        } elseif ($action === 'save_image') {
            $slot = $_POST['slot'] ?? '';
            $stmt = db()->prepare('SELECT * FROM images WHERE slot = ? LIMIT 1');
            $stmt->execute([$slot]);
            $row = $stmt->fetch();

            if (!$row) {
                $error = 'Unknown image slot.';
            } else {
                $alt = trim($_POST['alt_text'] ?? '');
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
                        ->execute([$newFilename, $alt, $slot]);
                    $notice = 'Saved "' . $row['label'] . '".';
                }
            }
        } elseif ($action === 'delete_image') {
            $slot = $_POST['slot'] ?? '';
            $stmt = db()->prepare('SELECT * FROM images WHERE slot = ? LIMIT 1');
            $stmt->execute([$slot]);
            $row = $stmt->fetch();
            if ($row) {
                delete_uploaded_image($row['filename']);
                db()->prepare('UPDATE images SET filename = NULL WHERE slot = ?')->execute([$slot]);
                $notice = 'Image removed for "' . $row['label'] . '".';
            }
        }
    }
}

$contentValues = all_content();
$images = all_images();
$token = csrf_token();

$pageTabs = [
    'home'    => 'Home Content',
    'about'   => 'About Content',
    'contact' => 'Contact Us Content',
];
$activePage = $_GET['page'] ?? 'home';
if (!isset($pageTabs[$activePage])) {
    $activePage = 'home';
}
$visibleSections = array_filter($sections, static fn($s) => ($s['page'] ?? 'home') === $activePage);
$formAction = 'content.php?page=' . e($activePage);

admin_header('content', 'Page Content Change');
?>
<p class="admin-lead">Edit the text and images shown on each page. Changes appear on the live site immediately.</p>

<div class="tab-nav">
    <?php foreach ($pageTabs as $key => $label): ?>
        <a href="content.php?page=<?= e($key) ?>" class="<?= $key === $activePage ? 'is-active' : '' ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
</div>

<?php if ($notice): ?><div class="alert ok"><?= e($notice) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

<?php foreach ($visibleSections as $section): ?>
    <div class="card">
        <h3><?= e($section['title']) ?></h3>

        <?php if (!empty($section['groups'])): ?>
            <!-- Grouped layout: each card below is one image + its related text, saved together. -->
            <div class="content-groups">
                <?php foreach ($section['groups'] as $group):
                    $img = null;
                    if (!empty($group['image'])) {
                        $img = $images[$group['image']['slot']] ?? null;
                    }
                    $fieldKeys = implode(',', array_keys($group['fields'] ?? []));
                ?>
                    <div class="content-group">
                        <?php if (!empty($group['image'])): ?>
                            <div class="content-group-media">
                                <div class="preview">
                                    <?php if ($img && $img['filename']): ?>
                                        <img src="<?= e(UPLOAD_URL . '/' . rawurlencode($img['filename'])) ?>" alt="<?= e($img['alt_text'] ?? '') ?>">
                                    <?php else: ?>
                                        <div class="preview-empty">No image yet</div>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($group['image']['size'])): ?><p class="size-hint"><?= e($group['image']['size']) ?></p><?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="<?= $formAction ?>" enctype="multipart/form-data" class="content-group-body">
                            <input type="hidden" name="csrf" value="<?= e($token) ?>">
                            <input type="hidden" name="action" value="save_group">
                            <input type="hidden" name="field_keys" value="<?= e($fieldKeys) ?>">
                            <h4><?= e($group['title']) ?></h4>

                            <?php if (!empty($group['image'])): $slot = $group['image']['slot']; ?>
                                <input type="hidden" name="slot" value="<?= e($slot) ?>">
                                <div class="content-group-fields">
                                    <label class="field"><?= e($group['image']['label']) ?>
                                        <input type="file" name="image" accept="image/*">
                                    </label>
                                    <label class="field">Image alt text
                                        <input type="text" name="alt_text" value="<?= e($img['alt_text'] ?? '') ?>">
                                    </label>
                                </div>
                            <?php endif; ?>

                            <div class="content-group-fields">
                                <?php foreach ($group['fields'] as $key => $meta): ?>
                                    <label class="field">
                                        <?= e($meta['label']) ?>
                                        <?php if ($meta['type'] === 'textarea'): ?>
                                            <textarea name="<?= e($key) ?>" rows="3"><?= e($contentValues[$key] ?? '') ?></textarea>
                                        <?php else: ?>
                                            <input type="text" name="<?= e($key) ?>" value="<?= e($contentValues[$key] ?? '') ?>">
                                        <?php endif; ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                            <div class="image-actions">
                                <button type="submit" class="btn-primary">Save</button>
                                <?php if (!empty($group['image']) && $img && $img['filename']): ?>
                                    <button type="submit" name="action" value="delete_image" class="btn-danger"
                                            onclick="return confirm('Remove this image?');">Remove image</button>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Flat layout (Home Content sections): images grid, then all text fields together. -->
            <?php if (!empty($section['images'])): ?>
                <div class="image-grid image-grid-compact">
                    <?php foreach ($section['images'] as $slot => $slotMeta): $img = $images[$slot] ?? null; ?>
                        <div class="image-card">
                            <div class="image-card-head">
                                <h4><?= e($slotMeta['label']) ?></h4>
                                <code><?= e($slot) ?></code>
                            </div>
                            <?php if (!empty($slotMeta['size'])): ?><p class="size-hint">Recommended size: <?= e($slotMeta['size']) ?></p><?php endif; ?>
                            <div class="preview">
                                <?php if ($img && $img['filename']): ?>
                                    <img src="<?= e(UPLOAD_URL . '/' . rawurlencode($img['filename'])) ?>" alt="<?= e($img['alt_text'] ?? '') ?>">
                                <?php else: ?>
                                    <div class="preview-empty">No image yet</div>
                                <?php endif; ?>
                            </div>
                            <form method="post" action="<?= $formAction ?>" enctype="multipart/form-data" class="image-form">
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
                                    <?php if ($img && $img['filename']): ?>
                                        <button type="submit" name="action" value="delete_image" class="btn-danger"
                                                onclick="return confirm('Remove this image?');">Remove</button>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= $formAction ?>" class="content-fields">
                <input type="hidden" name="csrf" value="<?= e($token) ?>">
                <input type="hidden" name="action" value="save_text">
                <?php foreach ($section['fields'] as $key => $meta): ?>
                    <label class="field">
                        <?= e($meta['label']) ?>
                        <?php if ($meta['type'] === 'textarea'): ?>
                            <textarea name="<?= e($key) ?>" rows="3"><?= e($contentValues[$key] ?? '') ?></textarea>
                        <?php else: ?>
                            <input type="text" name="<?= e($key) ?>" value="<?= e($contentValues[$key] ?? '') ?>">
                        <?php endif; ?>
                    </label>
                <?php endforeach; ?>
                <button type="submit" class="btn-primary">Save text</button>
            </form>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
<?php
admin_footer();
