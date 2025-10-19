<?php
require __DIR__ . '/config/db.php';

$email = 'admin@example.com';            // must end with .com to pass your rule
$pass  = 'Admin#1234';
$name  = 'Site Administrator';
$hash  = password_hash($pass, PASSWORD_DEFAULT);

// UPSERT admin (works on MySQL 8)
$sql = "INSERT INTO users (name, email, password_hash, role)
        VALUES (?, ?, ?, 'admin')
        ON DUPLICATE KEY UPDATE
          name = VALUES(name),
          password_hash = VALUES(password_hash),
          role = 'admin'";
$ok = $pdo->prepare($sql)->execute([$name, strtolower($email), $hash]);

echo $ok ? "Admin ready. Login with $email / $pass" : "Failed.";
