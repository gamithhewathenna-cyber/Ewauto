<?php
require_once __DIR__ . '/layout.php';

$notice = '';
$error  = '';

function sanitize_slug(string $s): string
{
    $s = strtolower(trim($s));
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    return trim($s, '-');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!check_csrf()) {
        $error = 'Session expired. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        $specs = [];
        for ($n = 1; $n <= 5; $n++) {
            $specs["spec{$n}_label"] = trim($_POST["spec{$n}_label"] ?? '');
            $specs["spec{$n}_value"] = trim($_POST["spec{$n}_value"] ?? '');
        }

        if ($action === 'add') {
            $name = trim($_POST['name'] ?? '');
            if ($name === '') {
                $error = 'Bike name is required.';
            } else {
                $slugInput = sanitize_slug($_POST['slug'] ?? '');
                $slug = $slugInput !== '' ? $slugInput : sanitize_slug($name);
                $slug = unique_bike_slug($slug);

                $maxOrder = (int) db()->query('SELECT COALESCE(MAX(sort_order), 0) FROM bikes')->fetchColumn();
                db()->prepare('INSERT INTO bikes
                    (slug, name, tagline, description, spec1_label, spec1_value, spec2_label, spec2_value,
                     spec3_label, spec3_value, spec4_label, spec4_value, spec5_label, spec5_value, sort_order, active)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)')
                    ->execute([
                        $slug, $name,
                        trim($_POST['tagline'] ?? ''),
                        trim($_POST['description'] ?? ''),
                        $specs['spec1_label'], $specs['spec1_value'],
                        $specs['spec2_label'], $specs['spec2_value'],
                        $specs['spec3_label'], $specs['spec3_value'],
                        $specs['spec4_label'], $specs['spec4_value'],
                        $specs['spec5_label'], $specs['spec5_value'],
                        $maxOrder + 10,
                    ]);
                $notice = 'Bike added. Now add colour images for it below.';
            }
        } elseif ($action === 'update') {
            $id = (int) ($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $stmt = db()->prepare('SELECT * FROM bikes WHERE id = ?');
            $stmt->execute([$id]);
            $row = $stmt->fetch();

            if (!$row) {
                $error = 'Bike not found.';
            } elseif ($name === '') {
                $error = 'Bike name is required.';
            } else {
                $slugInput = sanitize_slug($_POST['slug'] ?? '');
                if ($slugInput === '') {
                    $slugInput = sanitize_slug($name);
                }
                $dupe = db()->prepare('SELECT id FROM bikes WHERE slug = ? AND id != ?');
                $dupe->execute([$slugInput, $id]);
                if ($dupe->fetch()) {
                    $error = 'That URL slug is already used by another bike.';
                } else {
                    db()->prepare('UPDATE bikes SET slug=?, name=?, tagline=?, description=?,
                        spec1_label=?, spec1_value=?, spec2_label=?, spec2_value=?,
                        spec3_label=?, spec3_value=?, spec4_label=?, spec4_value=?,
                        spec5_label=?, spec5_value=?, sort_order=?, active=? WHERE id=?')
                        ->execute([
                            $slugInput, $name,
                            trim($_POST['tagline'] ?? ''),
                            trim($_POST['description'] ?? ''),
                            $specs['spec1_label'], $specs['spec1_value'],
                            $specs['spec2_label'], $specs['spec2_value'],
                            $specs['spec3_label'], $specs['spec3_value'],
                            $specs['spec4_label'], $specs['spec4_value'],
                            $specs['spec5_label'], $specs['spec5_value'],
                            (int) ($_POST['sort_order'] ?? 0),
                            isset($_POST['active']) ? 1 : 0,
                            $id,
                        ]);
                    $notice = 'Bike updated.';
                }
            }
        } elseif ($action === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            $stmt = db()->prepare('SELECT filename FROM bike_colors WHERE bike_id = ?');
            $stmt->execute([$id]);
            foreach ($stmt->fetchAll() as $c) {
                if (!empty($c['filename'])) {
                    @unlink(UPLOAD_DIR . '/' . $c['filename']);
                }
            }
            db()->prepare('DELETE FROM bikes WHERE id = ?')->execute([$id]);
            $notice = 'Bike and its colour images deleted.';
        }
    }
}

$bikes = all_bikes();
$colorCounts = [];
foreach (db()->query('SELECT bike_id, COUNT(*) AS n FROM bike_colors GROUP BY bike_id') as $row) {
    $colorCounts[$row['bike_id']] = (int) $row['n'];
}
$token = csrf_token();

admin_header('bikes', 'Bikes');
?>
<p class="admin-lead">Manage your bike models. Each bike gets its own page at <code>bike.php?slug=...</code> and shows on the homepage "Our Bikes" section. After adding a bike, click "Manage colours" to upload images for each colour option — visitors can click a colour to change the bike photo.</p>

<?php if ($notice): ?><div class="alert ok"><?= e($notice) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

<?php foreach ($bikes as $b): ?>
    <div class="card">
        <div class="image-card-head" style="margin-bottom:14px;">
            <h3><?= e($b['name']) ?> <code>/bike.php?slug=<?= e($b['slug']) ?></code></h3>
            <code><?= $b['active'] ? 'active' : 'hidden' ?></code>
        </div>
        <p class="admin-lead" style="margin:0 0 14px;">
            <a href="bike_colors.php?bike_id=<?= (int) $b['id'] ?>" class="link"><?= $colorCounts[$b['id']] ?? 0 ?> colour(s) &rarr; manage</a>
        </p>
        <form method="post" class="content-fields">
            <input type="hidden" name="csrf" value="<?= e($token) ?>">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?= (int) $b['id'] ?>">

            <label class="field">Name
                <input type="text" name="name" value="<?= e($b['name']) ?>" required>
            </label>
            <label class="field">URL slug
                <input type="text" name="slug" value="<?= e($b['slug']) ?>">
            </label>
            <label class="field">Tagline
                <input type="text" name="tagline" value="<?= e($b['tagline']) ?>">
            </label>
            <label class="field">Order
                <input type="number" name="sort_order" value="<?= (int) $b['sort_order'] ?>">
            </label>
            <label class="field" style="grid-column:1/-1;">Description
                <textarea name="description" rows="3"><?= e($b['description']) ?></textarea>
            </label>

            <?php for ($n = 1; $n <= 5; $n++): ?>
                <label class="field">Spec <?= $n ?> label
                    <input type="text" name="spec<?= $n ?>_label" value="<?= e($b['spec' . $n . '_label']) ?>">
                </label>
                <label class="field">Spec <?= $n ?> value
                    <input type="text" name="spec<?= $n ?>_value" value="<?= e($b['spec' . $n . '_value']) ?>">
                </label>
            <?php endfor; ?>

            <label class="toggle-row">
                <input type="checkbox" name="active" value="1" <?= $b['active'] ? 'checked' : '' ?>>
                <span>Active (visible on the site)</span>
            </label>

            <div class="image-actions" style="grid-column:1/-1;">
                <button type="submit" class="btn-primary">Save</button>
                <button type="submit" name="action" value="delete" class="btn-danger"
                        onclick="return confirm('Delete this bike and all its colour images? This cannot be undone.');">Delete</button>
            </div>
        </form>
    </div>
<?php endforeach; ?>

<div class="card">
    <h3>Add new bike</h3>
    <form method="post" class="content-fields">
        <input type="hidden" name="csrf" value="<?= e($token) ?>">
        <input type="hidden" name="action" value="add">

        <label class="field">Name
            <input type="text" name="name" placeholder="e.g. EV Pro TWO" required>
        </label>
        <label class="field">URL slug <span style="font-weight:400;color:#999;">(optional, auto-generated from name)</span>
            <input type="text" name="slug" placeholder="ev-pro-two">
        </label>
        <label class="field">Tagline
            <input type="text" name="tagline">
        </label>
        <label class="field" style="grid-column:1/-1;">Description
            <textarea name="description" rows="3"></textarea>
        </label>

        <?php
        $defaultLabels = ['Max Speed', 'Range', 'Weight allow', 'Motor', 'Battery'];
        for ($n = 1; $n <= 5; $n++): ?>
            <label class="field">Spec <?= $n ?> label
                <input type="text" name="spec<?= $n ?>_label" value="<?= e($defaultLabels[$n - 1]) ?>">
            </label>
            <label class="field">Spec <?= $n ?> value
                <input type="text" name="spec<?= $n ?>_value">
            </label>
        <?php endfor; ?>

        <div class="image-actions" style="grid-column:1/-1;">
            <button type="submit" class="btn-primary">Add bike</button>
        </div>
    </form>
</div>
<?php
admin_footer();
