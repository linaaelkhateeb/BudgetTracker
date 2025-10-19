<?php
// auth/reset.php — handle password reset submission
session_start();

require __DIR__ . '/../config/app.php';
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../lib/csrf.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
  header('Location: ' . url('reset.php'));
  exit;
}

if (!csrf_check($_POST['csrf'] ?? null)) {
  http_response_code(400);
  echo 'Invalid CSRF token';
  exit;
}

$email  = strtolower(trim($_POST['email'] ?? ''));
$token  = trim($_POST['token'] ?? '');
$pass   = $_POST['password'] ?? '';
$pass2  = $_POST['password_confirmation'] ?? '';

$errors = [];

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/\.com$/i', $email)) {
  $errors['email'] = 'Please enter a valid .com email.';
}
if ($token === '' || !preg_match('/^[a-f0-9]{64}$/i', $token)) {
  $errors['token'] = 'Invalid token.';
}
if (strlen($pass) < 8 || !preg_match('/[A-Za-z]/', $pass) || !preg_match('/\d/', $pass)) {
  $errors['password'] = 'Password must be at least 8 characters and include a letter and a number.';
}
if ($pass !== $pass2) {
  $errors['password_confirmation'] = 'Passwords do not match.';
}

if ($errors) {
  $_SESSION['form_errors'] = $errors;
  $_SESSION['form_old']    = ['email' => $email, 'token' => $token];
  header('Location: ' . url('reset.php'));
  exit;
}

try {
  // Verify token and expiry for this email
  $stmt = $pdo->prepare('SELECT id, reset_token, reset_expires FROM users WHERE LOWER(email) = ? LIMIT 1');
  $stmt->execute([$email]);
  $user = $stmt->fetch();

  $now = new DateTime('now');

  $valid = $user && is_string($user['reset_token']) && hash_equals($user['reset_token'], $token);
  if ($valid && $user['reset_expires']) {
    $exp = new DateTime($user['reset_expires']);
    if ($exp < $now) {
      $valid = false;
    }
  }

  if (!$valid) {
    $_SESSION['form_errors'] = ['general' => 'Invalid or expired token. Please request a new reset link.'];
    $_SESSION['form_old']    = ['email' => $email];
    header('Location: ' . url('reset.php'));
    exit;
  }

  // Update password and clear token
  $hash = password_hash($pass, PASSWORD_DEFAULT);
  $up = $pdo->prepare('UPDATE users SET password_hash = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?');
  $up->execute([$hash, (int)$user['id']]);

  // Auto-login user after reset
  session_regenerate_id(true);
  $_SESSION['user_id']    = (int)$user['id'];
  $_SESSION['user_email'] = $email;
  $_SESSION['user_name']  = $_SESSION['user_name'] ?? null; // optional, not fetched here
  $_SESSION['user_role']  = $_SESSION['user_role'] ?? 'user';

  header('Location: ' . url('dashboard.php'));
  exit;

} catch (Throwable $e) {
  $_SESSION['form_errors'] = ['general' => 'Something went wrong. Please try again.'];
  $_SESSION['form_old']    = ['email' => $email, 'token' => $token];
  header('Location: ' . url('reset.php'));
  exit;
}
