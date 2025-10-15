<?php
// config/db.php
$DB_HOST = '127.0.0.1';
$DB_NAME = 'budgettracker';
$DB_USER = 'root';           // ← change if needed
$DB_PASS = '';               // ← change if needed

$dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";
$options = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES   => false,
];

$pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
