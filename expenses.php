<?php
require 'db.php';
include 'nav.php';

// Calculate current balance directly from transactions table
$total_income = $conn->query("SELECT SUM(amount) AS total FROM transactions WHERE type='income'")
                    ->fetch_assoc()['total'] ?? 0;

$total_expense = $conn->query("SELECT SUM(amount) AS total FROM transactions WHERE type='expense'")
                     ->fetch_assoc()['total'] ?? 0;

$current_balance = $total_income - $total_expense;

// Fetch expenses
$expenses = $conn->query("SELECT * FROM expenses ORDER BY expense_date DESC, id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Expenses</title>
    <style>
        body { font-family: 'Space Grotesk', 'Urbanist', sans-serif; background:#f4f7fb; padding:20px; }
        .container { background:white; padding:20px; border-radius:12px; max-width:1000px; margin:auto; }
        h1 { display:flex; justify-content:space-between; align-items:center; }
        .balance { font-size:18px; font-weight:bold; margin:15px 0; }
        .balance.negative { color:red; }
        .balance.positive { color:green; }
        table { width:100%; border-collapse:collapse; margin-top:20px; }
        th,td { border:1px solid #ddd; padding:10px; font-size:14px; }
        th { background:#003366; color:white; }
        .btn { padding:8px 14px; border:none; border-radius:8px; cursor:pointer; font-family:inherit; }
        .btn-back { background:#003366; color:white; text-decoration:none; }
    </style>
</head>
<body>
<div class="container">
    <h1>Expenses</h1>

   

    <a href="add_expense.php" class="btn btn-back">+ Add Expense</a>
    <a href="transactions.php" class="btn btn-back">← Back to Transactions</a>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Description</th>
                <th>Category</th>
                <th>Amount (R)</th>
            </tr>
        </thead>
        <tbody>
            <?php $i=1; while($e=$expenses->fetch_assoc()): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($e['expense_date']) ?></td>
                <td><?= htmlspecialchars($e['description']) ?></td>
                <td><?= htmlspecialchars($e['category']) ?></td>
                <td><?= number_format($e['amount'],2) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
