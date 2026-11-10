<?php
require_once __DIR__ . '/auth.php';
require_login();
header('Location: ' . home_page_for_role());
exit;
