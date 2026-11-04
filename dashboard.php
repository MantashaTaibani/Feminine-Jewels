<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$page_title = 'My Dashboard — ' . SITE_NAME;
$active_nav = 'account';

$st = db()->prepare('SELECT COUNT(*) FROM orders WHERE user_id = ?');
$st->execute([$_SESSION['user_id']]);
$orderCount = (int)$st->fetchColumn();

require __DIR__ . '/includes/header.php';
?>

<div class="page-band"><div class="container"><h1>My account</h1><p>Manage your details and track your orders.</p></div></div>

<div class="container page-body">
  <div class="account-layout">
    <aside class="panel">
      <div class="avatar"><?= e(substr($_SESSION['name'], 0, 1)) ?></div>
      <h2 style="font-size:1.9rem;margin:0"><?= e($_SESSION['name']) ?></h2>
      <div class="profile-list">
        <div><small>Email</small><?= e($_SESSION['email']) ?></div>
        <div><small>Account type</small><?= e(ucfirst($_SESSION['role'])) ?></div>
      </div>
      <div class="stack">
        <a class="btn btn-ghost btn-block" href="logout.php">Logout</a>
      </div>
    </aside>

    <div class="panel">
      <h2>Welcome, <?= e($_SESSION['name']) ?></h2>
      <p class="muted" style="margin-bottom:26px">You have placed <?= $orderCount ?> order<?= $orderCount === 1 ? '' : 's' ?> with us so far.</p>

      <div class="cat-grid" style="grid-template-columns:repeat(4,1fr)">
        <a class="cat-card" href="products.php">
          <div style="background:var(--blush);border-radius:22px;aspect-ratio:1;display:flex;align-items:center;justify-content:center;box-shadow:var(--shadow-sm);margin-bottom:10px;font-size:2rem">💎</div>
          <h3 style="font-size:1.15rem">Browse Jewellery</h3>
        </a>
        <a class="cat-card" href="cart.php">
          <div style="background:var(--blush);border-radius:22px;aspect-ratio:1;display:flex;align-items:center;justify-content:center;box-shadow:var(--shadow-sm);margin-bottom:10px;font-size:2rem">🛍️</div>
          <h3 style="font-size:1.15rem">My Cart</h3>
        </a>
        <a class="cat-card" href="orders.php">
          <div style="background:var(--blush);border-radius:22px;aspect-ratio:1;display:flex;align-items:center;justify-content:center;box-shadow:var(--shadow-sm);margin-bottom:10px;font-size:2rem">📦</div>
          <h3 style="font-size:1.15rem">My Orders</h3>
        </a>
        <a class="cat-card" href="dashboard.php">
          <div style="background:var(--blush);border-radius:22px;aspect-ratio:1;display:flex;align-items:center;justify-content:center;box-shadow:var(--shadow-sm);margin-bottom:10px;font-size:2rem">👤</div>
          <h3 style="font-size:1.15rem">Profile</h3>
        </a>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
