<?php
session_start();
require __DIR__ . '/config/app.php';
require __DIR__ . '/lib/csrf.php';

// flash errors/old input
$errors = $_SESSION['form_errors'] ?? [];
$old    = $_SESSION['form_old'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_old']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>BudgetTracker – Login</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= url('CSS/style.css') ?>">
</head>
<body>
  <div class="auth-wrap">
    <!-- Left -->
    <section class="auth-hero">
      <div class="brand">
        <div class="brand-badge parallax" aria-hidden="true">BT</div>
        <div class="brand-title parallax">BudgetTracker</div>
      </div>

      <h1 class="parallax">Track Your Finances Peacefully</h1>
      <p class="parallax">
      Organize your income, control expenses, and enjoy clarity with
      <strong>BudgetTracker</strong>.
      </p>
      <div class="hero-cards">
        <div class="hero-card"><h6>Income Logged</h6><div>+12.3% this month</div></div>
        <div class="hero-card"><h6>Balance</h6><div>AED 2,940</div></div>
        <div class="hero-card"><h6>Top Category</h6><div>Groceries</div></div>
      </div>
    </section>

    <!-- Right -->
    <section class="auth-panel">
      <div class="auth-card">
        <h2 class="mb-3">Welcome Back</h2>

        <?php if (isset($errors['general'])): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($errors['general']) ?></div>
        <?php endif; ?>

        <!-- Browser validation ON; posts to backend -->
        <form action="<?= url('auth/login.php') ?>" method="post" autocomplete="on">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES) ?>">

          <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input id="email" name="email" type="email"
              class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
              placeholder="you@example.com"
              required autocomplete="username" inputmode="email"
              pattern="^[^@\s]+@[^@\s]+\.com$"
              title="Enter a valid .com email (e.g. name@example.com)"
              value="<?= isset($old['email']) ? htmlspecialchars($old['email'], ENT_QUOTES) : '' ?>">
            <?php if (isset($errors['email'])): ?>
              <div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div>
            <?php endif; ?>
          </div>

          <div class="mb-2">
            <label class="form-label" for="password">Password</label>
            <input id="password" name="password" type="password"
              class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
              placeholder="••••••••"
              required minlength="8" autocomplete="current-password"
              pattern="^(?=.*[A-Za-z])(?=.*\d).{8,}$"
              title="At least 8 characters with a letter and a number">
            <?php if (isset($errors['password'])): ?>
              <div class="invalid-feedback"><?= htmlspecialchars($errors['password']) ?></div>
            <?php endif; ?>
          </div>

          <div class="form-check mb-3">
            <input type="checkbox" class="form-check-input" id="showPass" aria-controls="password">
            <label class="form-check-label" for="showPass">Show password</label>
          </div>

          <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="form-check">
              <input type="checkbox" class="form-check-input" id="remember" name="remember" autocomplete="on">
              <label for="remember" class="form-check-label">Remember me</label>
            </div>
            <a href="<?= url('forgetpassword.php') ?>">Forgot password?</a>
          </div>

          <button type="submit" class="btn btn-primary w-100 mb-3">
            <i class="fa-solid fa-right-to-bracket me-2"></i>Login
          </button>

          <div class="divider">or</div>
          <a href="<?= url('signup.php') ?>" class="btn btn-success w-100">
            <i class="fa-solid fa-user-plus me-2"></i>Create an account
          </a>
        </form>
      </div>
    </section>
  </div>

  <script src="<?= url('js/login.js') ?>"></script>
</body>
</html>
