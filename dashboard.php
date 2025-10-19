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
<script src="js/dashboard.js"></script>
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

    <!-- Recent Transactions -->
    <section class="transactions-section" id="transactions">
        <h2 class="section-title">Recent Transactions</h2>
        <div class="transactions-container fade-in">
            <div class="transaction-item transaction-income">
                <div class="transaction-details">
                    <div class="transaction-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="transaction-info">
                        <h4>Monthly Salary</h4>
                        <p>Salary • June 15, 2023</p>
                    </div>
                </div>
                <div class="transaction-amount">+$1,200.00</div>
            </div>
            <div class="transaction-item transaction-expense">
                <div class="transaction-details">
                    <div class="transaction-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <div class="transaction-info">
                        <h4>Rent Payment</h4>
                        <p>Housing • June 10, 2023</p>
                    </div>
                </div>
                <div class="transaction-amount">-$350.00</div>
            </div>
            <div class="transaction-item transaction-expense">
                <div class="transaction-details">
                    <div class="transaction-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <div class="transaction-info">
                        <h4>Electricity Bill</h4>
                        <p>Utilities • June 8, 2023</p>
                    </div>
                </div>
                <div class="transaction-amount">-$120.00</div>
            </div>
            <div class="transaction-item transaction-expense">
                <div class="transaction-details">
                    <div class="transaction-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="transaction-info">
                        <h4>Grocery Shopping</h4>
                        <p>Food • June 5, 2023</p>
                    </div>
                </div>
                <div class="transaction-amount">-$85.00</div>
            </div>
            <div class="transaction-item transaction-income">
                <div class="transaction-details">
                    <div class="transaction-icon">
                        <i class="fas fa-laptop"></i>
                    </div>
                    <div class="transaction-info">
                        <h4>Web Design Project</h4>
                        <p>Freelance • June 3, 2023</p>
                    </div>
                </div>
                <div class="transaction-amount">+$300.00</div>
            </div>
        </div>
    </section>

    <!-- Budget Progress -->
    <section class="section" id="budget">
        <h2 class="section-title">Budget Progress</h2>
        <div class="budget-section fade-in">
            <div class="budget-grid">
                <div class="budget-item">
                    <div class="budget-header">
                        <div class="budget-category">Housing</div>
                        <div class="budget-amount">$350 / $500</div>
                    </div>
                    <div class="progress">
                        <div class="progress-bar progress-expense" style="width: 70%"></div>
                    </div>
                    <div class="budget-stats">
                        <span>70% Spent</span>
                        <span>$150 Remaining</span>
                    </div>
                </div>
                <div class="budget-item">
                    <div class="budget-header">
                        <div class="budget-category">Food</div>
                        <div class="budget-amount">$320 / $400</div>
                    </div>
                    <div class="progress">
                        <div class="progress-bar progress-expense" style="width: 80%"></div>
                    </div>
                    <div class="budget-stats">
                        <span>80% Spent</span>
                        <span>$80 Remaining</span>
                    </div>
                </div>
                <div class="budget-item">
                    <div class="budget-header">
                        <div class="budget-category">Transportation</div>
                        <div class="budget-amount">$180 / $250</div>
                    </div>
                    <div class="progress">
                        <div class="progress-bar progress-expense" style="width: 72%"></div>
                    </div>
                    <div class="budget-stats">
                        <span>72% Spent</span>
                        <span>$70 Remaining</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Actions -->
    <section class="actions-section" id="actions">
        <h2 class="section-title">Quick Actions</h2>
        <div class="actions-container fade-in">
            <div class="quick-actions">
                <button class="action-btn btn-income" id="add-income">
                    <i class="fas fa-plus-circle"></i> Add Income
                </button>
                <button class="action-btn btn-expense" id="add-expense">
                    <i class="fas fa-minus-circle"></i> Add Expense
                </button>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2023 Budget Planner. All rights reserved.</p>
    </footer>

    <!-- Add Transaction Modal -->
    <div class="modal fade" id="transactionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Transaction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="transactionForm">
                        <div class="mb-3">
                            <label for="transactionType" class="form-label">Type</label>
                            <select class="form-select" id="transactionType">
                                <option value="income">Income</option>
                                <option value="expense">Expense</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="transactionAmount" class="form-label">Amount</label>
                            <input type="number" class="form-control" id="transactionAmount" placeholder="0.00" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label for="transactionCategory" class="form-label">Category</label>
                            <select class="form-select" id="transactionCategory">
                                <!-- Options will be populated by JavaScript -->
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="transactionDescription" class="form-label">Description</label>
                            <input type="text" class="form-control" id="transactionDescription" placeholder="Enter description">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveTransaction">Save</button>
                </div>
            </div>
        </div>
    </div>


   
</body>
</html>