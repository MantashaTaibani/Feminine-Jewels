<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$orderId = (int)($_SESSION['last_order_id'] ?? 0);
$st = db()->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ?');
$st->execute([$orderId, $_SESSION['user_id']]);
$order = $st->fetch();

if (!$order) {
    redirect('orders.php');
}

$itSt = db()->prepare('SELECT oi.*, p.image FROM order_items oi LEFT JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ?');
$itSt->execute([$order['id']]);
$items = $itSt->fetchAll();

$page_title = 'Order Confirmed — ' . SITE_NAME;
require __DIR__ . '/includes/header.php';
?>

<div class="container page-body">
  <div class="confirm">
    <div class="check-circle"><svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg></div>
    <h1>Thank you for your order!</h1>
    <p class="muted">We've received it and will email a confirmation shortly.</p>
    <div class="info-grid">
      <div><small>Order ID</small><b>FJ-<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></b></div>
      <div><small>Order date</small><b><?= date('j M Y', strtotime($order['created_at'])) ?></b></div>
      <div><small>Status</small><span class="status st-<?= strtolower($order['status']) ?>"><?= e($order['status']) ?></span></div>
    </div>
    <div class="panel">
      <?php foreach ($items as $i): ?>
        <div class="mini-item">
          <img src="<?= e(product_image($i['image'] ?? null)) ?>" alt="">
          <div><?= e($i['product_name']) ?><br><small>Qty <?= (int)$i['quantity'] ?> × <?= money($i['price']) ?></small></div>
          <b><?= money($i['price'] * $i['quantity']) ?></b>
        </div>
      <?php endforeach; ?>
      <div style="margin-top:8px">
        <div class="sum-row"><span>Subtotal</span><span><?= money($order['subtotal']) ?></span></div>
        <div class="sum-row"><span>Shipping</span><span><?= $order['shipping'] > 0 ? money($order['shipping']) : 'Free' ?></span></div>
        <div class="sum-row total"><span>Total</span><b><?= money($order['total']) ?></b></div>
      </div>
      <p class="muted small" style="margin-top:12px">Delivering to: <?= e($order['address'] . ', ' . $order['city'] . ', ' . $order['state'] . ' - ' . $order['pincode']) ?></p>
    </div>
    <div class="hero-btns" style="justify-content:center">
      <a class="btn" href="products.php">Continue shopping</a>
      <a class="btn btn-outline" href="orders.php">View my orders</a>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
