<?php
// Start of the main PHP block.

/**
 * ===================================================================
 * BACKEND PART 1: PROCESS NEW TRANSACTION (from the modal)
 * ===================================================================
 * We check if the form was submitted using the 'POST' method.
 */

// This variable will hold our success message.
$successMessage = ""; 

// '$_SERVER["REQUEST_METHOD"]' checks how the page was loaded.
// If it was 'POST', it means the modal's 'Save' button was clicked.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // We 'save' the data by reading it from the $_POST array.
    // 'htmlspecialchars' is a security step.
    $type = htmlspecialchars($_POST['modalType']);
    $amount = htmlspecialchars($_POST['amount']);
    $date = htmlspecialchars($_POST['date']);
    $category_id = htmlspecialchars($_POST['category_id']);
    $description = htmlspecialchars($_POST['description']);

    // --- This is where you WOULD save to a database ---
    // For now, we just build a success message to prove it worked.
    
    $successMessage = "<strong>Success!</strong> You 'saved' a new $type. 
                       Amount: $$amount, 
                       Date: $date, 
                       Category ID: $category_id, 
                       Description: $description";
}


/**
 * ===================================================================
 * BACKEND PART 2: PROCESS FILTERS (from the filter bar)
 * ===================================================================
 * We check if any filter data was sent using the 'GET' method.
 */

// This variable will hold our filter message.
$filterMessage = "";
$filter_type = ""; // We'll use this to make the filter "sticky"

// 'isset()' checks if a variable exists.
// We check if 'type' was in the URL (e.g., ?type=expense).
if (isset($_GET['type']) && $_GET['type'] != 'all') {
    $filter_type = htmlspecialchars($_GET['type']);
    $filterMessage = "You are filtering by type: <strong>$filter_type</strong>";
}


/**
 * ===================================================================
 * MOCK PHP DATA (for the table)
 * ===================================================================
 */
$transactions = [
    ['id' => 1, 'date' => '2025-10-18', 'description' => 'Monthly Salary', 'category' => 'Salary', 'type' => 'income', 'amount' => 3500.00],
    ['id' => 2, 'date' => '2025-10-17', 'description' => 'Weekly Groceries', 'category' => 'Groceries', 'type' => 'expense', 'amount' => 124.50],
    ['id' => 3, 'date' => '2025-10-16', 'description' => 'Electricity Bill', 'category' => 'Utilities', 'type' => 'expense', 'amount' => 85.00]
];
$currentPage = 'transactions'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions - BudgetWise</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Open+Sans&family=Roboto+Mono&display=swap" rel="stylesheet">
</head>
<body>

    <div class="d-flex">
        
        <div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark" style="width: 250px; min-height: 100vh;">
            <h5 class="mb-4">BudgetWise</h5>
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item"><a href="#" class="nav-link text-white <?php if($currentPage == 'dashboard') echo 'active'; ?>">Dashboard</a></li>
                <li class="nav-item"><a href="#" class="nav-link text-white <?php if($currentPage == 'transactions') echo 'active'; ?>">Transactions</a></li>
                <li class="nav-item"><a href="#" class="nav-link text-white <?php if($currentPage == 'budgets') echo 'active'; ?>">Budgets</a></li>
                <li class="nav-item"><a href="#" class="nav-link text-white <?php if($currentPage == 'reports') echo 'active'; ?>">Reports</a></li>
                <li class="nav-item"><a href="#" class="nav-link text-white <?php if($currentPage == 'settings') echo 'active'; ?>">Settings</a></li>
            </ul>
        </div>

        <main class="flex-grow-1 p-4" style="background-color: #ecf0f1;">
            
            <h1 class="h3 mb-4">Transactions</h1>

            <?php
            // If the $successMessage variable is not empty, display it.
            if ($successMessage != ""):
            ?>
                <div class="alert alert-success">
                    <?php echo $successMessage; // Print the message here ?>
                </div>
            <?php
            endif; // End the 'if' statement
            
            // If the $filterMessage variable is not empty, display it.
            if ($filterMessage != ""):
            ?>
                <div class="alert alert-info">
                    <?php echo $filterMessage; // Print the message here ?>
                </div>
            <?php
            endif; // End the 'if' statement
            ?>

            <div class="card mb-4">
                <div class="card-body">
                    <form action="transactions.php" method="GET" class="row g-3 align-items-center">
                        <div class="col-md-3">
                            <label for="dateStart" class="form-label-sm">From</label>
                            <input type="date" id="dateStart" name="date_start" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label for="dateEnd" class="form-label-sm">To</label>
                            <input type="date" id="dateEnd" name="date_end" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label for="filterCategory" class="form-label-sm">Category</label>
                            <select id="filterCategory" name="category" class="form-select">
                                <option value="all">All</option>
                                <option value="groceries">Groceries</option>
                                <option value="salary">Salary</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="filterType" class="form-label-sm">Type</label>
                            <select id="filterType" name="type" class="form-select">
                                <option value="all">All</option>
                                
                                <option value="income" <?php if($filter_type == 'income') echo 'selected'; ?>>
                                    Income
                                </option>
                                <option value="expense" <?php if($filter_type == 'expense') echo 'selected'; ?>>
                                    Expense
                                </option>
                            </select>
                        </div>
                        
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-secondary w-100">Filter</button>
                        </div>
                    </form>
                    
                    <hr>
                    
                    <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#transactionModal">
                        <i class="fas fa-plus me-1"></i> Add Transaction
                    </button>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Date</th><th>Description</th><th>Category</th><th class="text-end">Amount</th><th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $tx): ?>
                                <tr>
                                    <td><?= htmlspecialchars($tx['date']) ?></td>
                                    <td><?= htmlspecialchars($tx['description']) ?></td>
                                    <td><?= htmlspecialchars($tx['category']) ?></td>
                                    
                                    <?php if ($tx['type'] == 'income'): ?>
                                        <td class="text-end mono-number text-income fw-bold">
                                            +$<?= number_format($tx['amount'], 2) ?>
                                        </td>
                                    <?php else: ?>
                                        <td class="text-end mono-number text-expense fw-bold">
                                            -$<?= number_format($tx['amount'], 2) ?>
                                        </td>
                                    <?php endif; ?>
                                    
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#transactionModal">
                                            <i class="fas fa-pencil-alt"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div class="modal fade" id="transactionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                
                <form id="transactionForm" action="transactions.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Transaction</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        
                        <input type="hidden" name="transaction_id" value="">

                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="modalType" id="modalTypeIncome" value="income" autocomplete="off">
                                <label class="btn btn-outline-success" for="modalTypeIncome"><i class="fas fa-plus me-1"></i> Income</label>
                                
                                <input type="radio" class="btn-check" name="modalType" id="modalTypeExpense" value="expense" autocomplete="off" checked>
                                <label class="btn btn-outline-danger" for="modalTypeExpense"><i class="fas fa-minus me-1"></i> Expense</label>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="modalAmount" class="form-label">Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="modalAmount" name="amount" placeholder="0.00" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="modalDate" class="form-label">Date</label>
                                <input type="date" class="form-control" id="modalDate" name="date" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="modalCategory" class="form-label">Category</label>
                            <select id="modalCategory" name="category_id" class.form-select" required>
                                <option value="">-- Select Category --</option>
                                <option value="1">Groceries</option>
                                <option value="2">Salary</option>
                                <option value="3">Utilities</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="modalDescription" class="form-label">Description</label>
                            <textarea id="modalDescription" name="description" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary-custom">Save Transaction</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>