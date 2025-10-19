<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budgets - Budget Tracker</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Additional styles for budget page */
        .app-container {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 2rem;
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Header */
        .header {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(6px);
            border-bottom: 1px solid rgba(198, 184, 162, 0.55);
            padding: 1rem 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .logo {
            font-family: "Poppins", sans-serif;
            font-weight: 800;
            font-size: 1.8rem;
            color: var(--primary);
            text-decoration: none;
            transition: transform 0.3s ease, color 0.3s ease;
        }

        .logo:hover {
            transform: translateY(-2px);
            color: var(--accent);
        }

        .user-menu {
            font-weight: 600;
            color: var(--support-1);
        }

        /* Sidebar */
        .sidebar {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(6px);
            border: 1px solid rgba(198, 184, 162, 0.55);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 1.5rem;
            height: fit-content;
            position: sticky;
            top: 2rem;
        }

        .sidebar-nav {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-nav li {
            margin-bottom: 0.5rem;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            text-decoration: none;
            color: var(--text);
            border-radius: 10px;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .sidebar-nav a:hover {
            background: rgba(123, 75, 58, 0.1);
            transform: translateX(5px);
            color: var(--primary);
        }

        .sidebar-nav a.active {
            background: var(--primary);
            color: white;
            box-shadow: 0 6px 16px rgba(123, 75, 58, 0.22);
        }

        /* Main Content */
        .main-content {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(6px);
            border: 1px solid rgba(198, 184, 162, 0.55);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 2rem;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid rgba(198, 184, 162, 0.3);
        }

        .page-title {
            font-weight: 800;
            color: var(--primary);
            font-size: 2.2rem;
            margin: 0;
        }

        /* Buttons */
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 10px;
            font-family: "Poppins", sans-serif;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .btn:active {
            transform: translateY(-1px);
        }

        .btn-success {
            background: var(--support-1);
            color: white;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-danger {
            background: #e74c3c;
            color: white;
        }

        /* Budgets Grid */
        .budgets-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 1rem;
        }

        /* Budget Card */
        .budget-card {
            background: white;
            border-radius: var(--radius);
            border: 1px solid rgba(198, 184, 162, 0.6);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
            padding: 1.5rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .budget-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
        }

        .budget-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.14);
        }

        .budget-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .budget-category {
            font-weight: 700;
            color: var(--primary);
            font-size: 1.3rem;
            margin: 0;
        }

        .budget-amount {
            font-weight: 600;
            color: var(--support-1);
            font-size: 1.1rem;
        }

        /* Progress Bar */
        .progress-bar {
            width: 100%;
            height: 8px;
            background: rgba(198, 184, 162, 0.3);
            border-radius: 10px;
            overflow: hidden;
            margin: 1rem 0;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--support-1), var(--primary));
            border-radius: 10px;
            transition: width 0.5s ease;
        }

        .progress-fill.over-budget {
            background: linear-gradient(90deg, #e74c3c, #c0392b);
        }

        /* Budget Details */
        .budget-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .budget-details span:last-child {
            color: var(--support-1);
        }

        .budget-actions {
            display: flex;
            gap: 0.5rem;
        }

        .budget-actions .btn {
            flex: 1;
            justify-content: center;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            animation: fadeIn 0.3s ease;
        }

        .modal-content {
            background: white;
            margin: 10% auto;
            padding: 2rem;
            border-radius: var(--radius);
            width: 90%;
            max-width: 500px;
            animation: slideUp 0.3s ease;
            border: 1px solid rgba(198, 184, 162, 0.6);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--support-1);
            font-family: "Poppins", sans-serif;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid rgba(198, 184, 162, 0.75);
            border-radius: 10px;
            background: #fff;
            transition: all 0.3s ease;
            font-family: "Open Sans", sans-serif;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 0.25rem rgba(161, 104, 85, 0.25);
            transform: translateY(-2px);
            outline: none;
        }

        .modal-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .app-container {
                grid-template-columns: 1fr;
                padding: 1rem;
                gap: 1rem;
            }
            
            .sidebar {
                position: static;
                order: 2;
            }
            
            .main-content {
                order: 1;
            }
            
            .page-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
            
            .budgets-grid {
                grid-template-columns: 1fr;
            }
            
            .header-content {
                padding: 0 1rem;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: 1rem;
            }
            
            .modal-content {
                margin: 5% auto;
                padding: 1.5rem;
            }
            
            .budget-actions {
                flex-direction: column;
            }
        }

        /* Animation for new budget cards */
        @keyframes cardAppear {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .budget-card {
            animation: cardAppear 0.5s ease;
        }
    </style>
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