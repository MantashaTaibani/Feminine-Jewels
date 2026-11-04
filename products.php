<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin('../');

$page_title = 'Products';
$admin_active = 'products';

$categories = db()->query('SELECT name FROM categories ORDER BY name')->fetchAll(PDO::FETCH_COLUMN);
$q   = trim((string)($_GET['q'] ?? ''));
$cat = in_array($_GET['cat'] ?? '', $categories, true) ? $_GET['cat'] : '';

$sql = 'SELECT * FROM products WHERE 1=1';
$params = [];
if ($cat !== '') { $sql .= ' AND category = ?'; $params[] = $cat; }
if ($q !== '')   { $sql .= ' AND (name LIKE ? OR category LIKE ?)'; $params[] = "%$q%"; $params[] = "%$q%"; }
$sql .= ' ORDER BY created_at DESC';
$st = db()->prepare($sql);
$st->execute($params);
$products = $st->fetchAll();

require __DIR__ . '/includes_header.php';
?>

<form class="toolbar" method="GET" action="products.php">
  <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search products…" aria-label="Search products">
  <select name="cat" onchange="this.form.submit()" aria-label="Filter by category">
    <option value="">All categories</option>
    <?php foreach ($categories as $c): ?>
      <option value="<?= e($c) ?>" <?= $cat === $c ? 'selected' : '' ?>><?= e($c) ?></option>
    <?php endforeach; ?>
  </select>
  <button class="btn btn-outline btn-sm" type="submit">Filter</button>
  <span class="grow"></span>
  <a class="btn" href="add_product.php">+ Add product</a>
</form>

<div class="panel">
  <div class="table-wrap">
    <table class="table">
      <thead><tr><th>ID</th><th>Image</th><th>Product name</th><th>Category</th><th>Price</th><th>Stock</th><th>Actions</th></tr></thead>
      <tbody>
      <?php if ($products): foreach ($products as $p): ?>
        <tr>
          <td>#<?= (int)$p['id'] ?></td>
          <td><img class="thumb" src="<?= e(product_image($p['image'], '../')) ?>" alt=""></td>
          <td><b><?= e($p['name']) ?></b></td>
          <td><?= e($p['category']) ?></td>
          <td><?= money($p['price']) ?></td>
          <td>
            <?php if ((int)$p['stock'] === 0): ?><span class="status st-cancelled">Out of stock</span>
            <?php elseif ((int)$p['stock'] <= 5): ?><span class="status st-pending"><?= (int)$p['stock'] ?> left</span>
            <?php else: ?><?= (int)$p['stock'] ?><?php endif; ?>
          </td>
          <td>
            <div class="row-actions">
              <a class="btn btn-outline btn-sm" href="edit_product.php?id=<?= (int)$p['id'] ?>">Edit</a>
              <form method="post" action="delete_product.php" onsubmit="return confirm('Delete this product? This can\'t be undone.');" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                <button class="btn btn-danger btn-sm" type="submit">Delete</button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; else: ?>
        <tr><td colspan="7" class="muted" style="text-align:center;padding:32px">No products match.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/includes_footer.php'; ?>
