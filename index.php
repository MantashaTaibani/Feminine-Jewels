<?php
require_once __DIR__ . '/includes/auth.php';
$page_title = SITE_NAME . ' — Elegance That Tells Your Story';
$active_nav = 'home';

$categories = db()->query('SELECT * FROM categories ORDER BY name')->fetchAll();
$featured   = db()->query('SELECT * FROM products WHERE stock > 0 ORDER BY created_at DESC LIMIT 6')->fetchAll();
if (count($featured) < 6) {
    $featured = db()->query('SELECT * FROM products ORDER BY created_at DESC LIMIT 6')->fetchAll();
}

require __DIR__ . '/includes/header.php';
?>

<div class="hero">
  <div class="container hero-inner">
    <div>
      <span class="eyebrow">Fashion &amp; fine imitation jewellery</span>
      <h1>Elegance That Tells Your Story</h1>
      <p class="lead">Pearl drops, rose-gold rings and hand-finished sets made for the moments you'll remember. Find the piece that already sounds like you.</p>
      <div class="hero-btns">
        <a class="btn" href="products.php">Explore Jewellery</a>
        <a class="btn btn-outline" href="products.php?cat=Jewellery+Set">Shop Collection</a>
      </div>
      <div class="hero-facts">
        <div><b>500+</b><span>Designs</span></div>
        <div><b>4.8</b><span>Average rating</span></div>
        <div><b>Free</b><span>Shipping over <?= money(FREE_SHIPPING_ABOVE) ?></span></div>
      </div>
    </div>
    <div class="hero-visual" aria-hidden="true">
      <img class="art" src="<?= e(product_image('royal-pearl-jewellery-set.jpg')) ?>" alt="">
      <svg class="gem" viewBox="0 0 200 200"><polygon points="100,10 180,70 150,190 50,190 20,70" fill="none" stroke="#fdf8f4" stroke-width="2"/><polygon points="100,10 180,70 100,100" fill="#fdf8f4" opacity=".25"/><polygon points="20,70 100,10 100,100" fill="#fdf8f4" opacity=".15"/><polygon points="100,100 150,190 50,190" fill="#fdf8f4" opacity=".1"/></svg>
      <span class="sp"></span><span class="sp"></span><span class="sp"></span>
    </div>
  </div>
</div>

<div class="strip"><div class="container strip-inner">
  <div class="strip-item"><i>✦</i><div><b>Free shipping</b>On orders over <?= money(FREE_SHIPPING_ABOVE) ?></div></div>
  <div class="strip-item"><i>✦</i><div><b>Easy returns</b>7-day return window</div></div>
  <div class="strip-item"><i>✦</i><div><b>Gift-ready</b>Every order boxed</div></div>
  <div class="strip-item"><i>✦</i><div><b>Secure checkout</b>Your details stay private</div></div>
</div></div>

<div class="section" id="categories">
  <div class="container">
    <div class="section-head"><h2>Shop by category</h2><p>Find your next favourite piece.</p></div>
    <div class="cat-grid">
      <?php
      $countStmt = db()->prepare('SELECT COUNT(*) FROM products WHERE category = ?');
      foreach ($categories as $c):
        $countStmt->execute([$c['name']]);
        $n = (int)$countStmt->fetchColumn(); ?>
        <a class="cat-card" href="products.php?cat=<?= urlencode($c['name']) ?>">
          <img src="<?= e(product_image('placeholder.svg')) ?>" onerror="this.style.display='none'" style="display:none" alt="">
          <div style="background:var(--blush);border-radius:26px;aspect-ratio:1;display:flex;align-items:center;justify-content:center;box-shadow:var(--shadow-sm);margin-bottom:12px">
            <span style="font-family:var(--font-display);font-size:1.6rem;color:var(--rosegold-dark)"><?= e($c['name']) ?></span>
          </div>
          <h3><?= e($c['name']) ?></h3>
          <span><?= (int)$n ?> <?= $n == 1 ? 'piece' : 'pieces' ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<div class="section tint">
  <div class="container">
    <div class="section-head"><h2>Featured pieces</h2><p>Our most-loved designs this season.</p></div>
    <div class="p-grid">
      <?php foreach ($featured as $p) echo product_card_html($p); ?>
    </div>
    <p style="text-align:center;margin-top:36px"><a class="btn btn-outline" href="products.php">View all jewellery</a></p>
  </div>
</div>

<div class="section">
  <div class="container">
    <div class="banner">
      <div>
        <h2>Complete the look with a matching set</h2>
        <p>Necklace, earrings and bracelet, designed to be worn together.</p>
      </div>
      <a class="btn" href="products.php?cat=Jewellery+Set">Browse jewellery sets</a>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
