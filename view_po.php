<?php
require 'db.php';

if (!isset($_GET['id'])) {
  die('Invalid PO ID');
}

$po_id = intval($_GET['id']);

// Fetch Purchase Order with Supplier and Project Names
$sql = "SELECT po.*, s.company_name AS supplier_name, p.name AS project_name 
        FROM purchase_orders po
        LEFT JOIN suppliers s ON po.supplier_id = s.id
        LEFT JOIN projects p ON po.project_id = p.id
        WHERE po.id = $po_id
        LIMIT 1";

$result = $conn->query($sql);
if (!$result || $result->num_rows == 0) {
  die('Purchase Order not found');
}
$po = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Purchase Order</title>
  <link href="https://fonts.googleapis.com/css2?family=Urbanist:wght@400;600&family=Space+Grotesk:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Urbanist', sans-serif;
      background: #f4f7fc;
      padding: 30px;
    }

    h2 {
      font-family: 'Space Grotesk', sans-serif;
      font-size: 26px;
      color: #333;
      margin-bottom: 20px;
    }

    .po-box {
      background: #fff;
      padding: 25px;
      border-radius: 10px;
      box-shadow: 0 3px 10px rgba(0, 0, 0, 0.07);
      max-width: 800px;
      margin: auto;
    }

    .po-row {
      margin-bottom: 12px;
      display: flex;
      justify-content: space-between;
      border-bottom: 1px dashed #ccc;
      padding-bottom: 10px;
    }

    .po-label {
      font-weight: 600;
      color: #555;
    }

    .po-value {
      font-weight: 500;
      color: #000;
    }

    .status {
      padding: 6px 12px;
      border-radius: 6px;
      font-weight: 600;
      display: inline-block;
    }

    .pending { background: #fff3cd; color: #856404; }
    .completed { background: #d4edda; color: #155724; }
    .cancelled { background: #f8d7da; color: #721c24; }

    .btn-back {
      display: inline-block;
      margin-top: 25px;
      text-decoration: none;
      background: #007bff;
      color: #fff;
      padding: 10px 18px;
      border-radius: 6px;
      font-weight: 600;
    }

    .btn-back:hover {
      background: #0056b3;
    }
  </style>
</head>
<body>

<div class="po-box">
  <h2>Purchase Order #<?= htmlspecialchars($po['id']) ?></h2>

  <div class="po-row">
    <div class="po-label">Supplier</div>
    <div class="po-value"><?= htmlspecialchars($po['supplier_name']) ?></div>
  </div>

  <div class="po-row">
    <div class="po-label">Project</div>
    <div class="po-value"><?= htmlspecialchars($po['project_name']) ?></div>
  </div>

  <div class="po-row">
    <div class="po-label">Amount</div>
    <div class="po-value">R <?= number_format($po['amount'], 2) ?></div>
  </div>

  <div class="po-row">
    <div class="po-label">Status</div>
    <div class="po-value status <?= strtolower($po['status']) ?>"><?= ucfirst($po['status']) ?></div>
  </div>

  <div class="po-row">
    <div class="po-label">Created At</div>
    <div class="po-value"><?= date("d M Y", strtotime($po['created_at'])) ?></div>
  </div>

  <a class="btn-back" href="purchase_orders.php">← Back to List</a>
</div>

</body>
</html>
