<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin('../');

$page_title = 'Dashboard';
$admin_active = 'dashboard';

$totalProducts  = (int)db()->query('SELECT COUNT(*) FROM products')->fetchColumn();
$totalCustomers = (int)db()->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
$totalOrders    = (int)db()->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$pendingOrders  = (int)db()->query("SELECT COUNT(*) FROM orders WHERE status = 'Pending'")->fetchColumn();

$STATUSES = ['Pending', 'Confirmed', 'Shipped', 'Delivered', 'Cancelled'];
$statusCounts = array_fill_keys($STATUSES, 0);
$st = db()->query('SELECT status, COUNT(*) n FROM orders GROUP BY status');
foreach ($st->fetchAll() as $row) {
    $statusCounts[$row['status']] = (int)$row['n'];
}

$recent = db()->query('SELECT * FROM orders ORDER BY created_at DESC LIMIT 5')->fetchAll();

$byCat = db()->query("SELECT oi.product_name, p.category, SUM(oi.price*oi.quantity) total
                       FROM order_items oi
                       JOIN orders o ON o.id = oi.order_id
                       LEFT JOIN products p ON p.id = oi.product_id
                       WHERE o.status != 'Cancelled'
                       GROUP BY p.category")->fetchAll();
$catTotals = [];
foreach ($byCat as $r) {
    $cat = $r['category'] ?? 'Other';
    $catTotals[$cat] = ($catTotals[$cat] ?? 0) + (float)$r['total'];
}
arsort($catTotals);
$topCat = $catTotals ? max($catTotals) : 1;

require __DIR__ . '/includes_header.php';
?>

<div class="stat-grid">
  <div class="stat"><div class="ico">◇</div><div><small>Total Products</small><b><?= $totalProducts ?></b></div></div>
  <div class="stat"><div class="ico">☺</div><div><small>Total Customers</small><b><?= $totalCustomers ?></b></div></div>
  <div class="stat"><div class="ico">✉</div><div><small>Total Orders</small><b><?= $totalOrders ?></b></div></div>
  <div class="stat"><div class="ico">◷</div><div><small>Pending Orders</small><b><?= $pendingOrders ?></b></div></div>
</div>

<div class="dash-grid">
  <div class="panel">
    <h3>Orders by status</h3>
    <div class="donut-wrap">
      <?php
        $colors = ['Pending' => '#e6b34d', 'Confirmed' => '#a68bd8', 'Shipped' => '#6fa3cf', 'Delivered' => '#6fae7d', 'Cancelled' => '#d98487'];
        $acc = 0; $segs = [];
        foreach ($STATUSES as $s) {
            $from = $totalOrders ? $acc / $totalOrders * 100 : 0;
            $acc += $statusCounts[$s];
            $to = $totalOrders ? $acc / $totalOrders * 100 : 0;
            $segs[] = $colors[$s] . " {$from}% {$to}%";
        }
      ?>
      <div class="donut" style="background:conic-gradient(<?= implode(',', $segs) ?>)" data-total="<?= $totalOrders ?>"></div>
      <div class="legend">
        <?php foreach ($STATUSES as $s): ?>
          <div><i style="background:<?= $colors[$s] ?>"></i><?= $s ?> — <?= $statusCounts[$s] ?></div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <div class="panel">
    <h3>Sales by category</h3>
    <?php if ($catTotals): foreach ($catTotals as $cat => $val): ?>
      <div class="hbar">
        <div style="display:flex;justify-content:space-between"><span><?= e($cat) ?></span><b><?= money($val) ?></b></div>
        <div class="track"><span style="width:<?= $topCat ? round($val / $topCat * 100) : 0 ?>%"></span></div>
      </div>
    <?php endforeach; else: ?><p class="muted">No sales yet.</p><?php endif; ?>
  </div>
</div>

<div class="panel">
  <h3>Recent orders</h3>
  <div class="table-wrap">
    <table class="table">
      <thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th></tr></thead>
      <tbody>
      <?php if ($recent): foreach ($recent as $o): ?>
        <tr>
          <td><b>FJ-<?= str_pad($o['id'], 5, '0', STR_PAD_LEFT) ?></b></td>
          <td><?= e($o['full_name']) ?></td>
          <td><?= money($o['total']) ?></td>
          <td><span class="status st-<?= strtolower($o['status']) ?>"><?= e($o['status']) ?></span></td>
        </tr>
      <?php endforeach; else: ?>
        <tr><td colspan="4" class="muted" style="text-align:center;padding:32px">No orders yet.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/includes_footer.php'; ?>
