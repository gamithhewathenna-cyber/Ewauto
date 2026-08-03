<?php
require_once __DIR__ . '/layout.php';
require_once __DIR__ . '/../includes/uploads.php';

$notice = '';
$error  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!check_csrf()) {
        $error = 'Session expired. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'add') {
            if (empty($_FILES['image']['name'])) {
                $error = 'Please choose an image for the new slide.';
            } else {
                $result = save_uploaded_image($_FILES['image'], 'slide');
                if (!$result['ok']) {
                    $error = $result['error'];
                } else {
                    $maxOrder = (int) db()->query('SELECT COALESCE(MAX(sort_order), 0) FROM slides')->fetchColumn();
                    db()->prepare('INSERT INTO slides (filename, alt_text, heading, subheading, link_url, sort_order, active)
                        VALUES (?, ?, ?, ?, ?, ?, 1)')
                        ->execute([
                            $result['filename'],
                            trim($_POST['alt_text'] ?? ''),
                            trim($_POST['heading'] ?? ''),
                            trim($_POST['subheading'] ?? ''),
                            trim($_POST['link_url'] ?? ''),
                            $maxOrder + 10,
                        ]);
                    $notice = 'Slide added.';
                }
            }
        } elseif ($action === 'update') {
            $id = (int) ($_POST['id'] ?? 0);
            $stmt = db()->prepare('SELECT * FROM slides WHERE id = ?');
            $stmt->execute([$id]);
            $row = $stmt->fetch();

            if (!$row) {
                $error = 'Slide not found.';
            } else {
                $newFilename = $row['filename'];
                if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $result = save_uploaded_image($_FILES['image'], 'slide');
                    if (!$result['ok']) {
                        $error = $result['error'];
                    } else {
                        delete_uploaded_image($row['filename']);
                        $newFilename = $result['filename'];
                    }
                }
                if (!$error) {
                    db()->prepare('UPDATE slides SET filename=?, alt_text=?, heading=?, subheading=?, link_url=?, sort_order=?, active=? WHERE id=?')
                        ->execute([
                            $newFilename,
                            trim($_POST['alt_text'] ?? ''),
                            trim($_POST['heading'] ?? ''),
                            trim($_POST['subheading'] ?? ''),
                            trim($_POST['link_url'] ?? ''),
                            (int) ($_POST['sort_order'] ?? 0),
                            isset($_POST['active']) ? 1 : 0,
                            $id,
                        ]);
                    $notice = 'Slide updated.';
                }
            }
        } elseif ($action === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            $stmt = db()->prepare('SELECT * FROM slides WHERE id = ?');
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if ($row) {
                delete_uploaded_image($row['filename']);
                db()->prepare('DELETE FROM slides WHERE id = ?')->execute([$id]);
                $notice = 'Slide deleted.';
            }
        }
    }
}

$slides = all_slides();
$token = csrf_token();

admin_header('slides', 'Slider Images');
?>
<p class="admin-lead">Manage the homepage hero slider. Slides show in order, lowest number first. If no slides are added, the homepage falls back to the single hero image set in Page Content.</p>

<?php if ($notice): ?><div class="alert ok"><?= e($notice) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

<div class="image-grid">
    <?php foreach ($slides as $s): ?>
        <div class="image-card">
            <div class="image-card-head">
                <h3>Slide #<?= (int) $s['id'] ?></h3>
                <code><?= $s['active'] ? 'active' : 'hidden' ?></code>
            </div>
            <div class="preview">
                <?php if ($s['filename']): ?>
                    <img src="<?= e(UPLOAD_URL . '/' . rawurlencode($s['filename'])) ?>" alt="<?= e($s['alt_text']) ?>">
                <?php else: ?>
                    <div class="preview-empty">No image yet</div>
                <?php endif; ?>
            </div>
            <form method="post" enctype="multipart/form-data" class="image-form">
                <input type="hidden" name="csrf" value="<?= e($token) ?>">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                <label class="field">Replace image
                    <input type="file" name="image" accept="image/*">
                </label>
                <label class="field">Alt text
                    <input type="text" name="alt_text" value="<?= e($s['alt_text']) ?>">
                </label>
                <label class="field">Heading (optional)
                    <input type="text" name="heading" value="<?= e($s['heading']) ?>">
                </label>
                <label class="field">Subheading (optional)
                    <input type="text" name="subheading" value="<?= e($s['subheading']) ?>">
                </label>
                <label class="field">Link URL (optional)
                    <input type="text" name="link_url" value="<?= e($s['link_url']) ?>">
                </label>
                <label class="field">Order
                    <input type="number" name="sort_order" value="<?= (int) $s['sort_order'] ?>">
                </label>
                <label class="toggle-row">
                    <input type="checkbox" name="active" value="1" <?= $s['active'] ? 'checked' : '' ?>>
                    <span>Active</span>
                </label>
                <div class="image-actions">
                    <button type="submit" class="btn-primary">Save</button>
                    <button type="submit" name="action" value="delete" class="btn-danger"
                            onclick="return confirm('Delete this slide?');">Delete</button>
                </div>
            </form>
        </div>
    <?php endforeach; ?>

    <div class="image-card">
        <div class="image-card-head"><h3>Add new slide</h3></div>
        <form method="post" enctype="multipart/form-data" class="image-form">
            <input type="hidden" name="csrf" value="<?= e($token) ?>">
            <input type="hidden" name="action" value="add">
            <label class="field">Image file
                <input type="file" name="image" accept="image/*" required>
            </label>
            <label class="field">Alt text
                <input type="text" name="alt_text">
            </label>
            <label class="field">Heading (optional)
                <input type="text" name="heading">
            </label>
            <label class="field">Subheading (optional)
                <input type="text" name="subheading">
            </label>
            <label class="field">Link URL (optional)
                <input type="text" name="link_url">
            </label>
            <div class="image-actions">
                <button type="submit" class="btn-primary">Add slide</button>
            </div>
        </form>
    </div>
</div>
<?php
admin_footer();
