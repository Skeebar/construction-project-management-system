<?php
require 'db.php';
include 'nav.php';

$invoiceId = intval($_GET['id'] ?? 0);
if (!$invoiceId) {
    die("Invoice ID missing.");
}

// Fetch invoice
$stmt = $conn->prepare("SELECT * FROM invoices WHERE id = ?");
$stmt->bind_param("i", $invoiceId);
$stmt->execute();
$invoice = $stmt->get_result()->fetch_assoc();
if (!$invoice) die("Invoice not found.");

// Fetch client info
$client = $conn->query("SELECT * FROM clients WHERE id = " . intval($invoice['client_id']))->fetch_assoc();

// Fetch invoice items
$items = $conn->query("SELECT * FROM invoice_items WHERE invoice_id = $invoiceId");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $invoice_number = $_POST['invoice_number'];
    $invoice_date = $_POST['invoice_date'];
    $status = $_POST['status'];
    $notes = $_POST['notes'] ?? '';
    $discount = floatval($_POST['discount'] ?? 0);
    $tax = floatval($_POST['tax'] ?? 15);

    // Update invoice table
    $stmt = $conn->prepare("UPDATE invoices SET invoice_number=?, invoice_date=?, status=?, notes=?, discount=?, tax=? WHERE id=?");
    $stmt->bind_param("sssdddi", $invoice_number, $invoice_date, $status, $notes, $discount, $tax, $invoiceId);
    $stmt->execute();

    // Delete existing items
    $conn->query("DELETE FROM invoice_items WHERE invoice_id=$invoiceId");

    // Insert updated items
    $descriptions = $_POST['item'];
    $quantities = $_POST['quantity'];
    $unit_prices = $_POST['unit_price'];

    $stmt_item = $conn->prepare("INSERT INTO invoice_items (invoice_id, description, quantity, unit_price) VALUES (?, ?, ?, ?)");
    foreach ($descriptions as $i => $desc) {
        $qty = floatval($quantities[$i]);
        $price = floatval($unit_prices[$i]);
        $stmt_item->bind_param("isdd", $invoiceId, $desc, $qty, $price);
        $stmt_item->execute();
    }

    header("Location: view_invoice.php?id=$invoiceId");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Invoice <?= htmlspecialchars($invoice['invoice_number']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600&family=Urbanist:wght@600&display=swap" rel="stylesheet">
<style>
body {font-family:'Space Grotesk',sans-serif; background:#f4f7fb; margin:30px;}
.container {max-width:950px; margin:auto; background:#fff; padding:30px; border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,0.08);}
h1 {font-family:'Urbanist',sans-serif; color:#003366; margin-bottom:20px;}
label {display:block; margin-top:15px; font-weight:600;}
input, textarea, select {width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; margin-top:5px;}
table {width:100%; margin-top:20px; border-collapse:collapse;}
th, td {border:1px solid #ddd; padding:10px;}
th {background:#003366; color:white;}
button {padding:12px 20px; margin-top:20px; border:none; border-radius:8px; cursor:pointer; font-weight:600;}
.save-btn {background:#003366; color:white;}
.save-btn:hover {background:#001f4d;}
.add-btn {background:#22c55e; color:white; margin-top:10px;}
.add-btn:hover {background:#16a34a;}
.nav {margin-bottom:20px;}
.nav a {margin-right:15px; text-decoration:none; font-weight:600; color:#003366;}
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
    <a href="invoices.php">← Back to Invoices</a>
</div>

<h1>Edit Invoice: <?= htmlspecialchars($invoice['invoice_number']) ?></h1>

<form method="POST">
    <label>Invoice Number</label>
    <input type="text" name="invoice_number" value="<?= htmlspecialchars($invoice['invoice_number']) ?>" required>

    <label>Invoice Date</label>
    <input type="date" name="invoice_date" value="<?= htmlspecialchars($invoice['invoice_date']) ?>" required>

    <label>Client</label>
    <input type="text" value="<?= htmlspecialchars($client['name']) ?>" readonly>

    <label>Status</label>
    <select name="status">
        <option value="Unpaid" <?= $invoice['status']=='Unpaid'?'selected':'' ?>>Unpaid</option>
        <option value="Paid" <?= $invoice['status']=='Paid'?'selected':'' ?>>Paid</option>
        <option value="Overdue" <?= $invoice['status']=='Overdue'?'selected':'' ?>>Overdue</option>
    </select>

    <label>Notes</label>
    <textarea name="notes" rows="4"><?= htmlspecialchars($invoice['notes']) ?></textarea>

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
        <?php while($item = $items->fetch_assoc()): ?>
            <tr>
                <td><input type="text" name="item[]" value="<?= htmlspecialchars($item['description']) ?>" required></td>
                <td><input type="number" name="quantity[]" value="<?= $item['quantity'] ?>" step="0.01" required></td>
                <td><input type="number" name="unit_price[]" value="<?= $item['unit_price'] ?>" step="0.01" required></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <button type="button" class="add-btn" onclick="addRow()">+ Add Item</button>

    <label>Discount (%)</label>
    <input type="number" name="discount" value="<?= htmlspecialchars($invoice['discount']) ?>" step="0.01">

    <label>Tax (%)</label>
    <input type="number" name="tax" value="<?= htmlspecialchars($invoice['tax']) ?>" step="0.01">

    <button type="submit" class="save-btn">💾 Save Changes</button>
</form>
</div>
</body>
</html>
