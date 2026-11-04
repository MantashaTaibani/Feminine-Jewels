<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin('../');

$categories = db()->query('SELECT name FROM categories ORDER BY name')->fetchAll(PDO::FETCH_COLUMN);
$errors = [];
$old = ['name' => '', 'category' => '', 'description' => '', 'price' => '', 'stock' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        $errors[] = 'Your session expired. Please try again.';
    }
    foreach (['name', 'category', 'description', 'price', 'stock'] as $f) {
        $old[$f] = trim((string)($_POST[$f] ?? ''));
    }

    if ($old['name'] === '')                              $errors[] = 'Please enter a product name.';
    if (!in_array($old['category'], $categories, true))   $errors[] = 'Please choose a valid category.';
    if ($old['description'] === '')                       $errors[] = 'Please enter a description.';
    if (!is_numeric($old['price']) || (float)$old['price'] <= 0)  $errors[] = 'Enter a price greater than 0.';
    if (!ctype_digit($old['stock']))                       $errors[] = 'Enter a whole number, 0 or more, for stock.';

    $imageName = '';
    if (!$errors && !empty($_FILES['image']['name'])) {
        $img = $_FILES['image'];
        if ($img['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($img['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                $errors[] = 'Product image must be a JPG, PNG or WEBP file.';
            } else {
                $imageName = 'p_' . bin2hex(random_bytes(6)) . '.' . $ext;
                $dest = __DIR__ . '/../assets/images/products/' . $imageName;
                if (!move_uploaded_file($img['tmp_name'], $dest)) {
                    $errors[] = 'Could not save the uploaded image. Please try again.';
                    $imageName = '';
                }
            }
        } elseif ($img['error'] !== UPLOAD_ERR_NO_FILE) {
            $errors[] = 'There was a problem uploading the image.';
        }
    }

    if (!$errors) {
        $st = db()->prepare('INSERT INTO products (name, category, description, price, image, stock) VALUES (?,?,?,?,?,?)');
        $st->execute([$old['name'], $old['category'], $old['description'], $old['price'], $imageName, $old['stock']]);
        flash('ok', 'Product added.');
        redirect('products.php');
    }
}

$page_title = 'Add Product';
$admin_active = 'products';
require __DIR__ . '/includes_header.php';
?>

<div class="panel" style="max-width:640px">
  <h2 style="font-size:1.8rem;margin-bottom:18px">Add product</h2>

  <?php if ($errors): ?>
    <div class="toast bad show" style="position:static;max-width:none;transform:none;opacity:1;margin-bottom:18px">
      <?php foreach ($errors as $err) echo e($err) . '<br>'; ?>
    </div>
  <?php endif; ?>

  <form method="post" action="add_product.php" enctype="multipart/form-data" novalidate>
    <?= csrf_field() ?>
    <div class="field"><label class="lbl" for="pName">Product name</label><input id="pName" name="name" value="<?= e($old['name']) ?>"></div>
    <div class="field"><label class="lbl" for="pDesc">Description</label><textarea id="pDesc" name="description"><?= e($old['description']) ?></textarea></div>
    <div class="form-grid">
      <div class="field full"><label class="lbl" for="pCat">Category</label>
        <select id="pCat" name="category">
          <option value="">Select category</option>
          <?php foreach ($categories as $c): ?><option <?= $old['category'] === $c ? 'selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="field"><label class="lbl" for="pPrice">Price (₹)</label><input id="pPrice" name="price" type="number" min="1" step="0.01" value="<?= e($old['price']) ?>"></div>
      <div class="field"><label class="lbl" for="pStock">Stock</label><input id="pStock" name="stock" type="number" min="0" value="<?= e($old['stock']) ?>"></div>
    </div>
    <div class="field"><label class="lbl" for="pImg">Image</label><input id="pImg" name="image" type="file" accept="image/*">
      <p class="muted small" style="margin-top:8px">JPG, PNG or WEBP. If left empty, a placeholder image is shown on the site.</p>
    </div>
    <div class="modal-actions">
      <a class="btn btn-ghost" href="products.php">Cancel</a>
      <button class="btn" type="submit">Add product</button>
    </div>
  </form>
</div>

<?php require __DIR__ . '/includes_footer.php'; ?>
