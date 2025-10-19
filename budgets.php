<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budgets - Budget Tracker</title>
    <link rel="stylesheet" href="css/style1.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <a href="#" class="logo">Budget Tracker</a>
            <div class="user-menu">
                <span>Welcome, User!</span>
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
            <div class="budgets-grid" id="budgetsGrid">
                <!-- Budget Cards will be dynamically generated -->
            </div>
        </main>
    </div>

    <!-- Create/Edit Budget Modal -->
    <div id="budgetModal" class="modal">
        <div class="modal-content">
            <h2 id="modalTitle">Create New Budget</h2>
            <form id="budgetForm">
                <input type="hidden" id="budgetId">
                <div class="form-group">
                    <label for="category">Category:</label>
                    <select id="category" required>
                        <option value="">Select Category</option>
                        <option value="Groceries">Groceries</option>
                        <option value="Entertainment">Entertainment</option>
                        <option value="Transportation">Transportation</option>
                        <option value="Utilities">Utilities</option>
                        <option value="Dining">Dining</option>
                        <option value="Shopping">Shopping</option>
                        <option value="Healthcare">Healthcare</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="amount">Budget Amount ($):</label>
                    <input type="number" id="amount" min="0" step="0.01" required>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-success">Save Budget</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Sample budget data
        let budgets = [
            { id: 1, category: "Groceries", budgetAmount: 500, spent: 400 },
            { id: 2, category: "Entertainment", budgetAmount: 200, spent: 150 },
            { id: 3, category: "Transportation", budgetAmount: 300, spent: 250 }
        ];

        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            renderBudgets();
        });

        // Render all budget cards
        function renderBudgets() {
            const grid = document.getElementById('budgetsGrid');
            grid.innerHTML = '';

            budgets.forEach(budget => {
                const remaining = budget.budgetAmount - budget.spent;
                const percentage = Math.min((budget.spent / budget.budgetAmount) * 100, 100);
                const isOverBudget = budget.spent > budget.budgetAmount;

                const card = document.createElement('div');
                card.className = 'budget-card';
                card.innerHTML = `
                    <div class="budget-header">
                        <h3 class="budget-category">${budget.category}</h3>
                        <span class="budget-amount">$${budget.spent} / $${budget.budgetAmount}</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill ${isOverBudget ? 'over-budget' : ''}" 
                             style="width: ${percentage}%"></div>
                    </div>
                    <div class="budget-details">
                        <span>Spent: $${budget.spent}</span>
                        <span style="${isOverBudget ? 'color:#e74c3c' : ''}">
                            ${isOverBudget ? `Over: $${Math.abs(remaining)}` : `Remaining: $${remaining}`}
                        </span>
                    </div>
                    <div class="budget-actions">
                        <button class="btn btn-primary" onclick="editBudget(${budget.id})">Edit</button>
                        <button class="btn btn-danger" onclick="deleteBudget(${budget.id})">Delete</button>
                    </div>
                `;
                grid.appendChild(card);
            });
        }

        // Modal functions
        function openModal(budgetId = null) {
            const modal = document.getElementById('budgetModal');
            const title = document.getElementById('modalTitle');
            const form = document.getElementById('budgetForm');
            
            if (budgetId) {
                // Edit mode
                title.textContent = 'Edit Budget';
                const budget = budgets.find(b => b.id === budgetId);
                document.getElementById('budgetId').value = budget.id;
                document.getElementById('category').value = budget.category;
                document.getElementById('amount').value = budget.budgetAmount;
            } else {
                // Create mode
                title.textContent = 'Create New Budget';
                form.reset();
            }
            
            modal.style.display = 'block';
        }

        function closeModal() {
            document.getElementById('budgetModal').style.display = 'none';
        }

        // Handle form submission
        document.getElementById('budgetForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const budgetId = document.getElementById('budgetId').value;
            const category = document.getElementById('category').value;
            const amount = parseFloat(document.getElementById('amount').value);

            if (budgetId) {
                // Update existing budget
                const index = budgets.findIndex(b => b.id === parseInt(budgetId));
                if (index !== -1) {
                    budgets[index].category = category;
                    budgets[index].budgetAmount = amount;
                }
            } else {
                // Create new budget
                const newBudget = {
                    id: budgets.length > 0 ? Math.max(...budgets.map(b => b.id)) + 1 : 1,
                    category: category,
                    budgetAmount: amount,
                    spent: 0
                };
                budgets.push(newBudget);
            }

            renderBudgets();
            closeModal();
        });

        // Edit budget
        function editBudget(id) {
            openModal(id);
        }

        // Delete budget
        function deleteBudget(id) {
            if (confirm('Are you sure you want to delete this budget?')) {
                budgets = budgets.filter(budget => budget.id !== id);
                renderBudgets();
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('budgetModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>