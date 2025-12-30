<?php
require 'db.php';

$id = $_POST['id'];
$supplier_id = $_POST['supplier_id'];
$project_id = $_POST['project_id'];
$status = $_POST['status'];
$amount = $_POST['amount'];

$stmt = $conn->prepare("UPDATE purchase_orders SET supplier_id=?, project_id=?, status=?, amount=? WHERE id=?");
$stmt->bind_param("iisdi", $supplier_id, $project_id, $status, $amount, $id);

if ($stmt->execute()) {
    header("Location: view_po.php?msg=Purchase Order updated");
    exit;
} else {
    echo "Error: " . $stmt->error;
}
