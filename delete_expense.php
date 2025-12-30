<?php
require 'db.php';
include 'nav.php';

$expenseId = intval($_GET['id'] ?? 0);

if (!$expenseId) {
    die("Expense ID missing.");
}

// Optional: fetch expense to display before deleting
$expense = $conn->query("SELECT * FROM expenses WHERE id = $expenseId")->fetch_assoc();
if (!$expense) {
    die("Expense not found.");
}

// Delete confirmation via GET param
if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
    $stmt = $conn->prepare("DELETE FROM expenses WHERE id = ?");
    $stmt->bind_param("i", $expenseId);
    if ($stmt->execute()) {
        header("Location: expenses.php?deleted=1");
        exit;
    } else {
        die("Error deleting expense: " . $stmt->error);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Delete Expense</title>
<style>
body { font-family: 'Space Grotesk', sans-serif; background:#f4f7fb; margin:0; padding:20px;}
.container { max-width: 600px; margin:auto; background:#fff; padding:30px; border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,0.08);}
h1 { color:#cc0000; margin-bottom:20px; }
button, a { padding:10px 18px; border-radius:6px; text-decoration:none; font-weight:600; margin-right:10px; }
button { border:none; cursor:pointer; background:#cc0000; color:white; }
button:hover { background:#990000; }
a.cancel { background:#ccc; color:#333; }
a.cancel:hover { background:#999; }
</style>
</head>
<body>
<div class="container">
<h1>Delete Expense</h1>
<p>Are you sure you want to delete the following expense?</p>
<ul>
    <li><strong>Description:</strong> <?= htmlspecialchars($expense['description']) ?></li>
    <li><strong>Amount:</strong> R<?= number_format($expense['amount'],2) ?></li>
    <li><strong>Date:</strong> <?= htmlspecialchars($expense['expense_date']) ?></li>
    <li><strong>Category:</strong> <?= htmlspecialchars($expense['category']) ?></li>
</ul>
<form method="get">
    <input type="hidden" name="id" value="<?= $expenseId ?>">
    <button type="submit" name="confirm" value="yes">Yes, Delete</button>
    <a href="expenses_list.php" class="cancel">Cancel</a>
</form>
</div>
</body>
</html>
