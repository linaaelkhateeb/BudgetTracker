<?php
// auth/login.php
session_start();

require __DIR__ . '/../config/app.php';
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../lib/csrf.php';

// Ensure POST request only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: ' . url('login.php'));
  exit;
}

// Validate CSRF token
if (!csrf_check($_POST['csrf'] ?? null)) {
  http_response_code(400);
  echo 'Invalid CSRF token';
  exit;
}

$email = strtolower(trim($_POST['email'] ?? ''));
$pass  = $_POST['password'] ?? '';

$errors = [];

// Validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/\.com$/i', $email)) {
  $errors['email'] = 'Please enter a valid .com email.';
}
if (strlen($pass) < 8 || !preg_match('/[A-Za-z]/', $pass) || !preg_match('/\d/', $pass)) {
  $errors['password'] = 'Password must be at least 8 characters and include a letter and a number.';
}

if ($errors) {
  $_SESSION['form_errors'] = $errors;
  $_SESSION['form_old']    = ['email' => $email];
  header('Location: ' . url('login.php'));
  exit;
}

// Look up user
$stmt = $pdo->prepare('SELECT id, email, password_hash, name, role FROM users WHERE LOWER(email) = ? LIMIT 1');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !is_string($user['password_hash']) || !password_verify($pass, $user['password_hash'])) {
  $_SESSION['form_errors'] = ['general' => 'Invalid email or password.'];
  $_SESSION['form_old']    = ['email' => $email];
  header('Location: ' . url('login.php'));
  exit;
}

// Successful login
session_regenerate_id(true);
$_SESSION['user_id']    = (int)$user['id'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_name']  = $user['name'] ?? null;
$_SESSION['user_role']  = $user['role'] ?? 'user';

// Rehash old passwords automatically if needed
if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
  $newHash = password_hash($pass, PASSWORD_DEFAULT);
  $up = $pdo->prepare('UPDATE users SET password_hash=? WHERE id=?');
  $up->execute([$newHash, $user['id']]);
}

// ✅ Redirect both admin and user to the same dashboard
header('Location: ' . url('dashboard.php'));
exit;
