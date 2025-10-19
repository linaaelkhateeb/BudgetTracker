<?php
// dashboard.php
require_once 'includes/database.php';
session_start();

// Temporary mock session for development - REMOVE LATER
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['user_name'] = 'John Doe';
}

$user_id = $_SESSION['user_id'];

// Initialize variables with default values
$user_name = $_SESSION['user_name'] ?? 'User';
$total_income = 4250.00;
$total_expenses = 2840.00;
$balance = $total_income - $total_expenses;

// Safely get user data from database
if ($conn) {
    // Check if users table exists and get user data
    $user_sql = "SELECT name FROM users WHERE id = ?";
    $user_stmt = $conn->prepare($user_sql);
    
    if ($user_stmt) {
        $user_stmt->bind_param("i", $user_id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        if ($user_data = $user_result->fetch_assoc()) {
            $user_name = $user_data['name'];
        }
        $user_stmt->close();
    }
    
    // Try to get summary data if transactions table exists
    $summary_sql = "SHOW TABLES LIKE 'transactions'";
    $table_result = $conn->query($summary_sql);
    
    if ($table_result->num_rows > 0) {
        $summary_sql = "
            SELECT 
                COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as total_income,
                COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as total_expenses
            FROM transactions 
            WHERE user_id = ? 
            AND MONTH(date) = MONTH(CURRENT_DATE()) 
            AND YEAR(date) = YEAR(CURRENT_DATE())
        ";
        $summary_stmt = $conn->prepare($summary_sql);
        
        if ($summary_stmt) {
            $summary_stmt->bind_param("i", $user_id);
            $summary_stmt->execute();
            $summary_result = $summary_stmt->get_result();
            if ($summary_data = $summary_result->fetch_assoc()) {
                $total_income = $summary_data['total_income'] ?: 4250.00;
                $total_expenses = $summary_data['total_expenses'] ?: 2840.00;
                $balance = $total_income - $total_expenses;
            }
            $summary_stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget Planner | Smart Finance Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="CSS/dashboard.css">
    <style>
        .section {
            margin-bottom: 3rem;
        }
        .summary-section {
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <div class="brand-badge">BP</div>
                <div class="brand-title">Budget Planner</div>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#summary">Summary</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#transactions">Transactions</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#budget">Budget</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#actions">Quick Actions</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?= htmlspecialchars($user_name) ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-content">
            <h1>Welcome back, <?= htmlspecialchars($user_name) ?>!</h1>
            <p>Take Control of Your Finances. Track income, manage expenses, and achieve your financial goals with ease and precision.</p>
            <div class="hero-buttons">
                <button class="btn btn-primary" id="get-started">Add Transaction</button>
                <button class="btn btn-outline">View Reports</button>
            </div>
        </div>
    </section>

    <!-- Monthly Summary - MOVED RIGHT AFTER HERO -->
    <section class="section" id="summary">
        <h2 class="section-title">Monthly Summary</h2>
        <div class="summary-section fade-in">
            <div class="summary-grid">
                <div class="summary-item income">
                    <div class="summary-icon">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                    <h3>Total Income</h3>
                    <div class="summary-amount" id="total-income">$<?= number_format($total_income, 2) ?></div>
                    <p>This Month</p>
                </div>
                <div class="summary-item expenses">
                    <div class="summary-icon">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                    <h3>Total Expenses</h3>
                    <div class="summary-amount" id="total-expenses">$<?= number_format($total_expenses, 2) ?></div>
                    <p>This Month</p>
                </div>
                <div class="summary-item balance">
                    <div class="summary-icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <h3>Balance</h3>
                    <div class="summary-amount" id="balance" style="color: <?= $balance >= 0 ? '#2ecc71' : '#e74c3c' ?>">
                        $<?= number_format($balance, 2) ?>
                    </div>
                    <p>This Month</p>
                </div>
            </div>
        </div>
    </section>
<!-- Transactions Section -->
<section class="section" id="transactions">
    <div class="section-header">
        <h2 class="section-title">Recent Transactions</h2>
        <p class="section-description">Your latest income and expense records</p>
        <a href="transactions.php" class="btn btn-outline">View All Transactions</a>
    </div>
    <div class="transactions-list">
        <!-- Transaction items will go here -->
    </div>
</section>

<!-- Budgets Section -->
<section class="section" id="budgets">
    <div class="section-header">
        <h2 class="section-title">Your Budgets</h2>
        <p class="section-description">Track your spending against budget limits</p>
        <a href="budgets.php" class="btn btn-outline">Manage Budgets</a>
    </div>
    <div class="budgets-list">
        <!-- Budget progress bars will go here -->
    </div>
</section>
    <style>
    #summary {
        margin-top: -0px !important;
        transform: translateY(-50px);
    }
    .section-header {
    text-align: center;
    margin-bottom: 2rem;
}

.section-description {
    color: #666;
    margin-bottom: 1rem;
}
</style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/dashboard.js"></script>
</body>
</html>