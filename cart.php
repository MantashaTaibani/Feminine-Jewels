<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$userId = (int)$_SESSION['user_id'];

/* ---------------------------------------------------------------
   Handle POST actions: add / inc / dec / remove
--------------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        flash('bad', 'Your session expired. Please try again.');
        redirect(safe_back($_POST['back'] ?? null, 'cart.php'));
    }

    $action    = $_POST['action'] ?? 'add';
    $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $qty       = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT) ?: 1;
    $back      = safe_back($_POST['back'] ?? null, 'cart.php');

    if ($productId) {
        $pst = db()->prepare('SELECT * FROM products WHERE id = ?');
        $pst->execute([$productId]);
        $product = $pst->fetch();

        if (!$product) {
            flash('bad', 'That product no longer exists.');
        } elseif ((int)$product['stock'] < 1) {
            flash('warn', 'Sorry, this piece is sold out.');
        } else {
            switch ($action) {
                case 'add':
                case 'buy_now':
                    $cst = db()->prepare('SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?');
                    $cst->execute([$userId, $productId]);
                    $have = (int)$cst->fetchColumn();
                    $newQty = min((int)$product['stock'], $have + max(1, $qty));
                    $up = db()->prepare('INSERT INTO cart (user_id, product_id, quantity) VALUES (?,?,?)
                                         ON DUPLICATE KEY UPDATE quantity = ?');
                    $up->execute([$userId, $productId, $newQty, $newQty]);
                    if ($action === 'buy_now') {
                        redirect('checkout.php');
                    }
                    flash('ok', $product['name'] . ' added to your cart.');
                    break;

                case 'inc':
                    $cst = db()->prepare('SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?');
                    $cst->execute([$userId, $productId]);
                    $have = (int)$cst->fetchColumn();
                    if ($have < (int)$product['stock']) {
                        db()->prepare('UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?')
                            ->execute([$userId, $productId]);
                    } else {
                        flash('warn', 'Only ' . (int)$product['stock'] . ' in stock.');
                    }
                    break;

                case 'dec':
                    db()->prepare('UPDATE cart SET quantity = GREATEST(1, quantity - 1) WHERE user_id = ? AND product_id = ?')
                        ->execute([$userId, $productId]);
                    break;

                case 'remove':
                    db()->prepare('DELETE FROM cart WHERE user_id = ? AND product_id = ?')->execute([$userId, $productId]);
                    flash('ok', 'Removed from your cart.');
                    break;
            }
        }
    }
    redirect($back);
}

/* ---------------------------------------------------------------
   Display cart
--------------------------------------------------------------- */
$page_title = 'Your Cart — ' . SITE_NAME;
$active_nav = 'cart';

$st = db()->prepare('SELECT c.quantity, p.* FROM cart c JOIN products p ON p.id = c.product_id WHERE c.user_id = ? ORDER BY c.id DESC');
$st->execute([$userId]);
$lines = $st->fetchAll();

$subtotal = 0;
foreach ($lines as $l) {
    $subtotal += $l['price'] * $l['quantity'];
}
$shipping = shipping_for($subtotal);
$total = $subtotal + $shipping;

require __DIR__ . '/includes/header.php';
?>

<div class="page-band"><div class="container"><h1>Your cart</h1><p>Review your pieces before checkout.</p></div></div>
<div class="container page-body">
  <?php if (!$lines): ?>
    <div class="empty">
      <h3>Your cart is empty</h3>
      <p>Add a few pieces you love and they'll show up here.</p>
      <a class="btn" href="products.php">Explore jewellery</a>
    </div>
  <?php else: ?>
    <div class="cart-layout">
      <div class="panel">
        <?php foreach ($lines as $l): $lineTotal = $l['price'] * $l['quantity']; ?>
          <div class="cart-row">
            <a href="product_details.php?id=<?= (int)$l['id'] ?>"><img src="<?= e(product_image($l['image'])) ?>" alt="<?= e($l['name']) ?>"></a>
            <div>
              <h3><a href="product_details.php?id=<?= (int)$l['id'] ?>"><?= e($l['name']) ?></a></h3>
              <span class="muted small"><?= e($l['category']) ?> · <?= money($l['price']) ?> each</span>
              <div style="margin-top:10px" class="qty sm">
                <form method="post" action="cart.php" style="display:contents">
                  <?= csrf_field() ?>
                  <input type="hidden" name="product_id" value="<?= (int)$l['id'] ?>">
                  <input type="hidden" name="back" value="cart.php">
                  <button type="submit" name="action" value="dec" aria-label="Decrease quantity">−</button>
                  <input value="<?= (int)$l['quantity'] ?>" readonly aria-label="Quantity">
                  <button type="submit" name="action" value="inc" aria-label="Increase quantity">+</button>
                </form>
              </div>
              <div>
                <form method="post" action="cart.php" style="display:inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="product_id" value="<?= (int)$l['id'] ?>">
                  <input type="hidden" name="back" value="cart.php">
                  <button class="link-btn" type="submit" name="action" value="remove">Remove</button>
                </form>
              </div>
            </div>
            <div class="line-total"><span class="price"><?= money($lineTotal) ?></span></div>
          </div>
        <?php endforeach; ?>
      </div>
      <aside class="panel">
        <h2 style="font-size:1.7rem;margin-bottom:8px">Order summary</h2>
        <div class="sum-row"><span>Subtotal</span><span><?= money($subtotal) ?></span></div>
        <div class="sum-row"><span>Shipping</span><span><?= $shipping ? money($shipping) : 'Free' ?></span></div>
        <div class="sum-row total"><span>Total</span><b><?= money($total) ?></b></div>
        <p class="sum-note"><?= $subtotal < FREE_SHIPPING_ABOVE ? 'Add ' . money(FREE_SHIPPING_ABOVE - $subtotal) . ' more for free shipping.' : 'You have free shipping on this order.' ?></p>
        <a class="btn btn-block" href="checkout.php">Proceed to Checkout</a>
        <a class="btn btn-ghost btn-block" href="products.php" style="margin-top:8px">Continue shopping</a>
      </aside>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
