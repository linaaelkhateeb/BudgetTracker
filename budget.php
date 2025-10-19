<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budgets - BudgetWise</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <a href="#" class="logo">BudgetWise</a>
            <div class="user-menu">
                <span>Welcome, User!</span>
                <button class="btn" style="background:#3498db; color:white;">Logout</button>
            </div>
        </div>
    </header>

    <!-- Main Layout -->
    <div class="app-container">
        <!-- Sidebar -->
        <nav class="sidebar">
            <ul class="sidebar-nav">
                <li><a href="#">📊 Dashboard</a></li>
                <li><a href="#">💳 Transactions</a></li>
                <li><a href="#" class="active">💰 Budgets</a></li>
                <li><a href="#">📈 Reports</a></li>
                <li><a href="#">⚙️ Settings</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">My Budgets</h1>
                <button class="btn btn-success" onclick="openModal()">
                    ＋ Create New Budget
                </button>
            </div>

            <!-- Budgets Grid -->
            <div class="budgets-grid">
                <!-- Budget Card 1 -->
                <div class="budget-card">
                    <div class="budget-header">
                        <h3 class="budget-category">Groceries</h3>
                        <span class="budget-amount">$400 / $500</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 80%"></div>
                    </div>
                    <div class="budget-details">
                        <span>Spent: $400</span>
                        <span>Remaining: $100</span>
                    </div>
                    <div class="budget-actions">
                        <button class="btn" style="background:#3498db; color:white;">Edit</button>
                        <button class="btn" style="background:#e74c3c; color:white;">Delete</button>
                    </div>
                </div>

                <!-- Budget Card 2 -->
                <div class="budget-card">
                    <div class="budget-header">
                        <h3 class="budget-category">Entertainment</h3>
                        <span class="budget-amount">$150 / $200</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 75%"></div>
                    </div>
                    <div class="budget-details">
                        <span>Spent: $150</span>
                        <span>Remaining: $50</span>
                    </div>
                    <div class="budget-actions">
                        <button class="btn" style="background:#3498db; color:white;">Edit</button>
                        <button class="btn" style="background:#e74c3c; color:white;">Delete</button>
                    </div>
                </div>

                <!-- Budget Card 3 -->
                <div class="budget-card">
                    <div class="budget-header">
                        <h3 class="budget-category">Transportation</h3>
                        <span class="budget-amount">$250 / $200</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 125%; background:#e74c3c"></div>
                    </div>
                    <div class="budget-details">
                        <span>Spent: $250</span>
                        <span style="color:#e74c3c">Over: $50</span>
                    </div>
                    <div class="budget-actions">
                        <button class="btn" style="background:#3498db; color:white;">Edit</button>
                        <button class="btn" style="background:#e74c3c; color:white;">Delete</button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function openModal() {
            alert('Create New Budget button clicked!');
        }
    </script>
</body>
</html>