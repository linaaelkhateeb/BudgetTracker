<?php
// auth/login.php
session_start();

require __DIR__ . '/../config/db.php';  // must define $pdo (PDO instance)
require __DIR__ . '/../lib/csrf.php';

// Only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: ../login.php'); // relative, not root
  exit;
}

// CSRF
if (!csrf_check($_POST['csrf'] ?? null)) {
  $_SESSION['form_errors'] = ['general' => 'Invalid form token, please try again.'];
  header('Location: ../login.php');
  exit;
}

// Inputs
$email = trim($_POST['email'] ?? '');
$pass  = $_POST['password'] ?? '';

$errors = [];

// Server-side validation (mirrors front-end)
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/\.com$/i', $email)) {
  $errors['email'] = 'Please enter a valid .com email.';
}
if (strlen($pass) < 8 || !preg_match('/[A-Za-z]/', $pass) || !preg_match('/\d/', $pass)) {
  $errors['password'] = 'Password must be at least 8 characters and include a letter and a number.';
}

if ($errors) {
  $_SESSION['form_errors'] = $errors;
  $_SESSION['form_old']    = ['email' => $email];
  header('Location: ../login.php'); // relative!
  exit;
}

// DB lookup
$stmt = $pdo->prepare('SELECT id, email, password_hash, name FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($pass, $user['password_hash'])) {
  $_SESSION['form_errors'] = ['general' => 'Invalid email or password.'];
  $_SESSION['form_old']    = ['email' => $email];
  header('Location: ../login.php');
  exit;
}

// Success
session_regenerate_id(true);
$_SESSION['user_id']    = (int)$user['id'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_name']  = $user['name'] ?? null;

// Optional: remember me cookie (implement securely if you enable it)

// Go to dashboard (relative path keeps you inside the subfolder)
header('Location: ../dashboard.php');
exit;
