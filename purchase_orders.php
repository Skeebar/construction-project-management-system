<?php
require 'db.php';
include 'nav.php';

// Fetch purchase orders
$sql = "SELECT po.*, s.company_name AS supplier_name, p.name AS project_name 
        FROM purchase_orders po
        LEFT JOIN suppliers s ON po.supplier_id = s.id
        LEFT JOIN projects p ON po.project_id = p.id
        ORDER BY po.created_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Purchase Orders</title>
  <link href="https://fonts.googleapis.com/css2?family=Urbanist:wght@400;600&family=Space+Grotesk:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Urbanist', sans-serif;
      background: #f8f9fa;
      padding: 30px;
    }

    h2 {
      font-family: 'Space Grotesk', sans-serif;
      color: #222;
      font-size: 28px;
      margin-bottom: 20px;
    }

    .btn-new {
      display: inline-block;
      padding: 10px 16px;
      background: #007bff;
      color: #fff;
      text-decoration: none;
      border-radius: 6px;
      margin-bottom: 20px;
      font-weight: 600;
    }

    .btn-new:hover {
      background: #0056b3;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background: #fff;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 3px 8px rgba(0,0,0,0.06);
    }

    th, td {
      padding: 14px 16px;
      text-align: left;
      border-bottom: 1px solid #e0e0e0;
    }

    th {
      background: #007bff;
      color: #fff;
      font-family: 'Space Grotesk', sans-serif;
      font-weight: normal;
    }

    tr:nth-child(even) {
      background: #f5f8ff;
    }

    tr:hover {
      background: #eef5ff;
    }

    .actions a {
      margin-right: 10px;
      color: #007bff;
      text-decoration: none;
      font-weight: 600;
    }

    .actions a:hover {
      text-decoration: underline;
    }

    .status {
      font-weight: 600;
    }

    .status.pending {
      color: orange;
    }

    .status.completed {
      color: green;
    }

    .status.cancelled {
      color: red;
    }
  </style>
</head>
<body>

<h2>Purchase Orders</h2>
<a class="btn-new" href="add_purchase_order.php">+ New Purchase Order</a>

<table>
  <tr>
    <th>PO Number</th>
    <th>Supplier</th>
    <th>Project</th>
    <th>Amount</th>
    <th>Status</th>
    <th>Date</th>
    <th>Actions</th>
  </tr>
  <?php while($row = $result->fetch_assoc()): ?>
    <tr>
      <td><?= htmlspecialchars($row['id']) ?></td>
      <td><?= htmlspecialchars($row['supplier_name']) ?></td>
<td><?= htmlspecialchars($row['project_name']) ?></td>

      <td>R <?= number_format($row['amount'], 2) ?></td>
      <td class="status <?= strtolower($row['status']) ?>"><?= ucfirst($row['status']) ?></td>
      <td><?= htmlspecialchars(date("d M Y", strtotime($row['created_at']))) ?></td>
      <td class="actions">
  <a href="view_po.php?id=<?= $row['id'] ?>">View</a>
  <a href="edit_po.php?id=<?= $row['id'] ?>">Edit</a>
  <a href="delete_po.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this PO?')">Delete</a>
  <a href="generate_po_pdf.php?id=<?= $row['id'] ?>" target="_blank">PDF</a>
</td>

    </tr>
  <?php endwhile; ?>
</table>

</body>
</html>
