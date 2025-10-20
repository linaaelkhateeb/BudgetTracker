<?php
// Connect to database
$host = "localhost";
$user = "root";
$pass = "";
$db = "budgettracker"; // ghayarooha l esm el database 3andoko
$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle Add Income
if (isset($_POST['add_income'])) {
    $amount = $_POST['amount'];
    $category = $_POST['category'];
    $type = 'income';
    $query = "INSERT INTO budgets (amount, category, type) VALUES ('$amount', '$category', '$type')";
    mysqli_query($conn, $query);
}

// Handle Add Expense
if (isset($_POST['add_expense'])) {
    $amount = $_POST['amount'];
    $category = $_POST['category'];
    $type = 'expense';
    $query = "INSERT INTO budgets (amount, category, type) VALUES ('$amount', '$category', '$type')";
    mysqli_query($conn, $query);
}

// Get totals
$income_query = mysqli_query($conn, "SELECT SUM(amount) AS total_income FROM budgets WHERE type='income'");
$expense_query = mysqli_query($conn, "SELECT SUM(amount) AS total_expense FROM budgets WHERE type='expense'");
$income = mysqli_fetch_assoc($income_query)['total_income'] ?? 0;
$expense = mysqli_fetch_assoc($expense_query)['total_expense'] ?? 0;
$balance = $income - $expense;

// Get all records
$records = mysqli_query($conn, "SELECT * FROM budgets ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Budgets</title>
  <link rel="stylesheet" href="css/style1.css" />
</head>
<body>
  <div class="auth-wrap">
    <!-- Left Section -->
    <div class="auth-hero">
      <div class="brand">
        <div class="brand-badge">💰</div>
        <div class="brand-title">Budget Tracker</div>
      </div>
      <h1>Manage Your Budget</h1>
      <p>Track your income and expenses in one place. Stay organized, stay in control.</p>

      <div class="hero-cards">
        <div class="hero-card">
          <h6>Total Income</h6>
          <p>EGP <?php echo number_format($income, 2); ?></p>
        </div>
        <div class="hero-card">
          <h6>Total Expenses</h6>
          <p>EGP <?php echo number_format($expense, 2); ?></p>
        </div>
        <div class="hero-card">
          <h6>Balance</h6>
          <p><strong>EGP <?php echo number_format($balance, 2); ?></strong></p>
        </div>
      </div>
    </div>

    <!-- Right Section -->
    <div class="auth-panel">
      <div class="auth-card">
        <h2>Add Income</h2>
        <form method="POST">
          <label class="form-label">Amount:</label><br />
          <input type="number" name="amount" class="form-control" required /><br />
          <label class="form-label">Category:</label><br />
          <input type="text" name="category" class="form-control" required /><br />
          <button type="submit" name="add_income" class="btn btn-primary">Add Income</button>
        </form>

        <div class="divider"><span>or</span></div>

        <h2>Add Expense</h2>
        <form method="POST">
          <label class="form-label">Amount:</label><br />
          <input type="number" name="amount" class="form-control" required /><br />
          <label class="form-label">Category:</label><br />
          <input type="text" name="category" class="form-control" required /><br />
          <button type="submit" name="add_expense" class="btn btn-success">Add Expense</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Records Section -->
  <div style="padding: 40px; max-width: 900px; margin: 0 auto;">
    <h2 style="text-align:center; color: var(--primary); margin-bottom: 20px;">Recent Transactions</h2>
    <table style="width:100%; border-collapse: collapse; background:#fff; border-radius:10px; overflow:hidden; box-shadow: var(--shadow);">
      <thead style="background: var(--secondary); color:#fff;">
        <tr>
          <th style="padding:12px;">ID</th>
          <th style="padding:12px;">Category</th>
          <th style="padding:12px;">Type</th>
          <th style="padding:12px;">Amount</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = mysqli_fetch_assoc($records)): ?>
        <tr style="border-bottom:1px solid #ddd;">
          <td style="padding:10px;"><?php echo $row['id']; ?></td>
          <td style="padding:10px;"><?php echo htmlspecialchars($row['category']); ?></td>
          <td style="padding:10px; color:<?php echo $row['type']=='income' ? 'green' : 'red'; ?>;">
            <?php echo ucfirst($row['type']); ?>
          </td>
          <td style="padding:10px;">EGP <?php echo number_format($row['amount'], 2); ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
