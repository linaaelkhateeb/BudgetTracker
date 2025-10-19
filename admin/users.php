<?php
// Include the database connection file.
// __DIR__ gets the current directory (admin), '/../' goes up one level,
// then it finds 'includes/database.php'.
require_once __DIR__ . '/../includes/database.php';

$successMessage = ""; 
$errorMessage = "";
$filterMessage = "";
$edit_user_data = null; 

// --- PROCESS FORM SUBMISSIONS (SUSPEND, ACTIVATE, DELETE, UPDATE) ---
// This block runs only if the page was loaded via a POST request (i.e., a form was submitted).
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';
    // Use intval to ensure user_id is an integer, preventing errors.
    $user_id = intval($_POST['user_id'] ?? 0);

    // Using a variable for the prepared statement.
    $stmt = null;

    if ($action == 'delete' && $user_id > 0) {
        // Prepare a DELETE statement to prevent SQL injection.
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        // 'i' means the parameter is an integer. Bind the user ID to the '?'.
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $successMessage = "User #$user_id was successfully deleted.";
        }
    } elseif ($action == 'suspend' && $user_id > 0) {
        // Prepare an UPDATE statement.
        $stmt = $conn->prepare("UPDATE users SET status = 'Suspended' WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $successMessage = "User #$user_id has been suspended.";
        }
    } elseif ($action == 'activate' && $user_id > 0) {
        // Prepare an UPDATE statement.
        $stmt = $conn->prepare("UPDATE users SET status = 'Active' WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $successMessage = "User #$user_id has been activated.";
        }
    } elseif ($action == 'update' && $user_id > 0) {
        // Get name and email from the edit modal form. trim() removes whitespace.
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        // 'ssi' means two strings, then one integer.
        $stmt->bind_param("ssi", $name, $email, $user_id);
        if ($stmt->execute()) {
            $successMessage = "User #$user_id details have been updated.";
        }
    }

    // Check if a statement was prepared and executed.
    if ($stmt) {
        // If there's no success message, it means an error occurred.
        if (!$successMessage) {
            $errorMessage = "Database error: " . htmlspecialchars($stmt->error);
        }
        // Always close the statement to free up resources.
        $stmt->close();
    }
}

// --- PROCESS URL ACTIONS (EDIT) ---
// This block runs if the URL has ?action=edit&id=...
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $id_to_edit = intval($_GET['id']);
    // Prepare a SELECT statement to get the data for the user being edited.
    $stmt = $conn->prepare("SELECT id, name, email FROM users WHERE id = ?");
    $stmt->bind_param("i", $id_to_edit);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        // Fetch the user data and store it to pre-fill the modal.
        $edit_user_data = $result->fetch_assoc();
    }
    $stmt->close();
}

// --- BUILD QUERY FOR FETCHING USERS (READ) ---
// Base SQL query.
$sql = "SELECT id, name, email, DATE_FORMAT(join_date, '%Y-%m-%d') as join_date, status FROM users";
$params = []; // Array to hold parameters for binding.
$types = ''; // String to hold the types of parameters (e.g., 's', 'ss').

// Get filter values from the URL.
$filter_status = $_GET['status'] ?? 'all'; 
$search_query = trim($_GET['search'] ?? '');

$where_clauses = []; // Array to hold the WHERE parts of the query.

// If a status filter is active, add it to the query.
if ($filter_status != 'all') {
    $where_clauses[] = "status = ?";
    $params[] = $filter_status;
    $types .= 's'; // 's' for string.
    $filterMessage = "Filtering by status: <strong>" . htmlspecialchars($filter_status) . "</strong>";
}

// If a search query is entered, add it to the query.
if (!empty($search_query)) {
    $where_clauses[] = "(name LIKE ? OR email LIKE ?)";
    $search_param = "%" . $search_query . "%"; // Add wildcards for partial matches.
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss'; // Two strings.
    $filterMessage .= ($filterMessage ? " and " : "") . "Searching for: <strong>" . htmlspecialchars($search_query) . "</strong>";
}

// If there are any WHERE clauses, add them to the main SQL query.
if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(' AND ', $where_clauses);
}

// Always order the results.
$sql .= " ORDER BY id DESC";

// Prepare and execute the final SELECT query.
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    // The '...' unpacks the $params array into individual arguments for bind_param.
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
// Fetch all resulting rows into an associative array.
$filtered_users = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Close the main database connection at the end of the script.
$conn->close();

// Set the current page for sidebar styling.
$currentPage = 'users'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    
    <!-- This links to your admin-specific stylesheet -->
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>

    <div class="page-wrapper">
        
        <div class="sidebar">
            <div class="brand">Budget<span>Tracker</span></div>
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
                <li class="nav-item"><a class="nav-link active" href="#"><i class="fas fa-users"></i>User Management</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-chart-line"></i>System Analytics</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-cog"></i>Site Settings</a></li>
            </ul>
        </div>

        <main class="main-content">
            
            <h1 class="h3 mb-4">User Management</h1>

            <!-- Display success, error, or filter messages -->
            <?php if ($successMessage): ?><div class="alert alert-success"><?= $successMessage; ?></div><?php endif; ?>
            <?php if ($errorMessage): ?><div class="alert alert-danger"><?= $errorMessage; ?></div><?php endif; ?>
            <?php if ($filterMessage): ?><div class="alert alert-info"><?= $filterMessage; ?></div><?php endif; ?>

            <!-- Filter Card -->
            <div class="card mb-4">
                <div class="card-body p-4">
                    <form action="users.php" method="GET" class="row g-3 align-items-end">
                        <div class="col-lg-5">
                            <label for="searchUser" class="form-label">Search User</label>
                            <input type="search" id="searchUser" name="search" class="form-control" placeholder="Search by name or email..." value="<?= htmlspecialchars($search_query) ?>">
                        </div>
                        <div class="col-lg-3">
                            <label for="filterStatus" class="form-label">Status</label>
                            <select id="filterStatus" name="status" class="form-select">
                                <option value="all">All</option>
                                <!-- "Sticky" filter options -->
                                <option value="Active" <?php if($filter_status == 'Active') echo 'selected'; ?>>Active</option>
                                <option value="Suspended" <?php if($filter_status == 'Suspended') echo 'selected'; ?>>Suspended</option>
                            </select>
                        </div>
                        <div class="col-lg-2">
                            <button type="submit" class="btn btn-secondary w-100">Filter</button>
                        </div>
                        <div class="col-lg-2 text-lg-end">
                             <a href="users.php" class="btn btn-outline-secondary w-100">Clear</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Users Table Card -->
            <div class="card">
                <div class="card-body p-0">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th class="p-3">ID</th><th class="p-3">Name</th><th class="p-3">Email</th><th class="p-3">Join Date</th><th class="p-3">Status</th><th class="text-center p-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($filtered_users)): ?>
                                <tr><td colspan="6" class="text-center p-4">No users found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($filtered_users as $user): ?>
                                    <tr>
                                        <td class="p-3"><?= $user['id'] ?></td>
                                        <td class="p-3"><?= htmlspecialchars($user['name']) ?></td>
                                        <td class="p-3"><?= htmlspecialchars($user['email']) ?></td>
                                        <td class="p-3"><?= $user['join_date'] ?></td>
                                        <td class="p-3">
                                            <span class="badge <?= $user['status'] == 'Active' ? 'bg-success' : 'bg-secondary' ?>">
                                                <?= $user['status'] ?>
                                            </span>
                                        </td>
                                        <td class="text-center p-3">
                                            <div class="d-flex justify-content-center">
                                                <!-- Edit button is a link to reload the page in edit mode -->
                                                <a href="?action=edit&id=<?= $user['id'] ?>" class="btn btn-sm btn-outline-secondary me-2" title="Edit User"><i class="fas fa-pencil-alt"></i></a>
                                                
                                                <!-- Suspend/Activate button is a mini-form -->
                                                <form action="users.php" method="POST" class="me-2">
                                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                    <?php if ($user['status'] == 'Active'): ?>
                                                        <input type="hidden" name="action" value="suspend">
                                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Suspend User"><i class="fas fa-user-slash"></i></button>
                                                    <?php else: ?>
                                                        <input type="hidden" name="action" value="activate">
                                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Activate User"><i class="fas fa-user-check"></i></button>
                                                    <?php endif; ?>
                                                </form>
                                                
                                                <!-- Delete button is a mini-form with a confirmation dialog -->
                                                <form action="users.php" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this user?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete User"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="users.php" method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($edit_user_data['id'] ?? '') ?>">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit User Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="userName" class="form-label">Name</label>
                            <input type="text" class="form-control" id="userName" name="name" value="<?= htmlspecialchars($edit_user_data['name'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="userEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="userEmail" name="email" value="<?= htmlspecialchars($edit_user_data['email'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- This small script runs only if the page is in edit mode. -->
    <?php if ($edit_user_data): ?>
    <script>
        // Wait for the document to be fully loaded before running JS.
        document.addEventListener('DOMContentLoaded', function () {
            // Get the modal element from the page.
            var editModalElement = document.getElementById('editUserModal');
            // Create a new Bootstrap Modal object.
            var editModal = new bootstrap.Modal(editModalElement);
            // Tell the modal object to show itself.
            editModal.show();
        });
    </script>
    <?php endif; ?>
</body>
</html>

