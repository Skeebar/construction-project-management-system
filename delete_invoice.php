<?php
require 'db.php';

// Check if invoice ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invoice ID is required.");
}

$invoice_id = intval($_GET['id']);

// Optional: fetch invoice to verify it exists
$stmt = $conn->prepare("SELECT * FROM invoices WHERE id = ?");
$stmt->bind_param("i", $invoice_id);
$stmt->execute();
$result = $stmt->get_result();
$invoice = $result->fetch_assoc();
$stmt->close();

if (!$invoice) {
    die("Invoice not found.");
}

// Delete invoice (invoice_items will also delete if FK ON DELETE CASCADE is set)
$stmt = $conn->prepare("DELETE FROM invoices WHERE id = ?");
$stmt->bind_param("i", $invoice_id);

if ($stmt->execute()) {
    $stmt->close();
    // Redirect back to invoice list
    header("Location: invoice_list.php?msg=Invoice+deleted+successfully");
    exit;
} else {
    echo "Error deleting invoice: " . $conn->error;
}
?>
