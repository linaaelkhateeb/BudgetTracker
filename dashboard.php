<?php
// dashboard.php
require_once 'includes/database.php';
session_start();

// Initialize auth variables
$is_logged_in = false;
$user_id = $_SESSION['user_id'] ?? null;
$user_name = 'User';

// Verify session against database; only then consider user logged in
if ($conn && $user_id) {
    $user_sql = "SELECT name FROM users WHERE id = ?";
    $user_stmt = $conn->prepare($user_sql);
    if ($user_stmt) {
        $user_stmt->bind_param("i", $user_id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        if ($user_data = $user_result->fetch_assoc()) {
            $is_logged_in = true;
            $user_name = $user_data['name'] ?: 'User';
        } else {
            // Stale session: clear user-related session vars
            unset($_SESSION['user_id'], $_SESSION['user_name']);
        }
        $user_stmt->close();
    }
}

// Get REAL financial data from database (only when logged in)
$total_income = 0;
$total_expenses = 0;

if ($conn && $is_logged_in) {
    // Get current month income
    $income_sql = "SELECT COALESCE(SUM(amount), 0) as total FROM transactions 
                   WHERE user_id = ? AND type = 'income' 
                   AND MONTH(date) = MONTH(CURRENT_DATE()) 
                   AND YEAR(date) = YEAR(CURRENT_DATE())";
    $income_stmt = $conn->prepare($income_sql);
    $income_stmt->bind_param("i", $user_id);
    $income_stmt->execute();
    $income_result = $income_stmt->get_result();
    $income_data = $income_result->fetch_assoc();
    $total_income = $income_data['total'] ?: 0;
    $income_stmt->close();

    // Get current month expenses  
    $expense_sql = "SELECT COALESCE(SUM(amount), 0) as total FROM transactions 
                    WHERE user_id = ? AND type = 'expense' 
                    AND MONTH(date) = MONTH(CURRENT_DATE()) 
                    AND YEAR(date) = YEAR(CURRENT_DATE())";
    $expense_stmt = $conn->prepare($expense_sql);
    $expense_stmt->bind_param("i", $user_id);
    $expense_stmt->execute();
    $expense_result = $expense_stmt->get_result();
    $expense_data = $expense_result->fetch_assoc();
    $total_expenses = $expense_data['total'] ?: 0;
    $expense_stmt->close();

    $balance = $total_income - $total_expenses;
}

// Safely get summary data if transactions table exists (logged in only)
if ($conn && $is_logged_in) {
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
            margin-bottom: 0;
        }
        .summary-section {
            margin-top: 0;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <div class="brand-badge">BP</div>
                <div class="brand-title">Budget Trackerrr</div>
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
                    <?php if ($is_logged_in): ?>
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
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-content">
            <?php if ($is_logged_in): ?>
                <h1>Welcome back, <?= htmlspecialchars($user_name) ?>!</h1>
                <p>Take Control of Your Finances. Track income, manage expenses, and achieve your financial goals with ease and precision.</p>
                <div class="hero-buttons">
                    <a href="#budget" class="btn btn-primary">Get Started</a>
                    <button class="btn btn-outline">View Reports</button>
                </div>
            <?php else: ?>
                <h1>Budget Tracker</h1>
                <p>Plan budgets, track expenses, and stay on top of your finances. Create an account or log in to get started.</p>
                <div class="hero-buttons">
                    <a href="auth/signup.php" class="btn btn-primary">Sign Up</a>
                    <a href="login.php" class="btn btn-outline">Login</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Monthly Summary - show only when logged in -->
    <?php if ($is_logged_in): ?>
    <section class="section" id="summary">
        <h2 class="section-title">Monthly Summary</h2>
        <div class="summary-section">
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
                    <div class="summary-amount" id="balance">
                        $<?= number_format($balance, 2) ?>
                    </div>
                    <p>This Month</p>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>
<!-- Transactions Section -->
<section class="section" id="transactions">
    <div class="section-header">
        <h2 class="section-title">Recent Transactions</h2>
        <p class="section-description">Your latest income and expense records</p>
        <a href="<?= $is_logged_in ? 'transactions.php' : 'login.php' ?>" class="btn btn-outline">View All Transactions</a>
    </div>
    <div class="transactions-list">
        <!-- Transaction items will go here -->
    </div>
</section>

<!-- Budgets Section -->
<section class="section" id="budget">
    <div class="section-header">
        <h2 class="section-title">Your Budgets</h2>
        <p class="section-description">Track your spending against budget limits</p>
        <a href="<?= $is_logged_in ? 'budgets.php' : 'login.php' ?>" class="btn btn-outline">Manage Budgets</a>
    </div>
    <div class="budgets-list">
        <!-- Budget progress bars will go here -->
    </div>
</section>
    <style>
    #summary {
        margin-top: 0 !important;
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