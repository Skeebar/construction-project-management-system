<?php
require 'db.php';

// Validate required top-level fields
if (!isset($_POST['supplier_id'], $_POST['items']) || empty($_POST['items'])) {
    die("Missing supplier or items.");
}

$supplier_id = intval($_POST['supplier_id']);
$project_id = !empty($_POST['project_id']) ? intval($_POST['project_id']) : null;
$delivery_address = $_POST['delivery_address'] ?? '';
$terms = $_POST['terms'] ?? '';
$order_date = date('Y-m-d');
$status = 'Pending';

$conn->begin_transaction();

try {
    // Insert into purchase_orders
    $stmt = $conn->prepare("INSERT INTO purchase_orders (supplier_id, project_id, order_date, status, notes) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $supplier_id, $project_id, $order_date, $status, $terms);
    $stmt->execute();
    $purchase_order_id = $stmt->insert_id;

    // Insert items
    $items = $_POST['items'];
    $item_stmt = $conn->prepare("INSERT INTO purchase_order_items (purchase_order_id, description, quantity, unit_price) VALUES (?, ?, ?, ?)");

    foreach ($items as $item) {
        $description = trim($item['description']);
        $qty = floatval($item['qty']);
        $price = floatval($item['price']);

        if ($description && $qty > 0 && $price >= 0) {
            $item_stmt->bind_param("isdd", $purchase_order_id, $description, $qty, $price);
            $item_stmt->execute();
        }
    }

    $conn->commit();
    header("Location: purchase_orders.php?success=1");
    exit;
} catch (Exception $e) {
    $conn->rollback();
    echo "Error processing purchase order: " . $e->getMessage();
}
?>
