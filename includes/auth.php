<?php
/**
 * Sessions, login guards and small helpers shared by every page.
 * Include with:  require_once __DIR__ . '/includes/auth.php';
 * (from admin/ use  __DIR__ . '/../includes/auth.php')
 */
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ---------- output helpers ---------- */
function e($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function money($n): string
{
    return '₹' . number_format((float)$n, 0, '.', ',');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit();
}

/* ---------- flash messages ---------- */
function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function pull_flash(): ?array
{
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

/* ---------- CSRF ---------- */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_valid(): bool
{
    return isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], (string)$_POST['csrf']);
}

/* ---------- login state ---------- */
function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

function is_admin(): bool
{
    return is_logged_in() && ($_SESSION['role'] ?? '') === 'admin';
}

/** $root is '' for pages in the project root and '../' for pages inside admin/ */
function require_login(string $root = ''): void
{
    if (!is_logged_in()) {
        flash('warn', 'Please log in to continue.');
        redirect($root . 'login.php');
    }
}

function require_admin(string $root = ''): void
{
    if (!is_logged_in()) {
        flash('warn', 'Please log in as an admin to continue.');
        redirect($root . 'login.php');
    }
    if (!is_admin()) {
        flash('warn', 'That area is for administrators only.');
        redirect($root . 'dashboard.php');
    }
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['name']    = $user['name'];
    $_SESSION['email']   = $user['email'];
    $_SESSION['role']    = $user['role'];
}

/* ---------- cart / product helpers ---------- */
function cart_count(): int
{
    if (!is_logged_in()) {
        return 0;
    }
    try {
        $st = db()->prepare('SELECT COALESCE(SUM(quantity),0) FROM cart WHERE user_id = ?');
        $st->execute([$_SESSION['user_id']]);
        return (int)$st->fetchColumn();
    } catch (Throwable $t) {
        return 0;
    }
}

function shipping_for(float $subtotal): float
{
    return ($subtotal <= 0 || $subtotal >= FREE_SHIPPING_ABOVE) ? 0 : SHIPPING_FEE;
}

/** URL of a product image, falling back to a neutral placeholder if the file is missing. */
function product_image(?string $file, string $root = ''): string
{
    $file = basename((string)$file);
    if ($file !== '' && is_file(__DIR__ . '/../assets/images/products/' . $file)) {
        return $root . 'assets/images/products/' . rawurlencode($file);
    }
    return $root . 'assets/images/placeholder.svg';
}

/** Only allow redirects back to our own simple pages (blocks open-redirects). */
function safe_back(?string $url, string $fallback = 'products.php'): string
{
    $url = (string)$url;
    return preg_match('/^[a-z_]+\.php(\?[A-Za-z0-9=&_%.\-]*)?$/', $url) ? $url : $fallback;
}

function product_card_html(array $p, string $root = ''): string
{
    $tag = '';
    if ((int)$p['stock'] === 0) {
        $tag = '<span class="tag">Sold out</span>';
    } elseif ((int)$p['stock'] <= 5) {
        $tag = '<span class="tag">Only ' . (int)$p['stock'] . ' left</span>';
    }
    $url  = $root . 'product_details.php?id=' . (int)$p['id'];
    $qs = $_SERVER['QUERY_STRING'] ?? '';
    $back = basename($_SERVER['PHP_SELF']) . ($qs ? '?' . $qs : '');
    $dis  = (int)$p['stock'] === 0 ? 'disabled' : '';
    ob_start(); ?>
    <article class="p-card">
      <a class="p-img" href="<?= e($url) ?>" aria-label="View <?= e($p['name']) ?>">
        <img src="<?= e(product_image($p['image'], $root)) ?>" alt="<?= e($p['name']) ?>" loading="lazy"><?= $tag ?>
      </a>
      <div class="p-body">
        <span class="p-cat"><?= e($p['category']) ?></span>
        <h3><a href="<?= e($url) ?>"><?= e($p['name']) ?></a></h3>
        <span class="price"><?= money($p['price']) ?></span>
        <div class="p-actions">
          <form method="post" action="<?= e($root) ?>cart.php">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
            <input type="hidden" name="quantity" value="1">
            <input type="hidden" name="back" value="<?= e($back) ?>">
            <button class="btn btn-sm btn-block" type="submit" <?= $dis ?>>Add to Cart</button>
          </form>
          <a class="btn btn-outline btn-sm" href="<?= e($url) ?>">View Details</a>
        </div>
      </div>
    </article>
    <?php
    return ob_get_clean();
}
