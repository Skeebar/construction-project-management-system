<?php
require 'db.php';

if (!isset($_GET['id'])) {
    die("Invoice ID is required.");
}

$invoice_id = intval($_GET['id']);

// Fetch invoice
$invResult = $conn->query("SELECT i.*, c.name AS client_name, c.email AS client_email 
                           FROM invoices i 
                           LEFT JOIN clients c ON i.client_id = c.id 
                           WHERE i.id = $invoice_id");

if (!$invResult || $invResult->num_rows === 0) {
    die("Invoice not found.");
}

$invoice = $invResult->fetch_assoc();

// Fetch invoice items
$itemResult = $conn->query("SELECT * FROM invoice_items WHERE invoice_id = $invoice_id");
$items = [];
while ($row = $itemResult->fetch_assoc()) {
    $items[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Invoice #<?= htmlspecialchars($invoice['invoice_number']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600&family=Urbanist:wght@600&display=swap" rel="stylesheet">
<style>
body {font-family:'Space Grotesk',sans-serif; background:#f4f7fb; margin:0; padding:20px;}
.container {max-width:900px; margin:auto; background:#fff; padding:30px; border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,0.08);}
h1 {font-family:'Urbanist',sans-serif; margin-bottom:20px; color:#1e3a8a;}
.nav {margin-bottom:20px;}
.nav a {margin-right:15px; text-decoration:none; font-weight:600; color:#1e3a8a;}
table {width:100%; border-collapse:collapse; margin-top:20px;}
th, td {border:1px solid #ddd; padding:10px;}
th {background:#1e3a8a; color:white;}
.total-row td {font-weight:700;}
.notes {margin-top:20px; padding:15px; background:#f1f5f9; border-radius:8px;}
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
        <a href="invoice_list.php">Invoices</a>
        <a href="edit_invoice.php?id=<?= $invoice['id'] ?>">Edit Invoice</a>
    </div>

    <h1>Invoice #<?= htmlspecialchars($invoice['invoice_number']) ?></h1>
    <p><strong>Date:</strong> <?= $invoice['invoice_date'] ?> | <strong>Status:</strong> 
       <span class="status <?= htmlspecialchars($invoice['status']) ?>"><?= htmlspecialchars($invoice['status']) ?></span></p>
    <p><strong>Client:</strong> <?= htmlspecialchars($invoice['client_name']) ?> (<?= htmlspecialchars($invoice['client_email']) ?>)</p>

    <h3>Items</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Description</th>
                <th>Quantity</th>
                <th>Unit Price (R)</th>
                <th>Total (R)</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $i=1; 
            foreach($items as $it): 
            ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($it['description']) ?></td>
                <td><?= number_format($it['quantity'],2) ?></td>
                <td><?= number_format($it['unit_price'],2) ?></td>
                <td><?= number_format($it['total'],2) ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="total-row">
                <td colspan="4" style="text-align:right;">Subtotal</td>
                <td><?= number_format($invoice['subtotal'],2) ?></td>
            </tr>
            <tr class="total-row">
                <td colspan="4" style="text-align:right;">Discount</td>
                <td><?= number_format($invoice['discount'],2) ?></td>
            </tr>
            <tr class="total-row">
                <td colspan="4" style="text-align:right;">Tax</td>
                <td><?= number_format($invoice['tax'],2) ?></td>
            </tr>
            <tr class="total-row">
                <td colspan="4" style="text-align:right;">Total Amount</td>
                <td><?= number_format($invoice['amount'],2) ?></td>
            </tr>
        </tbody>
    </table>

    <?php if(!empty($invoice['notes'])): ?>
        <div class="notes">
            <strong>Notes:</strong>
            <p><?= nl2br(htmlspecialchars($invoice['notes'])) ?></p>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
