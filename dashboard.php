<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget Planner | Smart Finance Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="CSS/dashboard.css">
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
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-content">
            <h1>Take Control of Your Finances</h1>
            <p>Budget Planner helps you track income, manage expenses, and achieve your financial goals with ease and precision.</p>
            <div class="hero-buttons">
                <button class="btn btn-primary" id="get-started">Get Started</button>
                <button class="btn btn-outline">Learn More</button>
            </div>
        </div>
    </section>

    <!-- Monthly Summary -->
    <section class="section" id="summary">
        <h2 class="section-title">Monthly Summary</h2>
        <div class="summary-section fade-in">
            <div class="summary-grid">
                <div class="summary-item income">
                    <div class="summary-icon">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                    <h3>Total Income</h3>
                    <div class="summary-amount" id="total-income">$4,250.00</div>
                    <p>This Month</p>
                </div>
                <div class="summary-item expenses">
                    <div class="summary-icon">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                    <h3>Total Expenses</h3>
                    <div class="summary-amount" id="total-expenses">$2,840.00</div>
                    <p>This Month</p>
                </div>
                <div class="summary-item balance">
                    <div class="summary-icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <h3>Balance</h3>
                    <div class="summary-amount" id="balance">$1,410.00</div>
                    <p>This Month</p>
                </div>
            </div>
        </div>
    </section>
<script src="js/dashboard.js"></script>
</body>
</html>