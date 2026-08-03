<?php
require_once __DIR__ . '/layout.php';
require_once __DIR__ . '/../includes/uploads.php';

$bikeId = (int) ($_GET['bike_id'] ?? $_POST['bike_id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM bikes WHERE id = ?');
$stmt->execute([$bikeId]);
$bike = $stmt->fetch();

if (!$bike) {
    header('Location: bikes.php');
    exit;
}

$notice = '';
$error  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!check_csrf()) {
        $error = 'Session expired. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'add') {
            $colorName = trim($_POST['color_name'] ?? '');
            $colorHex  = trim($_POST['color_hex'] ?? '#161616');
            if ($colorName === '') {
                $error = 'Colour name is required.';
            } elseif (empty($_FILES['image']['name'])) {
                $error = 'Please choose an image for this colour.';
            } else {
                $result = save_uploaded_image($_FILES['image'], 'bikecolor');
                if (!$result['ok']) {
                    $error = $result['error'];
                } else {
                    $stmt2 = db()->prepare('SELECT COALESCE(MAX(sort_order), 0) FROM bike_colors WHERE bike_id = ?');
                    $stmt2->execute([$bikeId]);
                    $maxOrder = (int) $stmt2->fetchColumn();

                    db()->prepare('INSERT INTO bike_colors (bike_id, color_name, color_hex, filename, alt_text, sort_order)
                        VALUES (?, ?, ?, ?, ?, ?)')
                        ->execute([
                            $bikeId, $colorName, $colorHex, $result['filename'],
                            trim($_POST['alt_text'] ?? ($bike['name'] . ' - ' . $colorName)),
                            $maxOrder + 10,
                        ]);
                    $notice = 'Colour added.';
                }
            }
        } elseif ($action === 'update') {
            $id = (int) ($_POST['id'] ?? 0);
            $cStmt = db()->prepare('SELECT * FROM bike_colors WHERE id = ? AND bike_id = ?');
            $cStmt->execute([$id, $bikeId]);
            $row = $cStmt->fetch();

            if (!$row) {
                $error = 'Colour not found.';
            } else {
                $newFilename = $row['filename'];
                if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $result = save_uploaded_image($_FILES['image'], 'bikecolor');
                    if (!$result['ok']) {
                        $error = $result['error'];
                    } else {
                        delete_uploaded_image($row['filename']);
                        $newFilename = $result['filename'];
                    }
                }
                if (!$error) {
                    db()->prepare('UPDATE bike_colors SET color_name=?, color_hex=?, filename=?, alt_text=?, sort_order=? WHERE id=?')
                        ->execute([
                            trim($_POST['color_name'] ?? $row['color_name']),
                            trim($_POST['color_hex'] ?? $row['color_hex']),
                            $newFilename,
                            trim($_POST['alt_text'] ?? ''),
                            (int) ($_POST['sort_order'] ?? 0),
                            $id,
                        ]);
                    $notice = 'Colour updated.';
                }
            }
        } elseif ($action === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            $cStmt = db()->prepare('SELECT * FROM bike_colors WHERE id = ? AND bike_id = ?');
            $cStmt->execute([$id, $bikeId]);
            $row = $cStmt->fetch();
            if ($row) {
                delete_uploaded_image($row['filename']);
                db()->prepare('DELETE FROM bike_colors WHERE id = ?')->execute([$id]);
                $notice = 'Colour deleted.';
            }
        }
    }
}

$colors = bike_colors($bikeId);
$token = csrf_token();

admin_header('bikes', 'Bikes');
?>
<p class="admin-lead"><a href="bikes.php" class="link">&larr; Back to Bikes</a></p>
<h2 style="margin:0 0 4px;"><?= e($bike['name']) ?> — colour options</h2>
<p class="admin-lead">The first colour (lowest order) is used as this bike's cover image on the homepage. On the bike's page, clicking a colour swatch swaps the photo to match.</p>

<?php if ($notice): ?><div class="alert ok"><?= e($notice) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

<div class="image-grid">
    <?php foreach ($colors as $c): ?>
        <div class="image-card">
            <div class="image-card-head">
                <h3><span class="swatch-dot" style="background:<?= e($c['color_hex']) ?>"></span> <?= e($c['color_name']) ?></h3>
                <code>#<?= (int) $c['id'] ?></code>
            </div>
            <p class="size-hint">Recommended size: 1200 × 1200px, transparent PNG recommended</p>
            <div class="preview">
                <?php if ($c['filename']): ?>
                    <img src="<?= e(UPLOAD_URL . '/' . rawurlencode($c['filename'])) ?>" alt="<?= e($c['alt_text']) ?>">
                <?php else: ?>
                    <div class="preview-empty">No image yet</div>
                <?php endif; ?>
            </div>
            <form method="post" enctype="multipart/form-data" class="image-form">
                <input type="hidden" name="csrf" value="<?= e($token) ?>">
                <input type="hidden" name="bike_id" value="<?= (int) $bikeId ?>">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                <label class="field">Colour name
                    <input type="text" name="color_name" value="<?= e($c['color_name']) ?>" required>
                </label>
                <label class="field">Swatch colour
                    <input type="color" name="color_hex" value="<?= e($c['color_hex']) ?>">
                </label>
                <label class="field">Replace image
                    <input type="file" name="image" accept="image/*">
                </label>
                <label class="field">Alt text
                    <input type="text" name="alt_text" value="<?= e($c['alt_text']) ?>">
                </label>
                <label class="field">Order
                    <input type="number" name="sort_order" value="<?= (int) $c['sort_order'] ?>">
                </label>
                <div class="image-actions">
                    <button type="submit" class="btn-primary">Save</button>
                    <button type="submit" name="action" value="delete" class="btn-danger"
                            onclick="return confirm('Delete this colour option?');">Delete</button>
                </div>
            </form>
        </div>
    <?php endforeach; ?>

    <div class="image-card">
        <div class="image-card-head"><h3>Add new colour</h3></div>
        <p class="size-hint">Recommended size: 1200 × 1200px, transparent PNG recommended</p>
        <form method="post" enctype="multipart/form-data" class="image-form">
            <input type="hidden" name="csrf" value="<?= e($token) ?>">
            <input type="hidden" name="bike_id" value="<?= (int) $bikeId ?>">
            <input type="hidden" name="action" value="add">
            <label class="field">Colour name
                <input type="text" name="color_name" placeholder="e.g. Matte Black" required>
            </label>
            <label class="field">Swatch colour
                <input type="color" name="color_hex" value="#161616">
            </label>
            <label class="field">Image file
                <input type="file" name="image" accept="image/*" required>
            </label>
            <label class="field">Alt text
                <input type="text" name="alt_text">
            </label>
            <div class="image-actions">
                <button type="submit" class="btn-primary">Add colour</button>
            </div>
        </form>
    </div>
</div>
<?php
admin_footer();
