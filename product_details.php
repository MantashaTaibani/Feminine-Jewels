<?php
require_once __DIR__ . '/includes/auth.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$product = null;
if ($id) {
    $st = db()->prepare('SELECT * FROM products WHERE id = ?');
    $st->execute([$id]);
    $product = $st->fetch();
}

$page_title = ($product ? $product['name'] . ' — ' : '') . SITE_NAME;
$active_nav = 'products';
require __DIR__ . '/includes/header.php';

if (!$product): ?>
  <div class="container page-body">
    <div class="empty">
      <h3>We couldn't find that piece</h3>
      <p>It may have been removed.</p>
      <a class="btn" href="products.php">Back to jewellery</a>
    </div>
  </div>
<?php
  require __DIR__ . '/includes/footer.php';
  exit;
endif;

$stock = (int)$product['stock'];
if ($stock === 0) {
    $stockClass = 'stock-out'; $stockText = 'Sold out';
} elseif ($stock <= 5) {
    $stockClass = 'stock-low'; $stockText = "Only $stock left";
} else {
    $stockClass = 'stock-ok'; $stockText = "In stock ($stock available)";
}

$relSt = db()->prepare('SELECT * FROM products WHERE id != ? AND category = ? ORDER BY created_at DESC LIMIT 4');
$relSt->execute([$product['id'], $product['category']]);
$related = $relSt->fetchAll();
if (count($related) < 4) {
    $need = 4 - count($related);
    $fillSt = db()->prepare('SELECT * FROM products WHERE id != :id AND category != :cat ORDER BY created_at DESC LIMIT ' . (int)$need);
    $fillSt->execute(['id' => $product['id'], 'cat' => $product['category']]);
    $related = array_merge($related, $fillSt->fetchAll());
}
$back = 'product_details.php?id=' . (int)$product['id'];
?>

<div class="container page-body">
  <div class="crumbs">
    <a href="index.php">Home</a> / <a href="products.php">Jewellery</a> /
    <a href="products.php?cat=<?= urlencode($product['category']) ?>"><?= e($product['category']) ?></a> /
    <?= e($product['name']) ?>
  </div>
  <div class="detail">
    <div class="detail-img"><img src="<?= e(product_image($product['image'])) ?>" alt="<?= e($product['name']) ?>"></div>
    <div>
      <span class="p-cat"><?= e($product['category']) ?></span>
      <h1><?= e($product['name']) ?></h1>
      <span class="price"><?= money($product['price']) ?></span>
      <p class="desc"><?= nl2br(e($product['description'])) ?></p>
      <dl class="meta-list">
        <dt>Category</dt><dd><?= e($product['category']) ?></dd>
        <dt>Availability</dt><dd class="<?= $stockClass ?>"><?= e($stockText) ?></dd>
        <dt>Delivery</dt><dd><?= $product['price'] >= FREE_SHIPPING_ABOVE ? 'Free shipping' : 'Shipping ' . money(SHIPPING_FEE) . ' (free over ' . money(FREE_SHIPPING_ABOVE) . ')' ?></dd>
      </dl>

      <form method="post" action="cart.php" class="buy-row">
        <?= csrf_field() ?>
        <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
        <input type="hidden" name="back" value="<?= e($back) ?>">
        <div class="qty">
          <button type="button" data-step="-1" aria-label="Decrease quantity">−</button>
          <input id="dQty" name="quantity" type="number" value="1" min="1" max="<?= max($stock, 1) ?>" aria-label="Quantity">
          <button type="button" data-step="1" aria-label="Increase quantity">+</button>
        </div>
        <button class="btn" type="submit" name="action" value="add" <?= $stock === 0 ? 'disabled' : '' ?>>Add to Cart</button>
        <button class="btn btn-outline" type="submit" name="action" value="buy_now" <?= $stock === 0 ? 'disabled' : '' ?>>Buy Now</button>
      </form>
    </div>
  </div>

  <?php if ($related): ?>
    <div class="section-head" style="margin-top:72px"><h2>You may also like</h2></div>
    <div class="p-grid"><?php foreach ($related as $r) echo product_card_html($r); ?></div>
  <?php endif; ?>
</div>

<script>
  document.querySelectorAll('[data-step]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var inp = btn.parentElement.querySelector('input');
      var max = Number(inp.max) || 99, step = Number(btn.dataset.step);
      inp.value = Math.max(1, Math.min(max, (Number(inp.value) || 1) + step));
    });
  });
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
