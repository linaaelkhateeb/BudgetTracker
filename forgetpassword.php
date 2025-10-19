<?php
session_start();
require __DIR__ . '/config/app.php';
require __DIR__ . '/lib/csrf.php';

// Flash messages
$errors  = $_SESSION['form_errors'] ?? [];
$success = $_SESSION['form_success'] ?? null;
$old     = $_SESSION['form_old'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_success'], $_SESSION['form_old']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>BudgetTracker – Forgot Password</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= url('CSS/style.css') ?>">
</head>
<body>
  <div class="auth-wrap">
    <!-- Left hero (reuse login visual) -->
    <section class="auth-hero">
      <div class="brand">
        <div class="brand-badge">BT</div>
        <div class="brand-title">BudgetTracker</div>
      </div>
      <h1>Reset your password</h1>
      <p>Enter your account email. If it exists, we'll send password reset instructions.</p>
    </section>

    <!-- Right panel -->
    <section class="auth-panel">
      <div class="auth-card">
        <h2 class="mb-3">Forgot Password</h2>

        <?php if ($success): ?>
          <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if (isset($errors['general'])): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($errors['general']) ?></div>
        <?php endif; ?>

        <form action="<?= url('auth/forgetpassword.php') ?>" method="post">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES) ?>">

          <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input id="email" name="email" type="email"
                   class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                   placeholder="you@example.com"
                   required inputmode="email" autocomplete="email"
                   value="<?= isset($old['email']) ? htmlspecialchars($old['email'], ENT_QUOTES) : '' ?>">
            <?php if (isset($errors['email'])): ?>
              <div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div>
            <?php endif; ?>
          </div>

          <button type="submit" class="btn btn-primary w-100">
            <i class="fa-solid fa-paper-plane me-2"></i>Send reset link
          </button>

          <div class="divider">or</div>
          <a href="<?= url('login.php') ?>" class="btn btn-success w-100">
            <i class="fa-solid fa-arrow-left me-2"></i>Back to login
          </a>

          <div class="mt-2 text-center">
            <a href="<?= url('reset.php') ?>">Already have a token? Reset your password</a>
          </div>
        </form>
      </div>
    </section>
  </div>
</body>
</html>

