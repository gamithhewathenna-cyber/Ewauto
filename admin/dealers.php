<?php
require_once __DIR__ . '/layout.php';

$notice = '';
$error  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!check_csrf()) {
        $error = 'Session expired. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'toggle_section') {
            $show = isset($_POST['show_dealers']);
            set_setting('show_dealers', $show ? '1' : '0');
            $notice = $show
                ? 'Dealership section is now shown on the website.'
                : 'Dealership section is now hidden from the website.';
        } elseif ($action === 'add') {
            $name = trim($_POST['name'] ?? '');
            if ($name === '') {
                $error = 'Please enter a dealer name.';
            } else {
                $maxOrder = (int) db()->query('SELECT COALESCE(MAX(sort_order), 0) FROM dealers')->fetchColumn();
                db()->prepare('INSERT INTO dealers (name, address, phone, sort_order, active) VALUES (?, ?, ?, ?, 1)')
                    ->execute([
                        $name,
                        trim($_POST['address'] ?? ''),
                        trim($_POST['phone'] ?? ''),
                        $maxOrder + 10,
                    ]);
                $notice = 'Dealer added.';
            }
        } elseif ($action === 'update') {
            $id = (int) ($_POST['id'] ?? 0);
            db()->prepare('UPDATE dealers SET name=?, address=?, phone=?, sort_order=?, active=? WHERE id=?')
                ->execute([
                    trim($_POST['name'] ?? ''),
                    trim($_POST['address'] ?? ''),
                    trim($_POST['phone'] ?? ''),
                    (int) ($_POST['sort_order'] ?? 0),
                    isset($_POST['active']) ? 1 : 0,
                    $id,
                ]);
            $notice = 'Dealer updated.';
        } elseif ($action === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            db()->prepare('DELETE FROM dealers WHERE id = ?')->execute([$id]);
            $notice = 'Dealer deleted.';
        }
    }
}

$dealers = all_dealers();
$showDealers = dealers_section_enabled();
$token = csrf_token();

admin_header('dealers', 'Dealerships');
?>
<p class="admin-lead">Manage the dealerships shown on the Home and Contact Us pages (name, address, and phone for each dealer).</p>

<?php if ($notice): ?><div class="alert ok"><?= e($notice) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

<div class="card">
    <h3>Show on website</h3>
    <form method="post" class="section-toggle">
        <input type="hidden" name="csrf" value="<?= e($token) ?>">
        <input type="hidden" name="action" value="toggle_section">
        <label class="toggle-row">
            <input type="checkbox" name="show_dealers" value="1" <?= $showDealers ? 'checked' : '' ?>>
            <span>Show the Dealership section on the Home and Contact Us pages</span>
        </label>
        <span class="status-pill <?= $showDealers ? 'is-on' : 'is-off' ?>"><?= $showDealers ? 'Visible on website' : 'Hidden from website' ?></span>
        <button type="submit" class="btn-primary">Save</button>
    </form>
    <p class="size-hint section-toggle-hint">Hiding it keeps all your dealers saved here - tick it again any time to bring the section back.</p>
</div>

<div class="image-grid">
    <?php foreach ($dealers as $d): ?>
        <div class="image-card">
            <div class="image-card-head">
                <h3><?= e($d['name']) ?></h3>
                <code><?= $d['active'] ? 'active' : 'hidden' ?></code>
            </div>
            <form method="post" class="image-form">
                <input type="hidden" name="csrf" value="<?= e($token) ?>">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= (int) $d['id'] ?>">
                <label class="field">Dealer name
                    <input type="text" name="name" value="<?= e($d['name']) ?>" required>
                </label>
                <label class="field">Location address
                    <input type="text" name="address" value="<?= e($d['address']) ?>">
                </label>
                <label class="field">Contact number
                    <input type="text" name="phone" value="<?= e($d['phone']) ?>">
                </label>
                <label class="field">Order
                    <input type="number" name="sort_order" value="<?= (int) $d['sort_order'] ?>">
                </label>
                <label class="toggle-row">
                    <input type="checkbox" name="active" value="1" <?= $d['active'] ? 'checked' : '' ?>>
                    <span>Active</span>
                </label>
                <div class="image-actions">
                    <button type="submit" class="btn-primary">Save</button>
                    <button type="submit" name="action" value="delete" class="btn-danger"
                            onclick="return confirm('Delete this dealer?');">Delete</button>
                </div>
            </form>
        </div>
    <?php endforeach; ?>

    <div class="image-card">
        <div class="image-card-head"><h3>Add new dealer</h3></div>
        <form method="post" class="image-form">
            <input type="hidden" name="csrf" value="<?= e($token) ?>">
            <input type="hidden" name="action" value="add">
            <label class="field">Dealer name
                <input type="text" name="name" required>
            </label>
            <label class="field">Location address
                <input type="text" name="address">
            </label>
            <label class="field">Contact number
                <input type="text" name="phone">
            </label>
            <div class="image-actions">
                <button type="submit" class="btn-primary">Add dealer</button>
            </div>
        </form>
    </div>
</div>
<?php
admin_footer();
