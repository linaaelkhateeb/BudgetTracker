<?php
// Connect to database
$host = "localhost";
$user = "root";
$pass = "";
$db = "budget_db"; // change to your DB name
$conn = mysqli_connect($host, $user, $pass, $db);

// Handle add income
if (isset($_POST['add_income'])) {
    $amount = $_POST['amount'];
    $category = $_POST['category'];
    $type = 'income';
    $query = "INSERT INTO budget (amount, category, type) VALUES ('$amount', '$category', '$type')";
    mysqli_query($conn, $query);
}

// Handle add expense
if (isset($_POST['add_expense'])) {
    $amount = $_POST['amount'];
    $category = $_POST['category'];
    $type = 'expense';
    $query = "INSERT INTO budget (amount, category, type) VALUES ('$amount', '$category', '$type')";
    mysqli_query($conn, $query);
}

// Totals
$total_income = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) AS total FROM budget WHERE type='income'"))['total'] ?? 0;
$total_expense = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) AS total FROM budget WHERE type='expense'"))['total'] ?? 0;
$balance = $total_income - $total_expense;

// Get all records
$result = mysqli_query($conn, "SELECT * FROM budget ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Budget</title>
  <link rel="stylesheet" href="style1.css">
</head>
<body>
  <div class="auth-wrap">
    <!-- Left hero -->
    <div class="auth-hero">
      <h1>Smart Budget Tracker</h1>
      <p>Manage your income and expenses effortlessly. Track your financial balance and stay in control!</p>
      <div class="hero-cards">
        <div class="hero-card">
          <h6>Total Income</h6>
          <p>💰 <?php echo number_format($total_income, 2); ?> EGP</p>
        </div>
        <div class="hero-card">
          <h6>Total Expenses</h6>
          <p>💸 <?php echo number_format($total_expense, 2); ?> EGP</p>
        </div>
        <div class="hero-card">
          <h6>Balance</h6>
          <p>⚖️ <?php echo number_format($balance, 2); ?> EGP</p>
        </div>
      </div>
    </div>

    <!-- Right Panel -->
    <div class="auth-panel">
      <div class="auth-card">
        <h2>Manage Your Budget</h2>

        <form method="post">
          <label class="form-label">Amount</label><br>
          <input type="number" name="amount" class="form-control" required><br>

          <label class="form-label">Category</label><br>
          <select name="category" class="form-control" required>
            <option value="Salary">Salary</option>
            <option value="Groceries">Groceries</option>
            <option value="Bills">Bills</option>
            <option value="Transportation">Transportation</option>
            <option value="Shopping">Shopping</option>
            <option value="Other">Other</option>
          </select><br>

          <div class="btns">
            <button type="submit" name="add_income" class="btn btn-primary">+ Add Income</button>
            <button type="submit" name="add_expense" class="btn btn-success">− Add Expense</button>
          </div>
        </form>

        <div class="divider"><span>Recent Transactions</span></div>
        <table class="table">
          <tr>
            <th>Type</th>
            <th>Category</th>
            <th>Amount (EGP)</th>
          </tr>
          <?php while ($row = mysqli_fetch_assoc($result)): ?>
          <tr>
            <td><?php echo ucfirst($row['type']); ?></td>
            <td><?php echo $row['category']; ?></td>
            <td><?php echo number_format($row['amount'], 2); ?></td>
          </tr>
          <?php endwhile; ?>
        </table>
      </div>
    </div>
  </div>

  <!-- Smooth page motion -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      document.body.style.opacity = '1';
      document.body.style.transform = 'translateY(0)';
    });
  </script>
</body>
</html>
