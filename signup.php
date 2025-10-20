<?php
session_start();
require __DIR__ . '/config/app.php';
require __DIR__ . '/lib/csrf.php';

// flash errors/old input (same pattern as login.php)
$errors = $_SESSION['form_errors'] ?? [];
$old    = $_SESSION['form_old'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_old']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>BudgetTracker – Create Account</title>
  <!-- Match login.php CSS stack for consistent fonts/forms -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= url('CSS/signup.css') ?>">
</head>
<body class="signup-page">

  <div class="auth-wrap">
    <!-- LEFT — matches login hero (now with parallax) -->
    <section class="auth-hero">
      <div class="brand parallax">
        <div class="brand-badge">BT</div>
        <div class="brand-title">BudgetTracker</div>
      </div>

      <h1 class="parallax">Track Your Finances Peacefully</h1>
      <p class="parallax">
        Organize your income, control expenses, and enjoy clarity with
        <strong>BudgetTracker</strong>.
      </p>

      <div class="hero-cards">
        <div class="hero-card">
          <h6>Income Logged</h6>
          <div>+12.3% this month</div>
        </div>
        <div class="hero-card">
          <h6>Balance</h6>
          <div>AED 2,940</div>
        </div>
        <div class="hero-card">
          <h6>Top Category</h6>
          <div>Groceries</div>
        </div>
      </div>
    </section>

    <!-- RIGHT — Signup card -->
    <section class="auth-panel">
      <div class="auth-card">
        <h2>Create Account</h2>

        <?php if (!empty($errors['general'])): ?>
          <div class="text-danger"><?= htmlspecialchars($errors['general']) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= url('auth/signup.php') ?>" novalidate>
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">

          <div class="mb-3">
            <label for="name" class="form-label">Full name</label>
            <input type="text" id="name" name="name" class="form-control"
                   value="<?= htmlspecialchars($old['name'] ?? '') ?>" required>
            <?php if (!empty($errors['name'])): ?>
              <div class="text-danger"><?= htmlspecialchars($errors['name']) ?></div>
            <?php endif; ?>
          </div>

          <div class="mb-3">
            <label for="email" class="form-label">Email (.com only)</label>
            <input type="email" id="email" name="email" class="form-control"
                   value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
            <?php if (!empty($errors['email'])): ?>
              <div class="text-danger"><?= htmlspecialchars($errors['email']) ?></div>
            <?php endif; ?>
          </div>

          <div class="mb-3">
            <label for="password" class="form-label">Password (min 8, letter + number)</label>
            <input type="password" id="password" name="password" class="form-control" required>
            <?php if (!empty($errors['password'])): ?>
              <div class="text-danger"><?= htmlspecialchars($errors['password']) ?></div>
            <?php endif; ?>
          </div>

          <div class="mb-2">
            <label for="password_confirmation" class="form-label">Confirm password</label>
            <input type="password" id="password_confirmation" name="password_confirmation"
                   class="form-control" required>
            <?php if (!empty($errors['password_confirmation'])): ?>
              <div class="text-danger"><?= htmlspecialchars($errors['password_confirmation']) ?></div>
            <?php endif; ?>
          </div>

          <!-- Stacked checkboxes (under password fields) -->
          <div class="helper-row">
            <label class="form-check"><input type="checkbox" id="showPass"> Show password</label>
            <label class="form-check"><input type="checkbox" id="showPass2"> Show confirm password</label>
          </div>

          <button type="submit" class="btn btn-primary w-100" style="margin-top:12px;">Create account</button>

          <div class="divider"><span>or</span></div>

          <a href="<?= url('login.php') ?>" class="btn btn-success w-100">Back to login</a>
        </form>
      </div>
    </section>
  </div>

  <script src="<?= url('js/signup.js') ?>"></script>
</body>
</html>
