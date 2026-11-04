<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin('../');

$STATUSES = ['Pending', 'Confirmed', 'Shipped', 'Delivered', 'Cancelled'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (csrf_valid()) {
        $orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
        $status  = $_POST['status'] ?? '';
        if ($orderId && in_array($status, $STATUSES, true)) {
            db()->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$status, $orderId]);
            flash('ok', 'Order FJ-' . str_pad((string)$orderId, 5, '0', STR_PAD_LEFT) . ' marked ' . $status . '.');
        }
    }
    redirect('orders.php' . (!empty($_GET['status']) ? '?status=' . urlencode($_GET['status']) : ''));
}

$page_title = 'Orders';
$admin_active = 'orders';

$filterStatus = in_array($_GET['status'] ?? '', $STATUSES, true) ? $_GET['status'] : '';
$sql = 'SELECT * FROM orders';
$params = [];
if ($filterStatus !== '') { $sql .= ' WHERE status = ?'; $params[] = $filterStatus; }
$sql .= ' ORDER BY created_at DESC';
$st = db()->prepare($sql);
$st->execute($params);
$orders = $st->fetchAll();

$viewId = filter_input(INPUT_GET, 'view', FILTER_VALIDATE_INT);
$viewOrder = null; $viewItems = [];
if ($viewId) {
    $vs = db()->prepare('SELECT * FROM orders WHERE id = ?');
    $vs->execute([$viewId]);
    $viewOrder = $vs->fetch();
    if ($viewOrder) {
        $vi = db()->prepare('SELECT * FROM order_items WHERE order_id = ?');
        $vi->execute([$viewOrder['id']]);
        $viewItems = $vi->fetchAll();
    }
}

require __DIR__ . '/includes_header.php';
?>

<form class="toolbar" method="GET" action="orders.php">
  <select name="status" onchange="this.form.submit()" aria-label="Filter by status">
    <option value="">All statuses</option>
    <?php foreach ($STATUSES as $s): ?><option value="<?= $s ?>" <?= $filterStatus === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?>
  </select>
</form>

<div class="panel">
  <div class="table-wrap">
    <table class="table">
      <thead><tr><th>Order ID</th><th>Customer</th><th>Date</th><th>Total</th><th>Status</th><th>Action</th></tr></thead>
      <tbody>
      <?php if ($orders): foreach ($orders as $o): ?>
        <tr>
          <td><b>FJ-<?= str_pad($o['id'], 5, '0', STR_PAD_LEFT) ?></b></td>
          <td><?= e($o['full_name']) ?></td>
          <td><?= date('j M Y', strtotime($o['created_at'])) ?></td>
          <td><?= money($o['total']) ?></td>
          <td>
            <form method="post" action="orders.php<?= $filterStatus ? '?status=' . urlencode($filterStatus) : '' ?>">
              <?= csrf_field() ?>
              <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
              <select class="status-select st-<?= strtolower($o['status']) ?>" name="status" onchange="this.form.submit()" aria-label="Status for order <?= (int)$o['id'] ?>">
                <?php foreach ($STATUSES as $s): ?><option <?= $s === $o['status'] ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?>
              </select>
            </form>
          </td>
          <td><a class="btn btn-outline btn-sm" href="orders.php?view=<?= (int)$o['id'] ?><?= $filterStatus ? '&status=' . urlencode($filterStatus) : '' ?>">View</a></td>
        </tr>
      <?php endforeach; else: ?>
        <tr><td colspan="6" class="muted" style="text-align:center;padding:32px">No orders with this status.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if ($viewOrder): ?>
  <div class="panel" style="margin-top:24px">
    <h3>Order FJ-<?= str_pad($viewOrder['id'], 5, '0', STR_PAD_LEFT) ?></h3>
    <p class="muted small">Customer: <?= e($viewOrder['full_name']) ?> · <?= e($viewOrder['email']) ?> · <?= e($viewOrder['phone']) ?></p>
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

<?php require __DIR__ . '/includes_footer.php'; ?>
