<?php
session_start();
require __DIR__ . '/config/app.php';
require __DIR__ . '/lib/csrf.php';

$errors  = $_SESSION['form_errors'] ?? [];
$success = $_SESSION['form_success'] ?? null;
$old     = $_SESSION['form_old'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_success'], $_SESSION['form_old']);

$prefillEmail = isset($_GET['email']) ? strtolower(trim($_GET['email'])) : ($old['email'] ?? '');
$prefillToken = isset($_GET['token']) ? $_GET['token'] : ($old['token'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>BudgetTracker – Reset Password</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= url('CSS/style.css') ?>">
</head>
<body>
  <div class="auth-wrap">
    <section class="auth-hero">
      <div class="brand">
        <div class="brand-badge">BT</div>
        <div class="brand-title">BudgetTracker</div>
      </div>
      <h1>Choose a new password</h1>
      <p>Enter your email, the reset token you received, and your new password.</p>
    </section>

    <section class="auth-panel">
      <div class="auth-card">
        <h2 class="mb-3">Reset Password</h2>

        <?php if ($success): ?>
          <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if (isset($errors['general'])): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($errors['general']) ?></div>
        <?php endif; ?>

        <form action="<?= url('auth/reset.php') ?>" method="post" novalidate>
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES) ?>">

          <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input id="email" name="email" type="email"
                   class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                   value="<?= htmlspecialchars($prefillEmail, ENT_QUOTES) ?>" required>
            <?php if (isset($errors['email'])): ?>
              <div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div>
            <?php endif; ?>
          </div>

          <div class="mb-3">
            <label class="form-label" for="token">Reset token</label>
            <input id="token" name="token" type="text"
                   class="form-control <?= isset($errors['token']) ? 'is-invalid' : '' ?>"
                   value="<?= htmlspecialchars($prefillToken, ENT_QUOTES) ?>" required>
            <?php if (isset($errors['token'])): ?>
              <div class="invalid-feedback"><?= htmlspecialchars($errors['token']) ?></div>
            <?php endif; ?>
          </div>

          <div class="mb-3">
            <label class="form-label" for="password">New password</label>
            <input id="password" name="password" type="password"
                   class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" required>
            <?php if (isset($errors['password'])): ?>
              <div class="invalid-feedback"><?= htmlspecialchars($errors['password']) ?></div>
            <?php endif; ?>
          </div>

          <div class="mb-3">
            <label class="form-label" for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password"
                   class="form-control <?= isset($errors['password_confirmation']) ? 'is-invalid' : '' ?>" required>
            <?php if (isset($errors['password_confirmation'])): ?>
              <div class="invalid-feedback"><?= htmlspecialchars($errors['password_confirmation']) ?></div>
            <?php endif; ?>
          </div>

          <button type="submit" class="btn btn-primary w-100">
            <i class="fa-solid fa-key me-2"></i>Update password
          </button>

          <div class="divider">or</div>
          <a class="btn btn-success w-100" href="<?= url('login.php') ?>">
            <i class="fa-solid fa-arrow-left me-2"></i>Back to login
          </a>
        </form>
      </div>
    </section>
  </div>
</body>
</html>
