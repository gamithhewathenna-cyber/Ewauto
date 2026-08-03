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
            $name = trim($_POST['name'] ?? '');
            if ($name === '') {
                $error = 'Product name is required.';
            } else {
                $filename = null;
                if (!empty($_FILES['image']['name'])) {
                    $result = save_uploaded_image($_FILES['image'], 'product');
                    if (!$result['ok']) {
                        $error = $result['error'];
                    } else {
                        $filename = $result['filename'];
                    }
                }
                if (!$error) {
                    $maxOrder = (int) db()->query('SELECT COALESCE(MAX(sort_order), 0) FROM products')->fetchColumn();
                    db()->prepare('INSERT INTO products (name, description, filename, alt_text, sort_order, active)
                        VALUES (?, ?, ?, ?, ?, 1)')
                        ->execute([
                            $name,
                            trim($_POST['description'] ?? ''),
                            $filename,
                            trim($_POST['alt_text'] ?? ''),
                            $maxOrder + 10,
                        ]);
                    $notice = 'Product added.';
                }
            }
        } elseif ($action === 'update') {
            $id = (int) ($_POST['id'] ?? 0);
            $stmt = db()->prepare('SELECT * FROM products WHERE id = ?');
            $stmt->execute([$id]);
            $row = $stmt->fetch();

            if (!$row) {
                $error = 'Product not found.';
            } else {
                $newFilename = $row['filename'];
                if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $result = save_uploaded_image($_FILES['image'], 'product');
                    if (!$result['ok']) {
                        $error = $result['error'];
                    } else {
                        delete_uploaded_image($row['filename']);
                        $newFilename = $result['filename'];
                    }
                }
                if (!$error) {
                    db()->prepare('UPDATE products SET name=?, description=?, filename=?, alt_text=?, sort_order=?, active=? WHERE id=?')
                        ->execute([
                            trim($_POST['name'] ?? $row['name']),
                            trim($_POST['description'] ?? ''),
                            $newFilename,
                            trim($_POST['alt_text'] ?? ''),
                            (int) ($_POST['sort_order'] ?? 0),
                            isset($_POST['active']) ? 1 : 0,
                            $id,
                        ]);
                    $notice = 'Product updated.';
                }
            }
        } elseif ($action === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            $stmt = db()->prepare('SELECT * FROM products WHERE id = ?');
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if ($row) {
                delete_uploaded_image($row['filename']);
                db()->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
                $notice = 'Product deleted.';
            }
        }
    }
}

$products = all_products();
$token = csrf_token();

admin_header('products', 'Product Images');
?>
<p class="admin-lead">Manage the products shown in the homepage lineup row. If no products are added, the homepage falls back to the single lineup image set in Page Content.</p>

<?php if ($notice): ?><div class="alert ok"><?= e($notice) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

<div class="image-grid">
    <?php foreach ($products as $p): ?>
        <div class="image-card">
            <div class="image-card-head">
                <h3><?= e($p['name']) ?></h3>
                <code><?= $p['active'] ? 'active' : 'hidden' ?></code>
            </div>
            <div class="preview">
                <?php if ($p['filename']): ?>
                    <img src="<?= e(UPLOAD_URL . '/' . rawurlencode($p['filename'])) ?>" alt="<?= e($p['alt_text']) ?>">
                <?php else: ?>
                    <div class="preview-empty">No image yet</div>
                <?php endif; ?>
            </div>
            <form method="post" enctype="multipart/form-data" class="image-form">
                <input type="hidden" name="csrf" value="<?= e($token) ?>">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                <label class="field">Name
                    <input type="text" name="name" value="<?= e($p['name']) ?>" required>
                </label>
                <label class="field">Description
                    <textarea name="description" rows="2"><?= e($p['description']) ?></textarea>
                </label>
                <label class="field">Replace image
                    <input type="file" name="image" accept="image/*">
                </label>
                <label class="field">Alt text
                    <input type="text" name="alt_text" value="<?= e($p['alt_text']) ?>">
                </label>
                <label class="field">Order
                    <input type="number" name="sort_order" value="<?= (int) $p['sort_order'] ?>">
                </label>
                <label class="toggle-row">
                    <input type="checkbox" name="active" value="1" <?= $p['active'] ? 'checked' : '' ?>>
                    <span>Active</span>
                </label>
                <div class="image-actions">
                    <button type="submit" class="btn-primary">Save</button>
                    <button type="submit" name="action" value="delete" class="btn-danger"
                            onclick="return confirm('Delete this product?');">Delete</button>
                </div>
            </form>
        </div>
    <?php endforeach; ?>

    <div class="image-card">
        <div class="image-card-head"><h3>Add new product</h3></div>
        <form method="post" enctype="multipart/form-data" class="image-form">
            <input type="hidden" name="csrf" value="<?= e($token) ?>">
            <input type="hidden" name="action" value="add">
            <label class="field">Name
                <input type="text" name="name" required>
            </label>
            <label class="field">Description
                <textarea name="description" rows="2"></textarea>
            </label>
            <label class="field">Image file
                <input type="file" name="image" accept="image/*">
            </label>
            <label class="field">Alt text
                <input type="text" name="alt_text">
            </label>
            <div class="image-actions">
                <button type="submit" class="btn-primary">Add product</button>
            </div>
        </form>
    </div>
</div>
<?php
admin_footer();
