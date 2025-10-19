<?php
// auth/signup.php
session_start();

require __DIR__ . '/../config/app.php';
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../lib/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: ' . url('signup.php')); exit;
}
if (!csrf_check($_POST['csrf'] ?? null)) {
  http_response_code(400);
  echo 'Invalid CSRF token'; exit;
}

$name   = trim($_POST['name'] ?? '');
$email  = strtolower(trim($_POST['email'] ?? ''));
$pass   = $_POST['password'] ?? '';
$pass2  = $_POST['password_confirmation'] ?? '';

$errors = [];

// Basic validations (match your login rules)
if ($name === '' || mb_strlen($name) < 2) {
  $errors['name'] = 'Please enter your full name.';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/\.com$/i', $email)) {
  $errors['email'] = 'Please enter a valid .com email.';
}

if (strlen($pass) < 8 || !preg_match('/[A-Za-z]/', $pass) || !preg_match('/\d/', $pass)) {
  $errors['password'] = 'Password must be at least 8 characters and include a letter and a number.';
}

if ($pass !== $pass2) {
  $errors['password_confirmation'] = 'Passwords do not match.';
}

if ($errors) {
  $_SESSION['form_errors'] = $errors;
  $_SESSION['form_old']    = ['name' => $name, 'email' => $email];
  header('Location: ' . url('signup.php')); exit;
}

// Ensure email uniqueness
try {
  // Check if user exists
  $check = $pdo->prepare('SELECT id FROM users WHERE LOWER(email) = ? LIMIT 1');
  $check->execute([$email]);
  if ($check->fetch()) {
    $_SESSION['form_errors'] = ['email' => 'This email is already registered. Please log in.'];
    $_SESSION['form_old']    = ['name' => $name, 'email' => $email];
    header('Location: ' . url('signup.php')); exit;
  }

  // Create user
  $hash = password_hash($pass, PASSWORD_DEFAULT);
  $ins  = $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, "user")');
  $ins->execute([$name, $email, $hash]);

  // Auto-login (same session keys as login)
  $userId = (int)$pdo->lastInsertId();
  session_regenerate_id(true);
  $_SESSION['user_id']    = $userId;
  $_SESSION['user_email'] = $email;
  $_SESSION['user_name']  = $name;
  $_SESSION['user_role']  = 'user';

  header('Location: ' . url('dashboard.php'));
  exit;

} catch (Throwable $e) {
  // Optional: log $e->getMessage() in development
  $_SESSION['form_errors'] = ['general' => 'Something went wrong. Please try again.'];
  $_SESSION['form_old']    = ['name' => $name, 'email' => $email];
  header('Location: ' . url('signup.php')); exit;
}
