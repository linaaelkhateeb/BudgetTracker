<?php
require __DIR__ . '/config/db.php';  // must define $pdo

$email = 'test@example.com';         // must end with .com to pass validation
$plain = 'Testpass123';              // >=8 chars, letters + numbers
$name  = 'Test User';

$hash = password_hash($plain, PASSWORD_DEFAULT);

$stmt = $pdo->prepare(
  "INSERT INTO users (email, password_hash, name)
   VALUES (?, ?, ?)
   ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), name = VALUES(name)"
);
$stmt->execute([strtolower($email), $hash, $name]);

echo "✅ Seeded user: $email / $plain";
