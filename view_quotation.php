<?php
require 'db.php';

$id = intval($_GET['id'] ?? 0);

if (!$id) {
    die("Quotation ID missing");
}

// Fetch quotation
$quoteRes = $conn->query("SELECT * FROM quotations WHERE id=$id");
$quote = $quoteRes->fetch_assoc();
if (!$quote) die("Quotation not found");

// Fetch line items
$itemsRes = $conn->query("SELECT * FROM quotation_items WHERE quotation_id=$id");
$lineItems = [];
while ($row = $itemsRes->fetch_assoc()) {
    $lineItems[] = $row;
}

// Calculate subtotal and tax
$subtotal = 0;
foreach ($lineItems as $item) {
    $subtotal += $item['quantity'] * $item['unit_price'];
}
$taxRate = 0.15;
$taxAmount = $subtotal * $taxRate;
$total = $subtotal + $taxAmount;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View Quotation #<?= $quote['quotation_number'] ?></title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600&family=Urbanist:wght@400;600;700&display=swap" rel="stylesheet">
<style>
body { font-family:'Space Grotesk',sans-serif; background:#f4f7fb; margin:0; padding:20px;}
.container { max-width:900px; margin:auto; background:white; padding:25px; border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,0.08);}
h1 { font-family:'Urbanist',sans-serif; color:#1e3a8a; text-align:center;}
table { width:100%; border-collapse:collapse; margin-top:20px;}
th, td { border:1px solid #ddd; padding:10px; text-align:left;}
th { background:#f1f5f9; }
.summary { margin-top:20px; text-align:right; }
.buttons { margin-top:20px; display:flex; gap:10px; flex-wrap:wrap; }
.buttons a { flex:1; padding:12px; border:none; border-radius:8px; font-weight:700; text-decoration:none; color:white; text-align:center;}
.buttons .edit { background:#1e3a8a; }
.buttons .edit:hover { background:#14356a; }
.buttons .back { background:#6b7280; }
.buttons .back:hover { background:#4b5563; }
</style>
</head>
<body>

<div class="container">
<h1>Quotation #<?= htmlspecialchars($quote['quotation_number']) ?></h1>
<p><strong>Client:</strong> <?= htmlspecialchars($quote['client_name']) ?></p>
<p><strong>Date:</strong> <?= htmlspecialchars($quote['quotation_date']) ?></p>
<p><strong>Status:</strong> <?= htmlspecialchars($quote['status']) ?></p>

<table>
<thead>
<tr>
<th>Description</th>
<th>Quantity</th>
<th>Unit Price (R)</th>
<th>Total (R)</th>
</tr>
</thead>
<tbody>
<?php foreach($lineItems as $item): ?>
<tr>
<td><?= htmlspecialchars($item['description']) ?></td>
<td><?= $item['quantity'] ?></td>
<td><?= number_format($item['unit_price'],2) ?></td>
<td><?= number_format($item['quantity'] * $item['unit_price'],2) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<div class="summary">
<p><strong>Subtotal:</strong> R <?= number_format($subtotal,2) ?></p>
<p><strong>Tax (15%):</strong> R <?= number_format($taxAmount,2) ?></p>
<p><strong>Total:</strong> R <?= number_format($total,2) ?></p>
</div>

<p><strong>Notes:</strong> <?= nl2br(htmlspecialchars($quote['notes'])) ?></p>

<div class="buttons">
<a href="edit_quotation.php?id=<?= $quote['id'] ?>" class="edit">✏️ Edit Quotation</a>
<a href="quotations.php" class="back">← Back to Quotations</a>
</div>

</div>
</body>
</html>
