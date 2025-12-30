<?php
require 'db.php';

if (!isset($_GET['id'])) {
    die("Quotation ID is required.");
}

$quotation_id = intval($_GET['id']);

// Fetch quotation
$qResult = $conn->query("SELECT * FROM quotations WHERE id = $quotation_id");
if (!$qResult || $qResult->num_rows === 0) {
    die("Quotation not found.");
}
$quotation = $qResult->fetch_assoc();

// 🔑 Lookup client_id using client_name
$client_id = null;
if (!empty($quotation['client_name'])) {
    $stmtClient = $conn->prepare("SELECT id, email FROM clients WHERE name = ? LIMIT 1");
    $stmtClient->bind_param("s", $quotation['client_name']);
    $stmtClient->execute();
    $stmtClient->bind_result($client_id, $client_email);
    $stmtClient->fetch();
    $stmtClient->close();
}

// Fetch quotation items
$itemsResult = $conn->query("SELECT * FROM quotation_items WHERE quotation_id = $quotation_id");
$items = [];
while ($row = $itemsResult->fetch_assoc()) {
    $items[] = $row;
}

// Generate new invoice number (not saved yet)
$invoiceNumberRes = $conn->query("SELECT COUNT(*) AS total FROM invoices");
$invoiceCount = $invoiceNumberRes->fetch_assoc()['total'] ?? 0;
$newInvoiceNumber = str_pad($invoiceCount + 1, 4, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Convert Quotation to Invoice</title>
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
        <a href="dashboard.php">← Back to Dashboard</a>
        <a href="quotations.php">Quotations</a>
        <a href="invoices.php">Invoices</a>
    </div>

    <h1>Convert Quotation #<?= htmlspecialchars($quotation['quotation_number']) ?> to Invoice</h1>

    <form method="POST" action="save_invoice.php">
        <input type="hidden" name="quotation_id" value="<?= $quotation['id'] ?>">
        <input type="hidden" name="client_id" value="<?= $client_id ?>">

        <label>Invoice Number</label>
        <input type="text" name="invoice_number" value="<?= $newInvoiceNumber ?>" readonly>

        <label>Invoice Date</label>
        <input type="date" name="invoice_date" value="<?= date('Y-m-d') ?>" required>

        <label>Client</label>
        <input type="text" name="client_name" value="<?= htmlspecialchars($quotation['client_name']) ?>" readonly>

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
                <?php foreach($items as $it): ?>
                <tr>
                    <td><input type="text" name="item[]" value="<?= htmlspecialchars($it['description']) ?>" required></td>
                    <td><input type="number" name="quantity[]" value="<?= $it['quantity'] ?>" step="0.01" required></td>
                    <td><input type="number" name="unit_price[]" value="<?= $it['unit_price'] ?>" step="0.01" required></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button type="button" class="add-btn" onclick="addRow()">+ Add Item</button>

        <label>Discount (%)</label>
        <input type="number" name="discount" value="0" step="0.01">

        <label>Tax (%)</label>
        <input type="number" name="tax" value="15" step="0.01">

        <label>Notes</label>
        <textarea name="notes" rows="4"><?= htmlspecialchars($quotation['notes']) ?></textarea>

        <button type="submit" class="save-btn">💾 Save Invoice</button>
    </form>
</div>
</body>
</html>
