<?php
// auth/logout.php — end session and redirect to login
session_start();

// Unset all session variables
$_SESSION = [];

// Destroy the session cookie if present
if (ini_get('session.use_cookies')) {
  $params = session_get_cookie_params();
  setcookie(session_name(), '', time() - 42000,
    $params['path'], $params['domain'], $params['secure'], $params['httponly']
  );
}

// Destroy the session
session_destroy();

// Simple redirect back to login
require __DIR__ . '/../config/app.php';
header('Location: ' . url('login.php'));
exit;

