<?php
require_once __DIR__ . '/auth.php';
require_login();

$notice = '';
$error  = '';

/**
 * Handle an upload / update / delete for a single image slot.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!check_csrf()) {
        $error = 'Session expired. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        $slot   = $_POST['slot'] ?? '';

        // Verify slot exists
        $stmt = db()->prepare('SELECT * FROM images WHERE slot = ? LIMIT 1');
        $stmt->execute([$slot]);
        $row = $stmt->fetch();

        if (!$row) {
            $error = 'Unknown image slot.';
        } elseif ($action === 'delete') {
            if ($row['filename']) {
                @unlink(UPLOAD_DIR . '/' . $row['filename']);
            }
            db()->prepare('UPDATE images SET filename = NULL WHERE slot = ?')->execute([$slot]);
            $notice = 'Image removed for "' . $row['label'] . '".';
        } elseif ($action === 'update') {
            $alt = trim($_POST['alt_text'] ?? '');
            $newFilename = $row['filename'];

            // File upload (optional — admin may just update alt text)
            if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $f = $_FILES['image'];
                if ($f['error'] !== UPLOAD_ERR_OK) {
                    $error = 'Upload failed (error code ' . $f['error'] . ').';
                } elseif ($f['size'] > MAX_UPLOAD_BYTES) {
                    $error = 'File is too large (max ' . (MAX_UPLOAD_BYTES / 1048576) . ' MB).';
                } else {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime  = finfo_file($finfo, $f['tmp_name']);
                    finfo_close($finfo);

                    if (!in_array($mime, ALLOWED_TYPES, true)) {
                        $error = 'Unsupported file type. Use JPG, PNG, WEBP, GIF or SVG.';
                    } else {
                        if (!is_dir(UPLOAD_DIR)) {
                            @mkdir(UPLOAD_DIR, 0775, true);
                        }
                        $extMap = [
                            'image/jpeg' => 'jpg', 'image/png' => 'png',
                            'image/webp' => 'webp', 'image/gif' => 'gif',
                            'image/svg+xml' => 'svg',
                        ];
                        $ext = $extMap[$mime];
                        $safe = $slot . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                        if (move_uploaded_file($f['tmp_name'], UPLOAD_DIR . '/' . $safe)) {
                            // remove the old file
                            if ($row['filename']) {
                                @unlink(UPLOAD_DIR . '/' . $row['filename']);
                            }
                            $newFilename = $safe;
                        } else {
                            $error = 'Could not save the uploaded file.';
                        }
                    }
                }
            }

            if (!$error) {
                db()->prepare('UPDATE images SET filename = ?, alt_text = ? WHERE slot = ?')
                    ->execute([$newFilename, $alt, $slot]);
                $notice = 'Saved "' . $row['label'] . '".';
            }
        }
    }
}

$images = db()->query('SELECT * FROM images ORDER BY id')->fetchAll();
$token  = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ZXTec Admin — Images</title>
<link rel="stylesheet" href="admin.css">
</head>
<body>
<header class="admin-top">
    <div class="admin-brand"><span class="brand-mark">&#9883;</span> ZXTec Admin</div>
    <div class="admin-top-right">
        <a href="../index.php" target="_blank" class="link">View site &nearr;</a>
        <span class="who">Hi, <?= e(current_admin()['username']) ?></span>
        <a href="logout.php" class="btn-ghost">Log out</a>
    </div>
</header>

<main class="admin-main">
    <h1>Site images</h1>
    <p class="admin-lead">Upload or replace any image used on the website. Changes appear on the live site immediately.</p>

    <?php if ($notice): ?><div class="alert ok"><?= e($notice) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

    <div class="image-grid">
        <?php foreach ($images as $img): ?>
            <div class="image-card">
                <div class="image-card-head">
                    <h3><?= e($img['label']) ?></h3>
                    <code><?= e($img['slot']) ?></code>
                </div>

                <div class="preview">
                    <?php if ($img['filename']): ?>
                        <img src="<?= e(UPLOAD_URL . '/' . rawurlencode($img['filename'])) ?>" alt="<?= e($img['alt_text']) ?>">
                    <?php else: ?>
                        <div class="preview-empty">No image yet</div>
                    <?php endif; ?>
                </div>

                <form method="post" enctype="multipart/form-data" class="image-form">
                    <input type="hidden" name="csrf" value="<?= e($token) ?>">
                    <input type="hidden" name="slot" value="<?= e($img['slot']) ?>">
                    <input type="hidden" name="action" value="update">

                    <label class="field">Image file
                        <input type="file" name="image" accept="image/*">
                    </label>
                    <label class="field">Alt text
                        <input type="text" name="alt_text" value="<?= e($img['alt_text']) ?>" placeholder="Describe the image">
                    </label>

                    <div class="image-actions">
                        <button type="submit" class="btn-primary">Save</button>
                        <?php if ($img['filename']): ?>
                            <button type="submit" name="action" value="delete" class="btn-danger"
                                    onclick="return confirm('Remove this image?');">Remove</button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</main>
</body>
</html>
