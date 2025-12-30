<?php
require 'db.php';

$client_id = $_POST['client_id'];
$project_id = $_POST['project_id'] ?: null;
$invoice_number = $_POST['invoice_number'];
$invoice_date = $_POST['invoice_date'];
$amount = $_POST['amount'];
$description = $_POST['description'];

$stmt = $conn->prepare("INSERT INTO invoices (client_id, project_id, invoice_number, invoice_date, amount, description)
                        VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iissds", $client_id, $project_id, $invoice_number, $invoice_date, $amount, $description);
$stmt->execute();

header("Location: view_invoices.php");
exit;
