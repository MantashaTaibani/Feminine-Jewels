<?php
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    redirect(is_admin() ? 'admin/dashboard.php' : 'dashboard.php');
}

$error = null;
$old_email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        $error = 'Your session expired. Please try again.';
    } else {
        $email    = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $old_email = $email;

        $st = db()->prepare('SELECT id, name, email, password, role FROM users WHERE email = ?');
        $st->execute([$email]);
        $user = $st->fetch();

        if ($user && password_verify($password, $user['password'])) {
            login_user($user);
            // Redirect according to role. Never send the user to a page that doesn't exist.
            if ($user['role'] === 'admin') {
                redirect('admin/dashboard.php');
            }
            redirect('dashboard.php');
        }

        // Do NOT reveal whether the email or the password was wrong.
        $error = 'Invalid email or password';
    }
}

$flash = pull_flash();
$page_title = 'Sign In — ' . SITE_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($page_title) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,500&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/login.css">
</head>
<body>

  <div class="card">
    <div class="brand-panel">
      <div>
        <div class="brand-name">Feminine Jewells</div>
        <div class="brand-tag">Fashion &amp; Fine Imitation Jewellery</div>
      </div>
      <p class="brand-quote">"Every piece tells a story worth wearing."</p>
      <svg class="gem-deco" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
        <polygon points="100,10 180,70 150,190 50,190 20,70" fill="none" stroke="#fdf8f4" stroke-width="2"/>
        <polygon points="100,10 180,70 100,100" fill="#fdf8f4" opacity="0.25"/>
        <polygon points="20,70 100,10 100,100" fill="#fdf8f4" opacity="0.15"/>
        <polygon points="100,100 150,190 50,190" fill="#fdf8f4" opacity="0.1"/>
      </svg>
      <div class="sparkle"></div>
      <div class="sparkle"></div>
      <div class="sparkle"></div>
    </div>

    <div class="form-panel">
      <h1>Welcome back</h1>
      <p class="subtitle">Sign in to continue exploring your favourite pieces.</p>

      <?php if ($flash): ?>
        <p style="background:#e3f1e6;color:#4f7f5b;padding:12px 16px;border-radius:14px;font-size:.9rem;margin-bottom:18px"><?= e($flash['message']) ?></p>
      <?php endif; ?>
      <?php if ($error): ?>
        <p style="background:#f8dcdc;color:#a5464a;padding:12px 16px;border-radius:14px;font-size:.9rem;margin-bottom:18px"><?= e($error) ?></p>
      <?php endif; ?>

      <form action="login.php" method="POST">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

        <div class="field">
          <label for="email">Email</label>
          <input type="text" id="email" name="email" placeholder="you@example.com" value="<?= e($old_email) ?>" required>
        </div>

        <div class="field">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="Enter your password" required>
        </div>

        <div class="row-between">
          <label class="remember">
            <input type="checkbox" name="remember">
            Remember me
          </label>
          <a href="#" class="forgot-link">Forgot password?</a>
        </div>

        <button type="submit" class="btn-primary">Sign In</button>
      </form>

      <div class="divider">New here?</div>

      <p class="signup-note">
        Don't have an account? <a href="signup.php">Create one</a>
      </p>
    </div>

  </div>

</body>
</html>
