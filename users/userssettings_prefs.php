<?php
// users/userssettings_prefs.php — update preferences
session_start();
require __DIR__ . '/../config/app.php';
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../lib/csrf.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: ' . url('login.php'));
  exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
  header('Location: ' . url('users/userssettings.php#preferences'));
  exit;
}

if (!csrf_check($_POST['csrf'] ?? null)) {
  http_response_code(400);
  echo 'Invalid CSRF token';
  exit;
}

$userId = (int)$_SESSION['user_id'];
$dateFormat = trim($_POST['date_format'] ?? 'Y-m-d');
$weekStart  = (int)($_POST['week_start'] ?? 1);
$alerts     = (int)($_POST['alerts'] ?? 1);
$currency   = strtoupper(trim($_POST['currency'] ?? ''));

$validFormats = ['Y-m-d','d/m/Y','m/d/Y'];
if (!in_array($dateFormat, $validFormats, true)) {
  $dateFormat = 'Y-m-d';
}
if (!in_array($weekStart, [0,1], true)) {
  $weekStart = 1;
}
$alerts = $alerts ? 1 : 0;
if ($currency !== '' && !preg_match('/^[A-Z]{3}$/', $currency)) {
  $currency = '';
}

try {
  $pdo->beginTransaction();
  $sel = $pdo->prepare('SELECT user_id FROM preferences WHERE user_id = ?');
  $sel->execute([$userId]);
  if ($sel->fetch()) {
    if ($currency !== '') {
      $up = $pdo->prepare('UPDATE preferences SET date_format=?, week_start=?, alerts=?, currency=? WHERE user_id=?');
      $up->execute([$dateFormat, $weekStart, $alerts, $currency, $userId]);
    } else {
      $up = $pdo->prepare('UPDATE preferences SET date_format=?, week_start=?, alerts=? WHERE user_id=?');
      $up->execute([$dateFormat, $weekStart, $alerts, $userId]);
    }
  } else {
    if ($currency !== '') {
      $ins = $pdo->prepare('INSERT INTO preferences (user_id, date_format, week_start, alerts, currency) VALUES (?, ?, ?, ?, ?)');
      $ins->execute([$userId, $dateFormat, $weekStart, $alerts, $currency]);
    } else {
      $ins = $pdo->prepare('INSERT INTO preferences (user_id, date_format, week_start, alerts) VALUES (?, ?, ?, ?)');
      $ins->execute([$userId, $dateFormat, $weekStart, $alerts]);
    }
  }
  $pdo->commit();
  $_SESSION['form_success'] = 'Preferences saved.';
} catch (Throwable $e) {
  if ($pdo->inTransaction()) { $pdo->rollBack(); }
  $_SESSION['form_errors'] = ['preferences' => 'Could not save preferences.'];
}

header('Location: ' . url('users/userssettings.php#preferences'));
exit;
