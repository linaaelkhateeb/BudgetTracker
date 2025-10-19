<?php
// config/db.php
$host = '127.0.0.1';
$db   = 'budgettracker';   // <= confirm this matches phpMyAdmin DB name exactly
$user = 'root';            // XAMPP default
$pass = '';                // XAMPP default is empty password
$dsn  = "mysql:host=$host;dbname=$db;charset=utf8mb4";

$options = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES   => false,
];

$pdo = new PDO($dsn, $user, $pass, $options);
