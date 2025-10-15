<?php
session_start();
if (empty($_SESSION['user_id'])) {
  header('Location: /login.php'); exit;
}
$name = $_SESSION['user_name'] ?: $_SESSION['user_email'];
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Dashboard</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body class="p-4">
  <h1>Welcome, <?= htmlspecialchars($name) ?> 👋</h1>
  <p>You’re logged in.</p>
  <p><a href="logout.php">Log out</a></p>
</body>
</html>
