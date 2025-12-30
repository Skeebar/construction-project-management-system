<?php
require 'db.php';

// Validate POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request");
}

$quotation_id = $_POST['quotation_id'] ?? null;
$project_id   = $_POST['project_id'] ?? null;
$invoice_number = $_POST['invoice_number'];
$invoice_date   = $_POST['invoice_date'];
$status         = $_POST['status'];
$notes          = $_POST['notes'] ?? '';
$discount       = floatval($_POST['discount'] ?? 0);
$tax            = floatval($_POST['tax'] ?? 0);

$clientId   = $_POST['client_id'] ?? null;
$clientName = trim($_POST['client_name'] ?? '');

// ✅ Auto-create client if missing
if (empty($clientId) && !empty($clientName)) {
    $stmt = $conn->prepare("INSERT INTO clients (name) VALUES (?)");
    $stmt->bind_param("s", $clientName);
    $stmt->execute();
    $clientId = $stmt->insert_id;
    $stmt->close();
}

// If still no clientId, stop here
if (empty($clientId)) {
    die("Client information is required.");
}

// Items
$items = $_POST['item'] ?? [];
$quantities = $_POST['quantity'] ?? [];
$unit_prices = $_POST['unit_price'] ?? [];

// Calculate totals
$subtotal = 0;
for ($i = 0; $i < count($items); $i++) {
    $subtotal += ($quantities[$i] * $unit_prices[$i]);
}
$discountAmount = ($discount / 100) * $subtotal;
$taxAmount = ($tax / 100) * ($subtotal - $discountAmount);
$total = $subtotal - $discountAmount + $taxAmount;

// ✅ Insert invoice
$stmt = $conn->prepare("
    INSERT INTO invoices 
    (invoice_number, client_id, project_id, quotation_id, invoice_date, status, subtotal, discount, tax, amount, notes, created_at) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
");
$stmt->bind_param(
    "siiissdddss",
    $invoice_number,
    $clientId,
    $project_id,
    $quotation_id,
    $invoice_date,
    $status,
    $subtotal,
    $discount,
    $tax,
    $total,
    $notes
);
$stmt->execute();
$invoiceId = $stmt->insert_id;
$stmt->close();

// ✅ Insert invoice items
$stmt = $conn->prepare("
    INSERT INTO invoice_items (invoice_id, description, quantity, unit_price) 
    VALUES (?, ?, ?, ?)
");

for ($i = 0; $i < count($items); $i++) {
    $desc = $items[$i];
    $qty  = $quantities[$i];
    $price = $unit_prices[$i];
    $stmt->bind_param("isid", $invoiceId, $desc, $qty, $price);
    $stmt->execute();
}
$stmt->close();

// ✅ Redirect to view invoice
header("Location: view_invoice.php?id=" . $invoiceId);
exit;
?>
