<?php
require 'db.php';
include 'nav.php';

// Get selected month filter
$monthFilter = $_GET['month'] ?? date('Y-m'); // default current month

// Build WHERE clause for month filter
$whereMonth = "DATE_FORMAT(transaction_date, '%Y-%m') = '{$monthFilter}'";

// Combine transactions + expenses + paid invoices
$query = "
    SELECT id, transaction_date AS date, description, type, amount
    FROM transactions
    WHERE $whereMonth
    
    UNION
    
    SELECT e.id, e.expense_date AS date, e.description, 'expense' AS type, e.amount
    FROM expenses e
    WHERE DATE_FORMAT(e.expense_date, '%Y-%m') = '{$monthFilter}'
      AND NOT EXISTS (
        SELECT 1 FROM transactions t 
        WHERE t.description = e.description 
          AND t.amount = e.amount 
          AND t.transaction_date = e.expense_date
      )
      
    UNION
    
    SELECT i.id, i.invoice_date AS date, CONCAT('Invoice #', i.id) AS description, 'income' AS type, i.amount
    FROM invoices i
    WHERE i.status = 'paid'
      AND DATE_FORMAT(i.invoice_date, '%Y-%m') = '{$monthFilter}'
      AND NOT EXISTS (
        SELECT 1 FROM transactions t
        WHERE t.description = CONCAT('Invoice #', i.id)
          AND t.amount = i.amount
          AND t.transaction_date = i.invoice_date
      )
      
    ORDER BY date DESC, id DESC
";
$all_transactions = $conn->query($query);

// Totals
$total_income = $conn->query("
    SELECT SUM(amount) AS total 
    FROM (
        SELECT amount, transaction_date FROM transactions WHERE type='income'
        UNION ALL
        SELECT i.amount, i.invoice_date FROM invoices i WHERE i.status='paid'
          AND NOT EXISTS (
              SELECT 1 FROM transactions t
              WHERE t.description = CONCAT('Invoice #', i.id)
                AND t.amount = i.amount
                AND t.transaction_date = i.invoice_date
          )
    ) as incomes
    WHERE DATE_FORMAT(transaction_date, '%Y-%m') = '{$monthFilter}'
")->fetch_assoc()['total'] ?? 0;

$total_expense = $conn->query("
    SELECT SUM(amount) AS total 
    FROM (
        SELECT amount, transaction_date FROM transactions WHERE type='expense'
        UNION ALL
        SELECT e.amount, e.expense_date FROM expenses e
          WHERE NOT EXISTS (
              SELECT 1 FROM transactions t
              WHERE t.description = e.description
                AND t.amount = e.amount
                AND t.transaction_date = e.expense_date
          )
    ) as expenses
    WHERE DATE_FORMAT(transaction_date, '%Y-%m') = '{$monthFilter}'
")->fetch_assoc()['total'] ?? 0;

$current_balance = $total_income - $total_expense;

// Get distinct months for filter dropdown
$months = $conn->query("
    SELECT DISTINCT DATE_FORMAT(transaction_date, '%Y-%m') AS month FROM transactions
    UNION
    SELECT DISTINCT DATE_FORMAT(expense_date, '%Y-%m') AS month FROM expenses
    UNION
    SELECT DISTINCT DATE_FORMAT(invoice_date, '%Y-%m') AS month FROM invoices
    ORDER BY month DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Transactions</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600&family=Urbanist:wght@400;600&display=swap" rel="stylesheet">
<style>
body { font-family: 'Urbanist', sans-serif; background:#f4f7fb; padding:20px; margin:0; }
.container { background:white; padding:30px; border-radius:16px; max-width:1100px; margin:auto; box-shadow:0 6px 18px rgba(0,0,0,0.08);}
h1 { font-family:'Space Grotesk', sans-serif; display:flex; justify-content:space-between; align-items:center; color:#003366; margin-bottom:20px;}
.add-btn { background:#003366; color:white; padding:8px 16px; border-radius:8px; font-size:14px; text-decoration:none; font-weight:600; transition:0.3s;}
.add-btn:hover { background:#001f4d; }
.balance { font-size:18px; font-weight:600; margin:15px 0; padding:10px 15px; border-radius:8px;}
.balance.negative { background:#ffe5e5; color:#b91c1c;}
.balance.positive { background:#e6ffed; color:#16a34a;}
.filter-form { margin-bottom:20px; }
.filter-form select { padding:6px 10px; border-radius:6px; font-size:14px; }
table { width:100%; border-collapse:collapse; margin-top:10px; font-size:14px;}
th,td { border:1px solid #ddd; padding:12px 10px; text-align:left;}
th { background:#003366; color:white; font-family:'Space Grotesk', sans-serif;}
tbody tr:hover { background:#f9fafc; }
</style>
</head>
<body>
<div class="container">
    <h1>
        Transactions
        <a href="add_transaction.php" class="add-btn">+ Add Transaction</a>
    </h1>

    <form method="get" class="filter-form">
        <label for="month">Filter by Month:</label>
        <select name="month" id="month" onchange="this.form.submit()">
            <?php while($m = $months->fetch_assoc()): ?>
                <option value="<?= $m['month'] ?>" <?= $monthFilter == $m['month'] ? 'selected' : '' ?>>
                    <?= date('F Y', strtotime($m['month'].'-01')) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>

    <div class="balance <?= $current_balance < 0 ? 'negative' : 'positive' ?>">
        Current Balance: R <?= number_format($current_balance,2) ?>
    </div>

    <table>
    <thead>
    <tr>
        <th>#</th>
        <th>Date</th>
        <th>Description</th>
        <th>Type</th>
        <th>Amount (R)</th>
    </tr>
    </thead>
    <tbody>
    <?php $i=1; while($t=$all_transactions->fetch_assoc()): ?>
    <tr>
        <td><?= $i++ ?></td>
        <td><?= htmlspecialchars($t['date']) ?></td>
        <td><?= htmlspecialchars($t['description']) ?></td>
        <td><?= ucfirst($t['type']) ?></td>
        <td><?= number_format($t['amount'],2) ?></td>
    </tr>
    <?php endwhile; ?>
    </tbody>
    </table>
</div>
</body>
</html>
