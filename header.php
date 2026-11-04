<?php
/**
 * Shared storefront header.
 * Expects (optionally) $page_title to be set before including this file.
 * $root should be '' — this file is only used by pages in the project root.
 */
require_once __DIR__ . '/auth.php';
$root = '';
$flash = pull_flash();
$cartCount = cart_count();
$activeNav = $active_nav ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($page_title ?? SITE_NAME) ?></title>
<meta name="description" content="Feminine Jewells — fashion and fine imitation jewellery.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,500&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e($root) ?>assets/css/style.css">
</head>
<body>

<header class="site-header">
  <div class="container nav-inner">
    <a href="index.php" class="logo" aria-label="Feminine Jewells home">
      <svg viewBox="0 0 200 200" aria-hidden="true"><polygon points="100,10 180,70 150,190 50,190 20,70" fill="#f7e3dd" stroke="#c48a5c" stroke-width="10"/><polygon points="100,10 180,70 100,100" fill="#d9a679" opacity=".55"/><polygon points="20,70 100,10 100,100" fill="#e7b8ba" opacity=".7"/></svg>
      <span>Feminine Jewells</span>
    </a>

    <nav class="main-nav" id="mainNav" aria-label="Main">
      <a href="index.php" data-nav="home" class="<?= $activeNav === 'home' ? 'active' : '' ?>">Home</a>
      <a href="products.php" data-nav="products" class="<?= $activeNav === 'products' ? 'active' : '' ?>">Jewellery</a>
      <a href="cart.php" data-nav="cart" class="mobile-only <?= $activeNav === 'cart' ? 'active' : '' ?>">Cart</a>
      <?php if (is_logged_in()): ?>
        <a href="dashboard.php" class="mobile-only" data-nav="account">My Account</a>
        <a href="orders.php" class="mobile-only" data-nav="orders">My Orders</a>
        <a href="logout.php" class="mobile-only">Logout</a>
      <?php else: ?>
        <a href="login.php" class="mobile-only">Login</a>
      <?php endif; ?>
    </nav>

    <div class="nav-actions">
      <button class="icon-btn" id="searchBtn" aria-label="Search jewellery">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
      </button>
      <a class="icon-btn" href="cart.php" aria-label="Shopping cart">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8h12l-1 12H7L6 8z"/><path d="M9 8a3 3 0 016 0"/></svg>
        <span class="badge" id="cartCount"><?= (int)$cartCount ?></span>
      </a>
      <?php if (is_logged_in()): ?>
        <a class="btn btn-outline btn-sm" href="dashboard.php" data-nav="account">My Account</a>
        <a class="btn btn-sm" href="logout.php">Logout</a>
      <?php else: ?>
        <a class="btn btn-outline btn-sm" href="signup.php">Sign Up</a>
        <a class="btn btn-sm" href="login.php">Login</a>
      <?php endif; ?>
      <button class="icon-btn menu-btn" id="menuBtn" aria-label="Open menu" aria-expanded="false">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
      </button>
    </div>
  </div>

  <div class="search-bar" id="searchBar" hidden>
    <div class="container">
      <form id="hSearchForm" action="products.php" method="GET" role="search">
        <input type="search" id="hSearch" name="q" placeholder="Search by product name or category…" autocomplete="off" aria-label="Search" value="<?= e($_GET['q'] ?? '') ?>">
        <button class="btn" type="submit">Search</button>
      </form>
    </div>
  </div>
</header>

<?php if ($flash): ?>
  <div class="container" style="padding-top:18px">
    <div class="toast <?= $flash['type'] === 'bad' ? 'bad' : ($flash['type'] === 'warn' ? 'warn' : '') ?> show" style="position:static;max-width:none;transform:none;opacity:1">
      <?= e($flash['message']) ?>
    </div>
  </div>
<?php endif; ?>
