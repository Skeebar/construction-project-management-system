<?php
require 'db.php';
include 'nav.php';

$projectId = intval($_GET['project_id'] ?? 0);
$project = null;
$client = null;

if ($projectId) {
    // Fetch project
    $stmt = $conn->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->bind_param("i", $projectId);
    $stmt->execute();
    $project = $stmt->get_result()->fetch_assoc();

    if ($project) {
        // Try match client
        $stmt2 = $conn->prepare("SELECT * FROM clients WHERE name = ? LIMIT 1");
        $stmt2->bind_param("s", $project['client_name']);
        $stmt2->execute();
        $client = $stmt2->get_result()->fetch_assoc();
    }
}

// Generate next invoice number
$invoiceNumberRes = $conn->query("SELECT COUNT(*) AS total FROM invoices");
$invoiceCount = $invoiceNumberRes->fetch_assoc()['total'] ?? 0;
$newInvoiceNumber = str_pad($invoiceCount + 1, 4, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Invoice</title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600&family=Urbanist:wght@600&display=swap" rel="stylesheet">
    <style>
        body {font-family:'Space Grotesk',sans-serif; background:#f4f7fb; margin:0; padding:30px;}
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
            <td><input type="number" name="quantity[]" step="0.01" value="1" required></td>
            <td><input type="number" name="unit_price[]" step="0.01" required></td>
        `;
        table.appendChild(newRow);
    }
    </script>
</head>
<body>
<div class="container">
    <div class="nav">
        <a href="project_list.php">← Back to Projects</a>
        <a href="invoice_list.php">Invoices</a>
    </div>

    <h1>Create Invoice <?= $project ? "for Project: " . htmlspecialchars($project['name']) : "" ?></h1>

    <form method="POST" action="save_invoice.php">
        <input type="hidden" name="project_id" value="<?= $projectId ?>">
        <input type="hidden" name="client_id" value="<?= $client['id'] ?? '' ?>">

        <label>Invoice Number</label>
        <input type="text" name="invoice_number" value="<?= $newInvoiceNumber ?>" readonly>

        <label>Invoice Date</label>
        <input type="date" name="invoice_date" value="<?= date('Y-m-d') ?>" required>

        <label>Client</label>
        <input type="text" name="client_name" value="<?= $client['name'] ?? ($project['client_name'] ?? '') ?>" readonly>

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
                    <td><input type="text" name="item[]" value="<?= htmlspecialchars($project['description'] ?? '') ?>" required></td>
                    <td><input type="number" name="quantity[]" value="1" step="0.01" required></td>
                    <td><input type="number" name="unit_price[]" value="<?= htmlspecialchars($project['quoted_amount'] ?? '0') ?>" step="0.01" required></td>
                </tr>
            </tbody>
        </table>
        <button type="button" class="add-btn" onclick="addRow()">+ Add Item</button>

        <label>Discount (%)</label>
        <input type="number" name="discount" value="0" step="0.01">

        <label>Tax (%)</label>
        <input type="number" name="tax" value="15" step="0.01">

        <label>Notes</label>
        <textarea name="notes" rows="4"></textarea>

        <button type="submit" class="save-btn">💾 Save Invoice</button>
    </form>
</div>
</body>
</html>
