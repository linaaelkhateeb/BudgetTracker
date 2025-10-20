<?php
// users/userssettings_categories.php — CRUD for categories
session_start();
require __DIR__ . '/../config/app.php';
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../lib/csrf.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: ' . url('login.php'));
  exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
  header('Location: ' . url('users/userssettings.php#categories'));
  exit;
}

if (!csrf_check($_POST['csrf'] ?? null)) {
  http_response_code(400);
  echo 'Invalid CSRF token';
  exit;
}

$userId = (int)$_SESSION['user_id'];
$action = $_POST['action'] ?? '';

// Detect if 'icon' column exists to keep compatibility if migration wasn't run yet
$hasIconColumn = false;
try {
  $chkCol = $pdo->query("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'categories' AND COLUMN_NAME = 'icon' LIMIT 1");
  $hasIconColumn = (bool)$chkCol->fetchColumn();
} catch (Throwable $e) {
  $hasIconColumn = false;
}

try {
  if ($action === 'create') {
    $name  = trim($_POST['name'] ?? '');
    $color = trim($_POST['color'] ?? '');
    $icon  = trim($_POST['icon'] ?? '');
    if ($name === '') {
      $_SESSION['form_errors'] = ['categories' => 'Name is required.'];
    } else {
      if ($color !== '' && !preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
        $color = null;
      }
      if ($icon === '') { $icon = null; }
      if ($hasIconColumn) {
        $ins = $pdo->prepare('INSERT INTO categories (user_id, name, color, icon) VALUES (?, ?, ?, ?)');
        $ins->execute([$userId, $name, $color, $icon]);
      } else {
        $ins = $pdo->prepare('INSERT INTO categories (user_id, name, color) VALUES (?, ?, ?)');
        $ins->execute([$userId, $name, $color]);
      }
      $_SESSION['form_success'] = 'Category added.';
    }
  } elseif ($action === 'update') {
    $id    = (int)($_POST['id'] ?? 0);
    $name      = trim($_POST['name'] ?? '');
    $hasColor  = array_key_exists('color', $_POST);
    $color     = $hasColor ? trim($_POST['color']) : null; // optional
    $hasIcon   = $hasIconColumn && array_key_exists('icon', $_POST);
    $icon      = $hasIcon ? trim($_POST['icon']) : null; // optional
    if ($id <= 0 || $name === '') {
      $_SESSION['form_errors'] = ['categories' => 'Invalid update request.'];
    } else {
      if ($hasColor) {
        if ($color !== '' && !preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) { $color = null; }
      }
      if ($hasIcon && $icon === '') { $icon = null; }

      $sets = ['name = ?'];
      $params = [$name];
      if ($hasColor) { $sets[] = 'color = ?'; $params[] = $color; }
      if ($hasIcon)  { $sets[] = 'icon = ?';  $params[] = $icon; }
      $params[] = $id; $params[] = $userId;
      $sql = 'UPDATE categories SET ' . implode(', ', $sets) . ' WHERE id = ? AND user_id = ?';
      $up = $pdo->prepare($sql);
      $up->execute($params);
      $_SESSION['form_success'] = 'Category updated.';
    }
  } elseif ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
      $del = $pdo->prepare('DELETE FROM categories WHERE id = ? AND user_id = ?');
      $del->execute([$id, $userId]);
      $_SESSION['form_success'] = 'Category deleted.';
    }
  }
} catch (Throwable $e) {
  $_SESSION['form_errors'] = ['categories' => 'Operation failed. Please try again.'];
}

header('Location: ' . url('users/userssettings.php#categories'));
exit;
