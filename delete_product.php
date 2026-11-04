<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin('../');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valid()) {
    flash('bad', 'Invalid request.');
    redirect('products.php');
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if ($id) {
    db()->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
    flash('ok', 'Product deleted.');
} else {
    flash('bad', 'Invalid product.');
}
redirect('products.php');
