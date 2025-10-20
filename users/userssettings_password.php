<?php
// users/userssettings_password.php — change password
session_start();
require __DIR__ . '/../config/app.php';
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../lib/csrf.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: ' . url('login.php'));
  exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
  header('Location: ' . url('users/userssettings.php#security'));
  exit;
}

if (!csrf_check($_POST['csrf'] ?? null)) {
  http_response_code(400);
  echo 'Invalid CSRF token';
  exit;
}

$userId = (int)$_SESSION['user_id'];
$current = $_POST['current_password'] ?? '';
$new     = $_POST['new_password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

$errors = [];
if ($new !== $confirm) {
  $errors['confirm_password'] = 'Passwords do not match.';
}
if (strlen($new) < 8 || !preg_match('/[A-Za-z]/', $new) || !preg_match('/\d/', $new)) {
  $errors['new_password'] = 'Password must be at least 8 characters and include a letter and a number.';
}

if ($errors) {
  $_SESSION['form_errors'] = $errors;
  header('Location: ' . url('users/userssettings.php#security'));
  exit;
}

try {
  $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ? LIMIT 1');
  $stmt->execute([$userId]);
  $row = $stmt->fetch();
  if (!$row || !password_verify($current, $row['password_hash'])) {
    $_SESSION['form_errors'] = ['current_password' => 'Your current password is incorrect.'];
    header('Location: ' . url('users/userssettings.php#security'));
    exit;
  }

  $hash = password_hash($new, PASSWORD_DEFAULT);
  $up = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
  $up->execute([$hash, $userId]);

  $_SESSION['form_success'] = 'Password updated successfully.';
  header('Location: ' . url('users/userssettings.php#security'));
  exit;

} catch (Throwable $e) {
  $_SESSION['form_errors'] = ['general' => 'Could not update password. Please try again.'];
  header('Location: ' . url('users/userssettings.php#security'));
  exit;
}
