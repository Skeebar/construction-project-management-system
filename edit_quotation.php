<?php
require 'db.php';

$quotationId = $_GET['id'] ?? null;
if (!$quotationId) die("No quotation selected.");

// Fetch quotation
$stmt = $conn->prepare("SELECT * FROM quotations WHERE id = ?");
$stmt->bind_param("i", $quotationId);
$stmt->execute();
$result = $stmt->get_result();
$quotation = $result->fetch_assoc();
if (!$quotation) die("Quotation not found.");

// Fetch line items
$stmt = $conn->prepare("SELECT * FROM quotation_items WHERE quotation_id = ?");
$stmt->bind_param("i", $quotationId);
$stmt->execute();
$itemsRes = $stmt->get_result();
$lineItems = [];
while ($row = $itemsRes->fetch_assoc()) $lineItems[] = $row;
$stmt->close();

$successMsg = $errorMsg = '';
$sendEmail = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clientName = trim($_POST['client_name'] ?? '');
    $quotationNumber = trim($_POST['quotation_number'] ?? '');
    $quotationDate = $_POST['quotation_date'] ?? '';
    $status = $_POST['status'] ?? 'Pending';
    $notes = trim($_POST['notes'] ?? '');
    $sendEmail = isset($_POST['send_email']);

    $items = $_POST['item'] ?? [];
    $qty = $_POST['quantity'] ?? [];
    $unitPrice = $_POST['unit_price'] ?? [];

    if (!$clientName || !$quotationNumber || !$quotationDate || empty($items)) {
        $errorMsg = "Please fill in all required fields and add at least one item.";
    } else {
        $conn->begin_transaction();
        try {
            // Calculate total_amount
            $totalAmount = 0;
            for ($i = 0; $i < count($items); $i++) {
                $totalAmount += floatval($qty[$i]) * floatval($unitPrice[$i]);
            }
            $tax = $totalAmount * 0.15; // Example 15% tax
            $totalAmountWithTax = $totalAmount + $tax;

            // Update quotation
            $stmt = $conn->prepare("UPDATE quotations SET client_name=?, quotation_number=?, quotation_date=?, status=?, notes=?, total_amount=? WHERE id=?");
            $stmt->bind_param("sssssdi", $clientName, $quotationNumber, $quotationDate, $status, $notes, $totalAmountWithTax, $quotationId);
            $stmt->execute();
            $stmt->close();

            // Delete old line items
            $stmt = $conn->prepare("DELETE FROM quotation_items WHERE quotation_id=?");
            $stmt->bind_param("i", $quotationId);
            $stmt->execute();
            $stmt->close();

            // Insert new line items
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
            $successMsg = "Quotation updated successfully!";

            // Send email
            if ($sendEmail) {
                $clientRes = $conn->query("SELECT email FROM clients WHERE name='".$conn->real_escape_string($clientName)."' LIMIT 1");
                $clientEmail = $clientRes->fetch_assoc()['email'] ?? '';
                if ($clientEmail) {
                    $subject = "Updated Quotation: $quotationNumber";
                    $message = "Dear $clientName,\n\nYour quotation ($quotationNumber) has been updated.\n\nThank you.";
                    mail($clientEmail, $subject, $message, "From: no-reply@yourcompany.com");
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
<title>Edit Quotation</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600;700&family=Urbanist:wght@400;600;700&display=swap" rel="stylesheet">
<style>
body { font-family:'Space Grotesk',sans-serif; background:#f4f7fb; margin:0; padding:0;}
.container { max-width:900px; margin:40px auto; background:#fff; padding:30px; border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,0.08);}
h1 { font-family:'Urbanist',sans-serif; text-align:center; color:#1e3a8a; margin-bottom:25px;}
form label { display:block; margin:10px 0 4px; font-weight:600;}
form input, form select, form textarea { width:100%; padding:10px 12px; border:1px solid #ccc; border-radius:8px; font-size:14px;}
form textarea { resize: vertical;}
.form-section { margin-bottom:25px;}
.line-items { margin-top:10px;}
.line-items table { width:100%; border-collapse: collapse;}
.line-items th, .line-items td { padding:10px; border:1px solid #ddd; text-align:left;}
.line-items input { width:100%; border:none; padding:6px; font-size:14px;}
.line-items th { background:#f1f5f9;}
.add-row-btn { margin-top:10px; padding:8px 14px; background:#22c55e; color:white; border:none; border-radius:6px; cursor:pointer; font-weight:600;}
.add-row-btn:hover { background:#16a34a;}
.buttons { display:flex; gap:10px; margin-top:20px; flex-wrap:wrap;}
.buttons button, .buttons a { flex:1; padding:12px; border:none; border-radius:8px; font-weight:700; cursor:pointer; text-align:center; text-decoration:none;}
.buttons .back-btn { background:#6b7280; color:white;}
.buttons .back-btn:hover { background:#4b5563;}
.buttons .dashboard-btn { background:#2563eb; color:white;}
.buttons .dashboard-btn:hover { background:#1e40af;}
.buttons .invoice-btn { background:#f59e0b; color:white;}
.buttons .invoice-btn:hover { background:#d97706;}
.buttons .save-btn { background:#1e3a8a; color:white;}
.buttons .save-btn:hover { background:#14356a;}
.buttons .email-btn { background:#22c55e; color:white;}
.buttons .email-btn:hover { background:#16a34a;}
.success { background:#d1fae5; color:#065f46; padding:10px; border-radius:6px; margin-bottom:15px;}
.error { background:#fee2e2; color:#b91c1c; padding:10px; border-radius:6px; margin-bottom:15px;}
</style>
</head>
<body>

<div class="container">
<h1>Edit Quotation</h1>

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
            <option value="<?= htmlspecialchars($client['name']) ?>" <?= $quotation['client_name']==$client['name']?'selected':'' ?>><?= htmlspecialchars($client['name']) ?></option>
        <?php endwhile; ?>
    </select>

    <label for="quotation_number">Quotation Number</label>
    <input type="text" name="quotation_number" id="quotation_number" value="<?= htmlspecialchars($quotation['quotation_number']) ?>" required>

    <label for="quotation_date">Quotation Date</label>
    <input type="date" name="quotation_date" id="quotation_date" value="<?= $quotation['quotation_date'] ?>" required>

    <label for="status">Status</label>
    <select name="status" id="status">
        <option value="Pending" <?= $quotation['status']=='Pending'?'selected':'' ?>>Pending</option>
        <option value="Approved" <?= $quotation['status']=='Approved'?'selected':'' ?>>Approved</option>
        <option value="Rejected" <?= $quotation['status']=='Rejected'?'selected':'' ?>>Rejected</option>
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
        <?php foreach($lineItems as $item): ?>
            <tr>
                <td><input type="text" name="item[]" value="<?= htmlspecialchars($item['description']) ?>" required></td>
                <td><input type="number" name="quantity[]" step="0.01" value="<?= $item['quantity'] ?>" required></td>
                <td><input type="number" name="unit_price[]" step="0.01" value="<?= $item['unit_price'] ?>" required></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <button type="button" class="add-row-btn" onclick="addRow()">+ Add Item</button>
</div>

<div class="form-section">
    <label for="notes">Notes / Additional Information</label>
    <textarea name="notes" id="notes" rows="4"><?= htmlspecialchars($quotation['notes']) ?></textarea>
</div>

<div class="buttons">
    <a href="quotations.php" class="back-btn">← Go Back to Quotations</a>
    <a href="dashboard.php" class="dashboard-btn">Dashboard</a>
    <a href="convert_to_invoice.php?id=<?= $quotation['id'] ?>" class="invoice-btn">Change to Invoice</a>
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
