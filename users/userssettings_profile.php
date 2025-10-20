<?php
// users/userssettings_profile.php
session_start();
require __DIR__ . '/../config/app.php';
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../lib/csrf.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: ' . url('login.php'));
  exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
  header('Location: ' . url('users/userssettings.php'));
  exit;
}

if (!csrf_check($_POST['csrf'] ?? null)) {
  http_response_code(400);
  echo 'Invalid CSRF token';
  exit;
}

$userId   = (int)$_SESSION['user_id'];
$name     = trim($_POST['name'] ?? '');
$email    = strtolower(trim($_POST['email'] ?? ''));
$currency = strtoupper(trim($_POST['currency'] ?? ''));

$errors = [];

if ($name === '' || mb_strlen($name) < 2) {
  $errors['name'] = 'Please enter your full name.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/\.com$/i', $email)) {
  $errors['email'] = 'Please enter a valid .com email.';
}
if ($currency !== '' && !preg_match('/^[A-Z]{3}$/', $currency)) {
  $errors['currency'] = 'Currency must be a 3-letter ISO code (e.g., USD).';
}

if ($errors) {
  $_SESSION['form_errors'] = $errors;
  header('Location: ' . url('users/userssettings.php'));
  exit;
}

try {
  // Ensure email unique (excluding current user)
  $chk = $pdo->prepare('SELECT id FROM users WHERE LOWER(email) = ? AND id <> ? LIMIT 1');
  $chk->execute([$email, $userId]);
  if ($chk->fetch()) {
    $_SESSION['form_errors'] = ['email' => 'This email is already in use by another account.'];
    header('Location: ' . url('users/userssettings.php'));
    exit;
  }

  // Update profile
  $up = $pdo->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
  $up->execute([$name, $email, $userId]);

  if ($currency !== '') {
    // Upsert preferences currency
    $pdo->beginTransaction();
    $sel = $pdo->prepare('SELECT user_id FROM preferences WHERE user_id = ?');
    $sel->execute([$userId]);
    if ($sel->fetch()) {
      $prefUp = $pdo->prepare('UPDATE preferences SET currency = ? WHERE user_id = ?');
      $prefUp->execute([$currency, $userId]);
    } else {
      $prefIns = $pdo->prepare('INSERT INTO preferences (user_id, currency) VALUES (?, ?)');
      $prefIns->execute([$userId, $currency]);
    }
    $pdo->commit();
  }

  $_SESSION['form_success'] = 'Profile updated successfully.';
  header('Location: ' . url('users/userssettings.php'));
  exit;

} catch (Throwable $e) {
  if ($pdo->inTransaction()) { $pdo->rollBack(); }
  $_SESSION['form_errors'] = ['general' => 'Could not update profile. Please try again.'];
  header('Location: ' . url('users/userssettings.php'));
  exit;
}
