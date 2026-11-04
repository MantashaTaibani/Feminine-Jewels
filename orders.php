<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$page_title = 'My Orders — ' . SITE_NAME;
$active_nav = 'orders';

$st = db()->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
$st->execute([$_SESSION['user_id']]);
$orders = $st->fetchAll();

$viewId = filter_input(INPUT_GET, 'view', FILTER_VALIDATE_INT);
$viewOrder = null;
$viewItems = [];
if ($viewId) {
    $vs = db()->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ?');
    $vs->execute([$viewId, $_SESSION['user_id']]);
    $viewOrder = $vs->fetch();
    if ($viewOrder) {
        $vi = db()->prepare('SELECT * FROM order_items WHERE order_id = ?');
        $vi->execute([$viewOrder['id']]);
        $viewItems = $vi->fetchAll();
    }
}

require __DIR__ . '/includes/header.php';
?>

<div class="page-band"><div class="container"><h1>My orders</h1><p>Track and review your previous orders.</p></div></div>
<div class="container page-body">
  <div class="panel">
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Order ID</th><th>Date</th><th>Total</th><th>Status</th><th>Details</th></tr></thead>
        <tbody>
        <?php if ($orders): foreach ($orders as $o): ?>
          <tr>
            <td><b>FJ-<?= str_pad($o['id'], 5, '0', STR_PAD_LEFT) ?></b></td>
            <td><?= date('j M Y', strtotime($o['created_at'])) ?></td>
            <td><?= money($o['total']) ?></td>
            <td><span class="status st-<?= strtolower($o['status']) ?>"><?= e($o['status']) ?></span></td>
            <td><a class="btn btn-outline btn-sm" href="orders.php?view=<?= (int)$o['id'] ?>">View Details</a></td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="5" class="muted" style="text-align:center;padding:32px">You haven't placed any orders yet.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php if ($viewOrder): ?>
    <div class="panel" style="margin-top:24px">
      <h2 style="font-size:1.7rem;margin-bottom:16px">Order FJ-<?= str_pad($viewOrder['id'], 5, '0', STR_PAD_LEFT) ?></h2>
      <?php foreach ($viewItems as $i): ?>
        <div class="mini-item">
          <div><?= e($i['product_name']) ?><br><small>Qty <?= (int)$i['quantity'] ?> × <?= money($i['price']) ?></small></div>
          <b><?= money($i['price'] * $i['quantity']) ?></b>
        </div>
      <?php endforeach; ?>
      <div style="margin-top:8px">
        <div class="sum-row"><span>Subtotal</span><span><?= money($viewOrder['subtotal']) ?></span></div>
        <div class="sum-row"><span>Shipping</span><span><?= $viewOrder['shipping'] > 0 ? money($viewOrder['shipping']) : 'Free' ?></span></div>
        <div class="sum-row total"><span>Total</span><b><?= money($viewOrder['total']) ?></b></div>
      </div>
      <p class="muted small" style="margin-top:10px">Ship to: <?= e($viewOrder['address'] . ', ' . $viewOrder['city'] . ', ' . $viewOrder['state'] . ' - ' . $viewOrder['pincode']) ?></p>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
