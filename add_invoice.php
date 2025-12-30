<?php
require 'db.php';

// Fetch clients for dropdown
$clients = $conn->query("SELECT id, name FROM clients ORDER BY name ASC");

// Generate next invoice number
$invoiceNumberRes = $conn->query("SELECT COUNT(*) AS total FROM invoices");
$invoiceCount = $invoiceNumberRes->fetch_assoc()['total'] ?? 0;
$newInvoiceNumber = str_pad($invoiceCount + 1, 4, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add New Invoice</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600&family=Urbanist:wght@600&display=swap" rel="stylesheet">
<style>
body {font-family:'Space Grotesk',sans-serif; background:#f4f7fb; margin:0; padding:20px;}
.container {max-width:900px; margin:auto; background:#fff; padding:30px; border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,0.08);}
h1 {font-family:'Urbanist',sans-serif; margin-bottom:20px; color:#1e3a8a;}
label {display:block; margin-top:15px; font-weight:600;}
input, textarea, select {width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; margin-top:5px;}
table {width:100%; margin-top:20px; border-collapse:collapse;}
th, td {border:1px solid #ddd; padding:10px;}
th {background:#1e3a8a; color:white;}
button {padding:12px 20px; margin-top:20px; border:none; border-radius:8px; cursor:pointer; font-weight:600;}
.save-btn {background:#1e3a8a; color:white;}
.save-btn:hover {background:#14356a;}
.add-btn {background:#22c55e; color:white; margin-top:10px;}
.add-btn:hover {background:#16a34a;}
.nav {margin-bottom:20px;}
.nav a {margin-right:15px; text-decoration:none; font-weight:600; color:#1e3a8a;}
</style>
<script>
function addRow() {
    const table = document.getElementById("itemsTable").querySelector("tbody");
    const newRow = document.createElement("tr");
    newRow.innerHTML = `
        <td><input type="text" name="item[]" required></td>
        <td><input type="number" name="quantity[]" step="0.01" required></td>
        <td><input type="number" name="unit_price[]" step="0.01" required></td>
    `;
    table.appendChild(newRow);
}
</script>
</head>
<body>
<div class="container">
    <div class="nav">
        <a href="invoice_list.php">← Back to Invoices</a>
    </div>

    <h1>Add New Invoice</h1>

    <form method="POST" action="save_invoice.php">
        <label>Invoice Number</label>
        <input type="text" name="invoice_number" value="<?= $newInvoiceNumber ?>" readonly>

        <label>Invoice Date</label>
        <input type="date" name="invoice_date" value="<?= date('Y-m-d') ?>" required>

        <label>Client</label>
        <select name="client_id" required>
            <option value="">-- Select Client --</option>
            <?php while ($c = $clients->fetch_assoc()): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
            <?php endwhile; ?>
        </select>

        <label>Status</label>
        <select name="status">
            <option value="Unpaid">Unpaid</option>
            <option value="Paid">Paid</option>
            <option value="Overdue">Overdue</option>
        </select>

        <h3>Invoice Items</h3>
        <table id="itemsTable">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th>Unit Price (R)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="text" name="item[]" required></td>
                    <td><input type="number" name="quantity[]" step="0.01" required></td>
                    <td><input type="number" name="unit_price[]" step="0.01" required></td>
                </tr>
            </tbody>
        </table>
        <button type="button" class="add-btn" onclick="addRow()">+ Add Item</button>

        <label>Discount (R)</label>
        <input type="number" step="0.01" name="discount" value="0">

        <label>Tax (%)</label>
        <input type="number" step="0.01" name="tax" value="0">

        <label>Notes</label>
        <textarea name="notes" rows="4"></textarea>

        <button type="submit" class="save-btn">💾 Save Invoice</button>
    </form>
</div>
</body>
</html>
