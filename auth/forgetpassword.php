<?php
// auth/forgetpassword.php — handles reset token generation
session_start();

require __DIR__ . '/../config/app.php';
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../lib/csrf.php';

// POST only
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
  header('Location: ' . url('forgetpassword.php'));
  exit;
}

// CSRF
if (!csrf_check($_POST['csrf'] ?? null)) {
  http_response_code(400);
  echo 'Invalid CSRF token';
  exit;
}

$email = strtolower(trim($_POST['email'] ?? ''));
$errors = [];

// Validate email (.com rule to mirror signup/login behavior)
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/\.com$/i', $email)) {
  $errors['email'] = 'Please enter a valid .com email.';
}

if ($errors) {
  $_SESSION['form_errors'] = $errors;
  $_SESSION['form_old']    = ['email' => $email];
  header('Location: ' . url('forgetpassword.php'));
  exit;
}

try {
  // Look up user (no user enumeration in response)
  $stmt = $pdo->prepare('SELECT id FROM users WHERE LOWER(email) = ? LIMIT 1');
  $stmt->execute([$email]);
  $user = $stmt->fetch();

  if ($user && isset($user['id'])) {
    // Generate secure token and expiry (1 hour)
    $token   = bin2hex(random_bytes(32));
    $expires = (new DateTime('+1 hour'))->format('Y-m-d H:i:s');

    $up = $pdo->prepare('UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?');
    $up->execute([$token, $expires, (int)$user['id']]);

    // In a real app, send email containing link: url('reset.php?token=...&email=...')
    // For now, we do not expose token to the UI to avoid leaks.
  }

  // Always show success message regardless of whether the email exists
  $_SESSION['form_success'] = 'If that email exists in our system, you will receive reset instructions shortly.';
  header('Location: ' . url('forgetpassword.php'));
  exit;

} catch (Throwable $e) {
  $_SESSION['form_errors'] = ['general' => 'Something went wrong. Please try again.'];
  $_SESSION['form_old']    = ['email' => $email];
  header('Location: ' . url('forgetpassword.php'));
  exit;
}

