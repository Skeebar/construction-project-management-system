<?php
require 'db.php';

// Handle form submission
$successMsg = '';
$errorMsg = '';
$sendEmail = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clientName = trim($_POST['client_name'] ?? '');
    $quotationNumber = trim($_POST['quotation_number'] ?? '');
    $quotationDate = $_POST['quotation_date'] ?? '';
    $status = $_POST['status'] ?? 'Pending';
    $notes = trim($_POST['notes'] ?? '');
    $sendEmail = isset($_POST['send_email']); // Save & email button clicked

    // Line items
    $items = $_POST['item'] ?? [];
    $qty = $_POST['quantity'] ?? [];
    $unitPrice = $_POST['unit_price'] ?? [];

    if (!$clientName || !$quotationNumber || !$quotationDate || empty($items)) {
        $errorMsg = "Please fill in all required fields and add at least one item.";
    } else {
        $conn->begin_transaction();
        try {
            // Calculate total
            $total = 0;
            for ($i = 0; $i < count($items); $i++) {
                $quantity = floatval($qty[$i]);
                $price = floatval($unitPrice[$i]);
                if ($quantity > 0 && $price > 0) {
                    $total += $quantity * $price;
                }
            }

            // Apply tax (e.g., 15%)
            $taxRate = 0.15;
            $taxAmount = $total * $taxRate;
            $totalAmount = $total + $taxAmount;

            // Insert quotation with total_amount
            $stmt = $conn->prepare("INSERT INTO quotations (client_name, quotation_number, quotation_date, status, notes, total_amount) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssd", $clientName, $quotationNumber, $quotationDate, $status, $notes, $totalAmount);
            $stmt->execute();
            $quotationId = $stmt->insert_id;
            $stmt->close();

            // Insert line items
            $stmt = $conn->prepare("INSERT INTO quotation_items (quotation_id, description, quantity, unit_price) VALUES (?, ?, ?, ?)");
            for ($i = 0; $i < count($items); $i++) {
                $description = $items[$i];
                $quantity = floatval($qty[$i]);
                $price = floatval($unitPrice[$i]);
                if ($description && $quantity > 0 && $price > 0) {
                    $stmt->bind_param("isdd", $quotationId, $description, $quantity, $price);
                    $stmt->execute();
                }
            }
            $stmt->close();
            $conn->commit();
            $successMsg = "Quotation added successfully! Total: R " . number_format($totalAmount, 2);

            // Send email if requested
            if ($sendEmail) {
                $clientRes = $conn->query("SELECT email FROM clients WHERE name='".$conn->real_escape_string($clientName)."' LIMIT 1");
                $clientEmail = $clientRes->fetch_assoc()['email'] ?? '';
                if ($clientEmail) {
                    $subject = "New Quotation: $quotationNumber";
                    $message = "Dear $clientName,\n\nA new quotation ($quotationNumber) has been created for you.\nTotal Amount (incl. tax): R " . number_format($totalAmount,2) . "\n\nThank you.";
                    $headers = "From: no-reply@yourcompany.com";
                    mail($clientEmail, $subject, $message, $headers);
                    $successMsg .= " Email sent to client.";
                }
            }
        } catch (Exception $e) {
            $conn->rollback();
            $errorMsg = "Database error: " . $e->getMessage();
        }
    }
}


// Fetch clients for dropdown
$clientsRes = $conn->query("SELECT id, name FROM clients ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Quotation</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600;700&family=Urbanist:wght@400;600;700&display=swap" rel="stylesheet">
<style>
body { font-family:'Space Grotesk',sans-serif; background:#f4f7fb; margin:0; padding:0; }
.container { max-width:800px; margin:50px auto; background:#fff; padding:30px; border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,0.08); }
h1 { font-family:'Urbanist',sans-serif; text-align:center; color:#1e3a8a; margin-bottom:25px; }
form label { display:block; margin:10px 0 4px; font-weight:600; }
form input, form select, form textarea { width:100%; padding:10px 12px; border:1px solid #ccc; border-radius:8px; font-size:14px; }
form textarea { resize: vertical; }
.form-section { margin-bottom:25px; }
.line-items { margin-top:10px; }
.line-items table { width:100%; border-collapse: collapse; }
.line-items th, .line-items td { padding:10px; border:1px solid #ddd; text-align:left; }
.line-items input { width:100%; border:none; padding:6px; font-size:14px; }
.line-items th { background:#f1f5f9; }
.add-row-btn { margin-top:10px; padding:8px 14px; background:#22c55e; color:white; border:none; border-radius:6px; cursor:pointer; font-weight:600; }
.add-row-btn:hover { background:#16a34a; }
.buttons { display:flex; gap:10px; margin-top:20px; flex-wrap:wrap; }
.buttons button, .buttons a { flex:1; padding:12px; border:none; border-radius:8px; font-weight:700; cursor:pointer; text-align:center; text-decoration:none; }
.buttons .back-btn { background:#6b7280; color:white; }
.buttons .back-btn:hover { background:#4b5563; }
.buttons .save-btn { background:#1e3a8a; color:white; }
.buttons .save-btn:hover { background:#14356a; }
.buttons .email-btn { background:#22c55e; color:white; }
.buttons .email-btn:hover { background:#16a34a; }
.success { background:#d1fae5; color:#065f46; padding:10px; border-radius:6px; margin-bottom:15px; }
.error { background:#fee2e2; color:#b91c1c; padding:10px; border-radius:6px; margin-bottom:15px; }
</style>
</head>
<body>

<div class="container">
<h1>Create New Quotation</h1>

<?php if($successMsg): ?>
<div class="success"><?= htmlspecialchars($successMsg) ?></div>
<?php endif; ?>
<?php if($errorMsg): ?>
<div class="error"><?= htmlspecialchars($errorMsg) ?></div>
<?php endif; ?>

<form method="POST" action="">
<div class="form-section">
    <label for="client_name">Client</label>
    <select name="client_name" id="client_name" required>
        <option value="">-- Select Client --</option>
        <?php while($client = $clientsRes->fetch_assoc()): ?>
            <option value="<?= htmlspecialchars($client['name']) ?>"><?= htmlspecialchars($client['name']) ?></option>
        <?php endwhile; ?>
    </select>

    <label for="quotation_number">Quotation Number</label>
    <input type="text" name="quotation_number" id="quotation_number" required>

    <label for="quotation_date">Quotation Date</label>
    <input type="date" name="quotation_date" id="quotation_date" required>

    <label for="status">Status</label>
    <select name="status" id="status">
        <option value="Pending">Pending</option>
        <option value="Accepted">Accepted</option>
        <option value="Rejected">Rejected</option>
    </select>
</div>

<div class="form-section line-items">
    <h3>Line Items</h3>
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
    <button type="button" class="add-row-btn" onclick="addRow()">+ Add Item</button>
</div>

<div class="form-section">
    <label for="notes">Notes / Additional Information</label>
    <textarea name="notes" id="notes" rows="4"></textarea>
</div>

<div class="buttons">
    <a href="quotations.php" class="back-btn">← Go Back to Quotations</a>
    <button type="submit" class="save-btn" name="save_only">💾 Save Only</button>
    <button type="submit" class="email-btn" name="send_email">📧 Save & Email to Client</button>
</div>
</form>
</div>

<script>
function addRow() {
    const table = document.getElementById('itemsTable').querySelector('tbody');
    const newRow = document.createElement('tr');
    newRow.innerHTML = `<td><input type="text" name="item[]" required></td>
                        <td><input type="number" name="quantity[]" step="0.01" required></td>
                        <td><input type="number" name="unit_price[]" step="0.01" required></td>`;
    table.appendChild(newRow);
}
</script>

</body>
</html>
