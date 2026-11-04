<?php
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    redirect(is_admin() ? 'admin/dashboard.php' : 'dashboard.php');
}

$errors = [];
$old = ['name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        $errors[] = 'Your session expired. Please try again.';
    }

    $name     = trim((string)($_POST['name'] ?? ''));
    $email    = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $confirm  = (string)($_POST['confirm_password'] ?? '');
    $old      = ['name' => $name, 'email' => $email];

    // 1. Validate name
    if (strlen($name) < 3) {
        $errors[] = 'Please enter your full name (at least 3 characters).';
    }
    // 2. Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    // 3. Validate password
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    // 4. Check if email already exists
    if (!$errors) {
        $st = db()->prepare('SELECT id FROM users WHERE email = ?');
        $st->execute([$email]);
        if ($st->fetch()) {
            $errors[] = 'An account with that email already exists. Please log in instead.';
        }
    }

    if (!$errors) {
        // 5. Hash password + 6. Insert user
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $st = db()->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
        $st->execute([$name, $email, $hash, 'customer']);

        // 7 & 8. Success message + redirect to login
        flash('ok', 'Account created successfully! Please sign in.');
        redirect('login.php');
    }
}

$page_title = 'Create your account — ' . SITE_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($page_title) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,500&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="is-auth">

<div class="auth-wrap">
  <div class="auth-card">
    <div class="auth-brand">
      <div>
        <div class="brand-name">Feminine Jewells</div>
        <div class="brand-tag">Fashion &amp; Fine Imitation Jewellery</div>
      </div>
      <p class="brand-quote">"Every piece tells a story worth wearing."</p>
      <svg class="gem" viewBox="0 0 200 200" aria-hidden="true"><polygon points="100,10 180,70 150,190 50,190 20,70" fill="none" stroke="#fdf8f4" stroke-width="2"/><polygon points="100,10 180,70 100,100" fill="#fdf8f4" opacity=".25"/><polygon points="20,70 100,10 100,100" fill="#fdf8f4" opacity=".15"/><polygon points="100,100 150,190 50,190" fill="#fdf8f4" opacity=".1"/></svg>
      <span class="sp"></span><span class="sp"></span><span class="sp"></span>
    </div>
    <div class="auth-form">
      <h1>Create your account</h1>
      <p class="subtitle">Join us to save your favourites and track your orders.</p>

      <?php if ($errors): ?>
        <div class="toast bad show" style="position:static;max-width:none;transform:none;opacity:1;margin-bottom:18px">
          <?php foreach ($errors as $err) echo e($err) . '<br>'; ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="signup.php" novalidate>
        <?= csrf_field() ?>
        <div class="field"><label class="lbl" for="sName">Full name</label><input id="sName" name="name" placeholder="Your full name" value="<?= e($old['name']) ?>" required></div>
        <div class="field"><label class="lbl" for="sEmail">Email</label><input id="sEmail" name="email" type="email" placeholder="you@example.com" value="<?= e($old['email']) ?>" required></div>
        <div class="field"><label class="lbl" for="sPass">Password</label><input id="sPass" name="password" type="password" placeholder="At least 8 characters" required></div>
        <div class="field"><label class="lbl" for="sPass2">Confirm password</label><input id="sPass2" name="confirm_password" type="password" placeholder="Re-enter your password" required></div>
        <button class="btn btn-block" type="submit">Create Account</button>
      </form>
      <p class="auth-note">Already have an account? <a href="login.php">Sign in</a></p>
    </div>
  </div>
</div>

</body>
</html>
