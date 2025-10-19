<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Budgets</title>
    <link rel="stylesheet" href="css/style1.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <span class="welcome-text">Welcome Back</span>
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
            <div class="page-header">
                <h1 class="page-title">My Budgets</h1>
                <button class="btn create-btn" id="createBtn">＋ Create New Budget</button>
            </div>

            <div class="budgets-grid" id="budgetsGrid">
                <!-- Example Budgets -->
                <div class="budget-card">
                    <div class="budget-header">
                        <h3 class="budget-category">Groceries</h3>
                        <span class="budget-amount">$400 / $500</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width:80%"></div>
                    </div>
                    <div class="budget-details">
                        <span>Spent: $400</span>
                        <span>Remaining: $100</span>
                    </div>
                    <div class="budget-actions">
                        <button class="btn editBtn">Edit</button>
                        <button class="btn deleteBtn danger">Delete</button>
                    </div>
                </div>

                <div class="budget-card">
                    <div class="budget-header">
                        <h3 class="budget-category">Entertainment</h3>
                        <span class="budget-amount">$150 / $200</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width:75%"></div>
                    </div>
                    <div class="budget-details">
                        <span>Spent: $150</span>
                        <span>Remaining: $50</span>
                    </div>
                    <div class="budget-actions">
                        <button class="btn editBtn">Edit</button>
                        <button class="btn deleteBtn danger">Delete</button>
                    </div>
                </div>

                <div class="budget-card">
                    <div class="budget-header">
                        <h3 class="budget-category">Transportation</h3>
                        <span class="budget-amount">$150 / $200</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width:75%"></div>
                    </div>
                    <div class="budget-details">
                        <span>Spent: $150</span>
                        <span>Remaining: $50</span>
                    </div>
                    <div class="budget-actions">
                        <button class="btn editBtn">Edit</button>
                        <button class="btn deleteBtn danger">Delete</button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Create Budget Modal -->
    <div id="budgetModal" class="modal">
        <div class="modal-content">
            <h2>Create New Budget</h2>
            <form id="budgetForm">
                <label>Category:</label>
                <input type="text" id="category" required>
                <label>Limit Amount:</label>
                <input type="number" id="limit" required>
                <div class="modal-buttons">
                    <button type="submit" class="btn">Save</button>
                    <button type="button" class="btn cancel" id="closeModal">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const createBtn = document.getElementById('createBtn');
        const modal = document.getElementById('budgetModal');
        const closeModal = document.getElementById('closeModal');
        const budgetForm = document.getElementById('budgetForm');
        const budgetsGrid = document.getElementById('budgetsGrid');

        // Open / Close Modal
        createBtn.onclick = () => modal.style.display = 'flex';
        closeModal.onclick = () => modal.style.display = 'none';
        window.onclick = e => { if (e.target === modal) modal.style.display = 'none'; }

        // Add new budget card
        budgetForm.onsubmit = e => {
            e.preventDefault();
            const category = document.getElementById('category').value;
            const limit = document.getElementById('limit').value;

            const card = document.createElement('div');
            card.className = 'budget-card fade-in';
            card.innerHTML = `
                <div class="budget-header">
                    <h3 class="budget-category">${category}</h3>
                    <span class="budget-amount">$0 / $${limit}</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width:0%"></div>
                </div>
                <div class="budget-details">
                    <span>Spent: $0</span>
                    <span>Remaining: $${limit}</span>
                </div>
                <div class="budget-actions">
                    <button class="btn editBtn">Edit</button>
                    <button class="btn deleteBtn danger">Delete</button>
                </div>
            `;
            budgetsGrid.appendChild(card);
            modal.style.display = 'none';
            budgetForm.reset();
            addButtonActions();
        };

        function addButtonActions() {
            document.querySelectorAll('.deleteBtn').forEach(btn => {
                btn.onclick = () => {
                    const card = btn.closest('.budget-card');
                    card.classList.add('fade-out');
                    setTimeout(() => card.remove(), 400);
                };
            });
            document.querySelectorAll('.editBtn').forEach(btn => {
                btn.onclick = () => alert("Edit feature coming soon!");
            });
        }
        addButtonActions();
    </script>
</body>
</html>
