<?php
require_once __DIR__ . '/includes/auth.php';

$page_title = 'Jewellery — ' . SITE_NAME;
$active_nav = 'products';

$categories = db()->query('SELECT name FROM categories ORDER BY name')->fetchAll(PDO::FETCH_COLUMN);

$q    = trim((string)($_GET['q'] ?? ''));
$cat  = in_array($_GET['cat'] ?? '', $categories, true) ? $_GET['cat'] : 'All';
$sort = in_array($_GET['sort'] ?? '', ['popular', 'newest', 'low', 'high'], true) ? $_GET['sort'] : 'popular';
$max  = isset($_GET['max']) && is_numeric($_GET['max']) ? (float)$_GET['max'] : null;

$priceCeil = (float)db()->query('SELECT COALESCE(MAX(price),4000) FROM products')->fetchColumn();
$priceCeil = max(500, ceil($priceCeil / 500) * 500);
if ($max === null || $max > $priceCeil) {
    $max = $priceCeil;
}

$sql = 'SELECT * FROM products WHERE price <= :max';
$params = ['max' => $max];
if ($cat !== 'All') {
    $sql .= ' AND category = :cat';
    $params['cat'] = $cat;
}
if ($q !== '') {
    $sql .= ' AND (name LIKE :q OR category LIKE :q)';
    $params['q'] = '%' . $q . '%';
}
$sql .= match ($sort) {
    'low'    => ' ORDER BY price ASC',
    'high'   => ' ORDER BY price DESC',
    'newest' => ' ORDER BY created_at DESC',
    default  => ' ORDER BY created_at DESC', // "popular" — no popularity column, fall back to newest first
};

$st = db()->prepare($sql);
$st->execute($params);
$products = $st->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<div class="page-band"><div class="container"><h1>Jewellery</h1><p>Search, filter and sort our collection.</p></div></div>
<div class="container page-body">
  <div class="shop-layout">
    <aside class="filters panel" aria-label="Filters">
      <form method="GET" action="products.php">
        <div class="filter-group">
          <h4>Search</h4>
          <input type="search" name="q" placeholder="Name or category" aria-label="Search products" value="<?= e($q) ?>">
        </div>
        <div class="filter-group">
          <h4>Category</h4>
          <div class="chips">
            <button type="submit" name="cat" value="All" class="chip <?= $cat === 'All' ? 'on' : '' ?>">All</button>
            <?php foreach ($categories as $c): ?>
              <button type="submit" name="cat" value="<?= e($c) ?>" class="chip <?= $cat === $c ? 'on' : '' ?>"><?= e($c) ?></button>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="filter-group">
          <h4>Price</h4>
          <input type="range" name="max" min="0" max="<?= (int)$priceCeil ?>" step="100" value="<?= (int)$max ?>" onchange="this.form.submit()" aria-label="Maximum price">
          <div class="range-row"><span>₹0</span><span>Up to <?= money($max) ?></span></div>
        </div>
        <div class="filter-group">
          <h4>Sort by</h4>
          <select name="sort" onchange="this.form.submit()" aria-label="Sort products">
            <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Popular</option>
            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
            <option value="low" <?= $sort === 'low' ? 'selected' : '' ?>>Price: Low to High</option>
            <option value="high" <?= $sort === 'high' ? 'selected' : '' ?>>Price: High to Low</option>
          </select>
        </div>
        <a class="btn btn-outline btn-block btn-sm" href="products.php">Reset filters</a>
      </form>
    </aside>
    <div>
      <div class="result-bar"><span><?= count($products) ?> <?= count($products) === 1 ? 'piece' : 'pieces' ?> found</span></div>
      <div class="p-grid">
        <?php if ($products): foreach ($products as $p) echo product_card_html($p); else: ?>
          <div class="empty" style="grid-column:1/-1">
            <h3>No matches</h3>
            <p>Try a different word, category or price limit.</p>
            <a class="btn btn-outline" href="products.php">Reset filters</a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
