<?php
// --- PHP Backend Logic ---

$successMessage = ""; 
$errorMessage = "";
$filterMessage = "";
$edit_data = null; // This will hold the transaction data when we are editing

// --- MOCK DATA (Would be a database call in a real app) ---
$transactions = [
    ['id' => 1, 'date' => '2025-10-18', 'description' => 'Monthly Salary', 'category' => 'Salary', 'type' => 'income', 'amount' => 3500.00],
    ['id' => 2, 'date' => '2025-10-17', 'description' => 'Weekly Groceries', 'category' => 'Groceries', 'type' => 'expense', 'amount' => 124.50],
    ['id' => 3, 'date' => '2025-10-16', 'description' => 'Electricity Bill', 'category' => 'Utilities', 'type' => 'expense', 'amount' => 85.00],
    ['id' => 4, 'date' => '2025-10-15', 'description' => 'Internet Bill', 'category' => 'Utilities', 'type' => 'expense', 'amount' => 60.00],
    ['id' => 5, 'date' => '2025-10-14', 'description' => 'Freelance Project', 'category' => 'Income', 'type' => 'income', 'amount' => 500.00]
];

// --- PROCESS FORM SUBMISSIONS (CREATE, UPDATE, DELETE) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if it's a delete action
    if (isset($_POST['action']) && $_POST['action'] == 'delete') {
        $id_to_delete = htmlspecialchars($_POST['transaction_id']);
        $successMessage = "<strong>Success!</strong> Transaction with ID #$id_to_delete was 'deleted'.";
        // In a real app, you would run a DELETE SQL query here.
    } 
    // Otherwise, it's a create or update action
    else {
        $id = htmlspecialchars($_POST['transaction_id']);
        $type = htmlspecialchars($_POST['modalType']);
        $amount = htmlspecialchars($_POST['amount']);
        $date = htmlspecialchars($_POST['date']);
        $category_id = htmlspecialchars($_POST['category_id']);
        $description = htmlspecialchars($_POST['description']);
        
        if (empty($id)) {
            // CREATE: No ID was submitted, so it's a new transaction
            $successMessage = "<strong>Success!</strong> You 'saved' a new $type. Amount: $$amount, Date: $date.";
            // In a real app, you would run an INSERT SQL query here.
        } else {
            // UPDATE: An ID was submitted, so we're editing
            $successMessage = "<strong>Success!</strong> You 'updated' transaction #$id.";
            // In a real app, you would run an UPDATE SQL query here.
        }
    }
}

// --- PROCESS URL ACTIONS (EDIT) ---
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $id_to_edit = $_GET['id'];
    // Find the transaction in our mock array
    foreach ($transactions as $tx) {
        if ($tx['id'] == $id_to_edit) {
            $edit_data = $tx;
            break;
        }
    }
    if (!$edit_data) {
        $errorMessage = "Transaction not found.";
    }
}

// --- PROCESS FILTERS (READ) ---
$filter_type = $_GET['type'] ?? 'all'; 
$filtered_transactions = $transactions; // Start with all transactions

if ($filter_type != 'all') {
    $filterMessage = "You are filtering by type: <strong>" . htmlspecialchars($filter_type) . "</strong>";
    $temp_transactions = [];
    foreach ($filtered_transactions as $tx) {
        if ($tx['type'] == $filter_type) {
            $temp_transactions[] = $tx;
        }
    }
    $filtered_transactions = $temp_transactions;
}

$currentPage = 'transactions'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions - BudgetTracker</title>

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    
   
    <link rel="stylesheet" href="css/transactions.css">
</head>
<body>

    <div class="page-wrapper">
        
        <div class="sidebar">
            <div class="brand">BudgetTracker</div>
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link <?php if($currentPage == 'dashboard') echo 'active'; ?>" href="#">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link <?php if($currentPage == 'transactions') echo 'active'; ?>" href="#">Transactions</a></li>
                <li class="nav-item"><a class="nav-link <?php if($currentPage == 'budgets') echo 'active'; ?>" href="#">Budgets</a></li>
                <li class="nav-item"><a class="nav-link <?php if($currentPage == 'reports') echo 'active'; ?>" href="#">Reports</a></li>
                <li class="nav-item"><a class="nav-link <?php if($currentPage == 'settings') echo 'active'; ?>" href="#">Settings</a></li>
            </ul>
        </div>

        <main class="main-content">
            
            <h1 class="h3 mb-4">Transactions</h1>

            <!-- PHP Alert Display -->
            <?php if ($successMessage): ?><div class="alert alert-success"><?= $successMessage; ?></div><?php endif; ?>
            <?php if ($errorMessage): ?><div class="alert alert-danger"><?= $errorMessage; ?></div><?php endif; ?>
            <?php if ($filterMessage): ?><div class="alert alert-info"><?= $filterMessage; ?></div><?php endif; ?>

            <!-- Filter Header Card -->
            <div class="card mb-4">
                <div class="card-body p-4">
                    <form action="transactions.php" method="GET" class="row g-3 align-items-end">
                        <div class="col-lg-3 col-md-6">
                            <label for="dateStart" class="form-label">From</label>
                            <input type="date" id="dateStart" name="date_start" class="form-control">
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label for="dateEnd" class="form-label">To</label>
                            <input type="date" id="dateEnd" name="date_end" class="form-control">
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <label for="filterType" class="form-label">Type</label>
                            <select id="filterType" name="type" class="form-select">
                                <option value="all">All</option>
                                <option value="income" <?php if($filter_type == 'income') echo 'selected'; ?>>Income</option>
                                <option value="expense" <?php if($filter_type == 'expense') echo 'selected'; ?>>Expense</option>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <button type="submit" class="btn btn-secondary w-100">Filter</button>
                        </div>
                        <div class="col-lg-2 col-md-12 text-lg-end">
                            <button type="button" class="btn btn-primary-custom w-100" data-bs-toggle="modal" data-bs-target="#transactionModal">
                                <i class="fas fa-plus me-1"></i> Add New
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Transactions Table Card -->
            <div class="card">
                <div class="card-body p-0">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th class="p-3">Date</th><th class="p-3">Description</th><th class="p-3">Category</th><th class="text-end p-3">Amount</th><th class="text-center p-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($filtered_transactions as $tx): ?>
                                <tr>
                                    <td class="p-3"><?= htmlspecialchars($tx['date']) ?></td>
                                    <td class="p-3"><?= htmlspecialchars($tx['description']) ?></td>
                                    <td class="p-3"><?= htmlspecialchars($tx['category']) ?></td>
                                    <td class="text-end mono-number fw-bold p-3 <?= $tx['type'] == 'income' ? 'text-income' : 'text-expense' ?>">
                                        <?= $tx['type'] == 'income' ? '+' : '-' ?>$<?= number_format($tx['amount'], 2) ?>
                                    </td>
                                    <td class="text-center p-3">
                                        <div class="d-flex justify-content-center">
                                            <a href="?action=edit&id=<?= $tx['id'] ?>" class="btn btn-sm btn-outline-secondary me-2"><i class="fas fa-pencil-alt"></i></a>
                                            <form action="transactions.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this item?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="transaction_id" value="<?= $tx['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Add/Edit Modal -->
    <div class="modal fade" id="transactionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="transactions.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title"><?= $edit_data ? 'Edit Transaction' : 'Add New Transaction' ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="transaction_id" value="<?= htmlspecialchars($edit_data['id'] ?? '') ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="modalType" id="modalTypeIncome" value="income" autocomplete="off" <?= ($edit_data['type'] ?? 'expense') == 'income' ? 'checked' : '' ?>><label class="btn btn-outline-success" for="modalTypeIncome"><i class="fas fa-plus me-1"></i> Income</label>
                                <input type="radio" class="btn-check" name="modalType" id="modalTypeExpense" value="expense" autocomplete="off" <?= ($edit_data['type'] ?? 'expense') == 'expense' ? 'checked' : '' ?>><label class="btn btn-outline-danger" for="modalTypeExpense"><i class="fas fa-minus me-1"></i> Expense</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="modalAmount" class="form-label">Amount</label>
                                <div class="input-group"><span class="input-group-text">$</span><input type="number" step="0.01" class="form-control" id="modalAmount" name="amount" placeholder="0.00" required value="<?= htmlspecialchars($edit_data['amount'] ?? '') ?>"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="modalDate" class="form-label">Date</label>
                                <input type="date" class="form-control" id="modalDate" name="date" required value="<?= htmlspecialchars($edit_data['date'] ?? date('Y-m-d')) ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="modalCategory" class="form-label">Category</label>
                            <select id="modalCategory" name="category_id" class="form-select" required>
                                <option value="">-- Select Category --</option>
                                <option value="1" <?= ($edit_data['category'] ?? '') == 'Groceries' ? 'selected' : '' ?>>Groceries</option>
                                <option value="2" <?= ($edit_data['category'] ?? '') == 'Salary' ? 'selected' : '' ?>>Salary</option>
                                <option value="3" <?= ($edit_data['category'] ?? '') == 'Utilities' ? 'selected' : '' ?>>Utilities</option>
                                <option value="4" <?= ($edit_data['category'] ?? '') == 'Income' ? 'selected' : '' ?>>Income</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="modalDescription" class="form-label">Description</label>
                            <textarea id="modalDescription" name="description" class="form-control" rows="3"><?= htmlspecialchars($edit_data['description'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary-custom"><?= $edit_data ? 'Update Transaction' : 'Save Transaction' ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JavaScript Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- SCRIPT to auto-open modal if in edit mode -->
    <?php if ($edit_data): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var editModal = new bootstrap.Modal(document.getElementById('transactionModal'));
            editModal.show();
        });
    </script>
    <?php endif; ?>
</body>
</html>

