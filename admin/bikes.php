<?php
require_once __DIR__ . '/layout.php';

$notice = '';
$error  = '';
$specColumns = bike_spec_columns();

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
        foreach ($specColumns as $col) {
            $specs[$col] = trim($_POST[$col] ?? '');
        }

        if ($action === 'add') {
            $name = trim($_POST['name'] ?? '');
            if ($name === '') {
                $error = 'Bike name is required.';
            } else {
                $slug = unique_bike_slug(sanitize_slug($name));

                $maxOrder = (int) db()->query('SELECT COALESCE(MAX(sort_order), 0) FROM bikes')->fetchColumn();
                $cols = array_merge(['slug', 'name', 'tagline', 'description'], $specColumns, ['sort_order']);
                $placeholders = implode(', ', array_fill(0, count($cols), '?'));
                $values = array_merge(
                    [$slug, $name, trim($_POST['tagline'] ?? ''), trim($_POST['description'] ?? '')],
                    array_values($specs),
                    [$maxOrder + 10]
                );
                db()->prepare('INSERT INTO bikes (' . implode(', ', $cols) . ', active) VALUES (' . $placeholders . ', 1)')
                    ->execute($values);
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
                $setCols = array_merge(['name', 'tagline', 'description'], $specColumns, ['sort_order', 'active']);
                $setSql = implode(', ', array_map(static fn($c) => "$c=?", $setCols));
                $values = array_merge(
                    [$name, trim($_POST['tagline'] ?? ''), trim($_POST['description'] ?? '')],
                    array_values($specs),
                    [(int) ($_POST['sort_order'] ?? 0), isset($_POST['active']) ? 1 : 0, $id]
                );
                db()->prepare('UPDATE bikes SET ' . $setSql . ' WHERE id=?')->execute($values);
                $notice = 'Bike updated.';
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
$specGroups = bike_spec_groups();
$token = csrf_token();

admin_header('bikes', 'Bikes');
?>
<p class="admin-lead">Manage your bike models. They show on the homepage — visitors flip between bikes with the left/right arrows, and between colours by clicking a swatch. After adding a bike, click "Manage colours" to upload images for each colour option.</p>

<?php if ($notice): ?><div class="alert ok"><?= e($notice) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

<?php foreach ($bikes as $b): ?>
    <div class="card">
        <div class="image-card-head" style="margin-bottom:14px;">
            <h3><?= e($b['name']) ?></h3>
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
            <label class="field">Tagline
                <input type="text" name="tagline" value="<?= e($b['tagline']) ?>">
            </label>
            <label class="field">Order
                <input type="number" name="sort_order" value="<?= (int) $b['sort_order'] ?>">
            </label>
            <label class="field" style="grid-column:1/-1;">Description
                <textarea name="description" rows="3"><?= e($b['description']) ?></textarea>
            </label>

            <?php foreach ($specGroups as $groupTitle => $fields): ?>
                <h4 class="spec-group-title" style="grid-column:1/-1;"><?= e($groupTitle) ?></h4>
                <?php foreach ($fields as $col => $label): ?>
                    <label class="field"><?= e($label) ?>
                        <input type="text" name="<?= e($col) ?>" value="<?= e($b[$col] ?? '') ?>">
                    </label>
                <?php endforeach; ?>
            <?php endforeach; ?>

            <label class="toggle-row" style="grid-column:1/-1;">
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
        <label class="field">Tagline
            <input type="text" name="tagline">
        </label>
        <label class="field" style="grid-column:1/-1;">Description
            <textarea name="description" rows="3"></textarea>
        </label>

        <?php foreach ($specGroups as $groupTitle => $fields): ?>
            <h4 class="spec-group-title" style="grid-column:1/-1;"><?= e($groupTitle) ?></h4>
            <?php foreach ($fields as $col => $label): ?>
                <label class="field"><?= e($label) ?>
                    <input type="text" name="<?= e($col) ?>">
                </label>
            <?php endforeach; ?>
        <?php endforeach; ?>

        <div class="image-actions" style="grid-column:1/-1;">
            <button type="submit" class="btn-primary">Add bike</button>
        </div>
    </form>
</div>
<?php
admin_footer();
