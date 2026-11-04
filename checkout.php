<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$userId = (int)$_SESSION['user_id'];

$st = db()->prepare('SELECT c.quantity, p.* FROM cart c JOIN products p ON p.id = c.product_id WHERE c.user_id = ? ORDER BY c.id DESC');
$st->execute([$userId]);
$lines = $st->fetchAll();

$subtotal = 0;
foreach ($lines as $l) {
    $subtotal += $l['price'] * $l['quantity'];
}
$shipping = shipping_for($subtotal);
$total = $subtotal + $shipping;

$errors = [];
$old = ['name' => $_SESSION['name'], 'email' => $_SESSION['email'], 'phone' => '', 'address' => '', 'city' => '', 'state' => '', 'pincode' => ''];

$STATES = ['Andhra Pradesh','Assam','Bihar','Chhattisgarh','Delhi','Goa','Gujarat','Haryana','Himachal Pradesh','Jharkhand','Karnataka','Kerala','Madhya Pradesh','Maharashtra','Odisha','Punjab','Rajasthan','Tamil Nadu','Telangana','Uttar Pradesh','Uttarakhand','West Bengal','Other'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $lines) {
    if (!csrf_valid()) {
        $errors[] = 'Your session expired. Please try again.';
    }
    foreach (['name', 'email', 'phone', 'address', 'city', 'state', 'pincode'] as $f) {
        $old[$f] = trim((string)($_POST[$f] ?? ''));
    }
    if (strlen($old['name']) < 3)                          $errors[] = 'Please enter your full name.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if (!preg_match('/^[6-9]\d{9}$/', $old['phone']))      $errors[] = 'Enter a valid 10-digit mobile number.';
    if ($old['address'] === '')                            $errors[] = 'Please enter your address.';
    if ($old['city'] === '')                                $errors[] = 'Please enter your city.';
    if (!in_array($old['state'], $STATES, true))            $errors[] = 'Please select your state.';
    if (!preg_match('/^\d{6}$/', $old['pincode']))          $errors[] = 'Pincode must be 6 digits.';

    if (!$errors) {
        $pdo = db();
        try {
            $pdo->beginTransaction();

            // Re-check stock right before placing the order.
            foreach ($lines as $l) {
                $chk = $pdo->prepare('SELECT stock FROM products WHERE id = ? FOR UPDATE');
                $chk->execute([$l['id']]);
                $stock = (int)$chk->fetchColumn();
                if ($stock < (int)$l['quantity']) {
                    throw new RuntimeException($l['name'] . ' only has ' . $stock . ' left in stock.');
                }
            }

            $address = $old['address'] . ', ' . $old['city'] . ', ' . $old['state'] . ' - ' . $old['pincode'];
            $ins = $pdo->prepare('INSERT INTO orders (user_id, full_name, email, phone, address, city, state, pincode, subtotal, shipping, total, status)
                                   VALUES (?,?,?,?,?,?,?,?,?,?,?,\'Pending\')');
            $ins->execute([$userId, $old['name'], $old['email'], $old['phone'], $old['address'], $old['city'], $old['state'], $old['pincode'], $subtotal, $shipping, $total]);
            $orderId = (int)$pdo->lastInsertId();

            $itemIns = $pdo->prepare('INSERT INTO order_items (order_id, product_id, product_name, price, quantity) VALUES (?,?,?,?,?)');
            $stockUpd = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ?');
            foreach ($lines as $l) {
                $itemIns->execute([$orderId, $l['id'], $l['name'], $l['price'], $l['quantity']]);
                $stockUpd->execute([$l['quantity'], $l['id']]);
            }

            $pdo->prepare('DELETE FROM cart WHERE user_id = ?')->execute([$userId]);
            $pdo->commit();

            $_SESSION['last_order_id'] = $orderId;
            redirect('order_confirmation.php');
        } catch (Throwable $ex) {
            $pdo->rollBack();
            $errors[] = $ex instanceof RuntimeException ? $ex->getMessage() : 'We could not place your order. Please try again.';
            if (!($ex instanceof RuntimeException)) {
                error_log('Checkout failed: ' . $ex->getMessage());
            }
        }
    }
}

$page_title = 'Checkout — ' . SITE_NAME;
require __DIR__ . '/includes/header.php';
?>

<div class="page-band"><div class="container"><h1>Checkout</h1><p>Tell us where to send your order.</p></div></div>
<div class="container page-body">
  <?php if (!$lines): ?>
    <div class="empty">
      <h3>Nothing to check out yet</h3>
      <p>Your cart is empty.</p>
      <a class="btn" href="products.php">Explore jewellery</a>
    </div>
  <?php else: ?>
    <?php if ($errors): ?>
      <div class="toast bad show" style="position:static;max-width:none;transform:none;opacity:1;margin-bottom:20px">
        <?php foreach ($errors as $err) echo e($err) . '<br>'; ?>
      </div>
    <?php endif; ?>
    <div class="cart-layout">
      <div class="panel">
        <h2 style="font-size:1.8rem;margin-bottom:18px">Delivery details</h2>
        <form method="POST" action="checkout.php" novalidate>
          <?= csrf_field() ?>
          <div class="form-grid">
            <div class="field"><label class="lbl" for="kName">Full name</label><input id="kName" name="name" value="<?= e($old['name']) ?>" autocomplete="name"></div>
            <div class="field"><label class="lbl" for="kEmail">Email</label><input id="kEmail" name="email" type="email" value="<?= e($old['email']) ?>" autocomplete="email"></div>
            <div class="field full"><label class="lbl" for="kPhone">Phone number</label><input id="kPhone" name="phone" type="tel" value="<?= e($old['phone']) ?>" autocomplete="tel"></div>
            <div class="field full"><label class="lbl" for="kAddr">Address</label><input id="kAddr" name="address" value="<?= e($old['address']) ?>" placeholder="House no., street, area" autocomplete="street-address"></div>
            <div class="field"><label class="lbl" for="kCity">City</label><input id="kCity" name="city" value="<?= e($old['city']) ?>" autocomplete="address-level2"></div>
            <div class="field"><label class="lbl" for="kState">State</label>
              <select id="kState" name="state">
                <option value="">Select state</option>
                <?php foreach ($STATES as $s): ?><option <?= $old['state'] === $s ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="field"><label class="lbl" for="kPin">Pincode</label><input id="kPin" name="pincode" value="<?= e($old['pincode']) ?>" inputmode="numeric" maxlength="6" autocomplete="postal-code"></div>
          </div>
          <div class="pay-note"><span>✦</span><span>Cash on delivery. No online payment is taken on this demo store.</span></div>
          <button class="btn btn-block" type="submit">Place Order</button>
        </form>
      </div>
      <aside class="panel">
        <h2 style="font-size:1.7rem;margin-bottom:8px">Order summary</h2>
        <?php foreach ($lines as $l): ?>
          <div class="mini-item">
            <img src="<?= e(product_image($l['image'])) ?>" alt="">
            <div><?= e($l['name']) ?><br><small>Qty <?= (int)$l['quantity'] ?> × <?= money($l['price']) ?></small></div>
            <b><?= money($l['price'] * $l['quantity']) ?></b>
          </div>
        <?php endforeach; ?>
        <div style="margin-top:10px">
          <div class="sum-row"><span>Subtotal</span><span><?= money($subtotal) ?></span></div>
          <div class="sum-row"><span>Shipping</span><span><?= $shipping ? money($shipping) : 'Free' ?></span></div>
          <div class="sum-row total"><span>Total</span><b><?= money($total) ?></b></div>
        </div>
        <a class="btn btn-ghost btn-block btn-sm" href="cart.php" style="margin-top:8px">Edit cart</a>
      </aside>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
