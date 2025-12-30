<?php
require 'db.php';

// Fetch invoices with client info
$sql = "SELECT i.*, c.name AS client_name 
        FROM invoices i 
        LEFT JOIN clients c ON i.client_id = c.id 
        ORDER BY i.created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Invoices</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600&family=Urbanist:wght@600&display=swap" rel="stylesheet">
<style>
body {font-family:'Space Grotesk',sans-serif; background:#f4f7fb; margin:0; padding:20px;}
.container {max-width:1000px; margin:auto; background:#fff; padding:25px; border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,0.08);}
h1 {font-family:'Urbanist',sans-serif; margin-bottom:20px; color:#1e3a8a;}
.nav {margin-bottom:20px;}
.nav a {margin-right:15px; text-decoration:none; font-weight:600; color:#1e3a8a;}
table {width:100%; border-collapse:collapse; margin-top:10px;}
th, td {padding:12px; border-bottom:1px solid #e5e7eb; text-align:left;}
th {background:#1e3a8a; color:white;}
tr:hover {background:#f9fafb;}
.actions a {margin-right:10px; text-decoration:none; font-size:14px; font-weight:600;}
.view {color:#2563eb;}
.edit {color:#f59e0b;}
.delete {color:#ef4444;}
.status {font-weight:700; padding:5px 10px; border-radius:6px; color:white;}
.status.Unpaid {background:#ef4444;}
.status.Paid {background:#10b981;}
.status.Overdue {background:#f59e0b;}
</style>
</head>
<body>
<div class="container">
    <div class="nav">
        <a href="dashboard.php">← Dashboard</a>
        <a href="add_invoice.php">+ New Invoice</a>
    </div>

    <h1>Invoices</h1>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Client</th>
                <th>Date</th>
                <th>Status</th>
                <th>Total (R)</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['invoice_number']) ?></td>
                <td><?= htmlspecialchars($row['client_name'] ?? 'Unknown') ?></td>
                <td><?= htmlspecialchars($row['invoice_date']) ?></td>
                <td><span class="status <?= htmlspecialchars($row['status']) ?>"><?= htmlspecialchars($row['status']) ?></span></td>
                <td><?= number_format($row['amount'], 2) ?></td>
                <td class="actions">
                    <a class="view" href="view_invoice.php?id=<?= $row['id'] ?>">View</a>
                    <a class="edit" href="edit_invoice.php?id=<?= $row['id'] ?>">Edit</a>
                    <a class="delete" href="delete_invoice.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this invoice?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">No invoices found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
