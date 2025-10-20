<?php
require_once 'includes/database.php';

// Test if users table exists
$result = $conn->query("SHOW TABLES LIKE 'users'");
if ($result->num_rows > 0) {
    echo "✅ Database connected and users table exists!";
} else {
    echo "❌ Users table not found - check the SQL import";
}
?>