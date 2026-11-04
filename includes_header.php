<?php
/**
 * Included by every admin/*.php page AFTER require_admin('../') has run.
 * Expects $page_title and $admin_active to be set.
 */
$flash = pull_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($page_title ?? 'Admin') ?> — <?= e(SITE_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,500&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="is-admin">

<div class="admin-shell">
  <aside class="admin-side" id="adminSide">
    <div class="logo">Feminine Jewells</div>
    <div class="role">Admin panel</div>
    <nav aria-label="Admin">
      <a href="dashboard.php" class="<?= $admin_active === 'dashboard' ? 'active' : '' ?>">▦ &nbsp;Dashboard</a>
      <a href="products.php" class="<?= $admin_active === 'products' ? 'active' : '' ?>">◇ &nbsp;Products</a>
      <a href="orders.php" class="<?= $admin_active === 'orders' ? 'active' : '' ?>">✉ &nbsp;Orders</a>
    </nav>
    <div class="spacer"></div>
    <a href="../index.php">← &nbsp;View store</a>
    <a href="../logout.php" class="side-link">⏻ &nbsp;Logout</a>
  </aside>

  <div class="admin-main">
    <div class="admin-top">
      <button class="icon-btn menu" id="adminMenuBtn" aria-label="Toggle menu"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg></button>
      <h1><?= e($page_title ?? 'Dashboard') ?></h1>
      <span class="grow"></span>
      <span class="muted small">Signed in as <?= e($_SESSION['name']) ?></span>
    </div>

    <div class="admin-body">
      <?php if ($flash): ?>
        <div class="toast <?= $flash['type'] === 'bad' ? 'bad' : ($flash['type'] === 'warn' ? 'warn' : '') ?> show" style="position:static;max-width:none;transform:none;opacity:1;margin-bottom:20px">
          <?= e($flash['message']) ?>
        </div>
      <?php endif; ?>
