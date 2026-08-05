<?php
// AJAX endpoint for the "Talk to Our Team" popup form (index/about/contact
// pages). Mirrors the full Contact Us page's own POST handling via the
// shared handle_contact_submission() so both forms behave identically.

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/admin/auth.php';
require_once __DIR__ . '/includes/contact_form.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Method not allowed.']);
    exit;
}

try {
    $content = all_content();
} catch (Throwable $ex) {
    $content = [];
}

echo json_encode(handle_contact_submission($content));
