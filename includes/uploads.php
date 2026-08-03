<?php
require_once __DIR__ . '/config.php';

/**
 * Validate and store an uploaded image ($_FILES[...] entry).
 * Returns ['ok' => bool, 'filename' => ?string, 'error' => ?string].
 */
function save_uploaded_image(array $file, string $prefix): array
{
    if (!empty($file['error']) && $file['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'filename' => null, 'error' => 'Upload failed (error code ' . $file['error'] . ').'];
    }
    if ($file['size'] > MAX_UPLOAD_BYTES) {
        return ['ok' => false, 'filename' => null, 'error' => 'File is too large (max ' . (MAX_UPLOAD_BYTES / 1048576) . ' MB).'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, ALLOWED_TYPES, true)) {
        return ['ok' => false, 'filename' => null, 'error' => 'Unsupported file type. Use JPG, PNG, WEBP, GIF or SVG.'];
    }

    $extMap = [
        'image/jpeg' => 'jpg', 'image/png' => 'png',
        'image/webp' => 'webp', 'image/gif' => 'gif',
        'image/svg+xml' => 'svg',
    ];
    $ext = $extMap[$mime];

    if (!is_dir(UPLOAD_DIR)) {
        @mkdir(UPLOAD_DIR, 0775, true);
    }

    $safe = preg_replace('/[^a-z0-9_]/i', '', $prefix) . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

    if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . '/' . $safe)) {
        return ['ok' => false, 'filename' => null, 'error' => 'Could not save the uploaded file.'];
    }

    return ['ok' => true, 'filename' => $safe, 'error' => null];
}

/**
 * Delete a previously uploaded file (if it exists) by filename.
 */
function delete_uploaded_image(?string $filename): void
{
    if ($filename) {
        @unlink(UPLOAD_DIR . '/' . $filename);
    }
}
