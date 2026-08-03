<?php
/**
 * If maintenance mode is on, show a lockout page to everyone except a
 * logged-in admin, then stop execution. Must be called after db.php and
 * admin/auth.php have been required.
 */
function maybe_show_maintenance_page(): void
{
    if (!is_maintenance_mode() || current_admin()) {
        return;
    }

    $message = get_setting('maintenance_message', "We're making some improvements. Please check back soon.");
    http_response_code(503);
    header('Retry-After: 3600');
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ZXTec — Under maintenance</title>
<style>
    body { margin:0; min-height:100vh; display:grid; place-items:center; background:#161616; color:#fff;
           font-family:"Inter","Helvetica Neue",Arial,sans-serif; text-align:center; padding:24px; }
    .box { max-width:420px; }
    .mark { display:inline-grid; place-items:center; width:56px; height:56px; border-radius:50%;
            background:#E11B22; font-size:26px; margin-bottom:18px; }
    h1 { font-size:24px; margin:0 0 12px; }
    p { color:#bbb; line-height:1.6; margin:0; }
</style>
</head>
<body>
    <div class="box">
        <div class="mark">&#9883;</div>
        <h1>We'll be right back</h1>
        <p><?= e($message) ?></p>
    </div>
</body>
</html>
    <?php
    exit;
}
