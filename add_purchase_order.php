<?php
require 'db.php';

$suppliers = $conn->query("SELECT id, company_name FROM suppliers ORDER BY company_name ASC");
$projects = $conn->query("SELECT id, name FROM projects ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create Purchase Order</title>
  <link href="https://fonts.googleapis.com/css2?family=Urbanist:wght@400;600&family=Space+Grotesk:wght@400;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Urbanist', sans-serif;
      background: #f5f7fa;
      padding: 40px;
      color: #333;
    }

    h2 {
      font-family: 'Space Grotesk', sans-serif;
      margin-bottom: 20px;
    }

    .form-section {
      background: white;
      border-radius: 12px;
      padding: 25px;
      box-shadow: 0 4px 16px rgba(0,0,0,0.06);
      margin-bottom: 30px;
    }

    label {
      font-weight: 600;
      display: block;
      margin-bottom: 6px;
      margin-top: 16px;
    }

    input, select, textarea {
      width: 100%;
      padding: 10px 12px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
      font-family: 'Urbanist', sans-serif;
    }

    table {
      width: 100%;
      margin-top: 20px;
      border-collapse: collapse;
    }

    th, td {
      padding: 12px;
      border: 1px solid #ddd;
    }

    th {
      background: #007bff;
      color: white;
      font-weight: 500;
    }

    .btn {
      background: #007bff;
      color: white;
      padding: 10px 20px;
      border: none;
      font-weight: bold;
      border-radius: 6px;
      cursor: pointer;
      margin-top: 20px;
    }

    .btn:hover {
      background: #005bb5;
    }

    .add-row-btn {
      margin-top: 10px;
      background: #28a745;
    }

    .remove-row-btn {
      background: #dc3545;
      padding: 4px 10px;
    }

    .totals {
      text-align: right;
      margin-top: 20px;
    }
  </style>
</head>
<body>

<h2>Create Purchase Order</h2>

<form action="submit_purchase_order.php" method="POST">
  <div class="form-section">
    <label for="supplier">Supplier</label>
    <select name="supplier_id" id="supplier" required>
      <option value="">Select supplier</option>
      <?php while($s = $suppliers->fetch_assoc()): ?>
        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['company_name']) ?></option>
      <?php endwhile; ?>
    </select>

    <label for="project">Project</label>
    <select name="project_id" id="project">
      <option value="">Optional</option>
      <?php while($p = $projects->fetch_assoc()): ?>
        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
      <?php endwhile; ?>
    </select>

    <label for="delivery_address">Delivery Address</label>
    <textarea name="delivery_address" id="delivery_address" rows="2"></textarea>

    <label for="terms">Terms and Conditions</label>
    <textarea name="terms" id="terms" rows="3">Payment within 30 days. Delivery within 7 days from order date.</textarea>
  </div>

  <div class="form-section">
    <table id="itemsTable">
      <thead>
        <tr>
          <th>Description</th>
          <th>Qty</th>
          <th>Unit Price</th>
          <th>Total</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><input name="items[0][description]" required></td>
          <td><input name="items[0][qty]" type="number" step="1" min="1" required oninput="calculateTotals()"></td>
          <td><input name="items[0][price]" type="number" step="0.01" min="0" required oninput="calculateTotals()"></td>
          <td class="item-total">0.00</td>
          <td><button type="button" class="remove-row-btn" onclick="removeRow(this)">X</button></td>
        </tr>
      </tbody>
    </table>
    <button type="button" class="btn add-row-btn" onclick="addRow()">+ Add Item</button>
    
    <div class="totals">
      <p><strong>Subtotal:</strong> R <span id="subtotal">0.00</span></p>
      <p><strong>VAT (15%):</strong> R <span id="vat">0.00</span></p>
      <p><strong>Total:</strong> R <span id="total">0.00</span></p>
    </div>
  </div>

  <button type="submit" class="btn">Submit Purchase Order</button>
</form>

<script>
let itemIndex = 1;

function addRow() {
  const table = document.querySelector("#itemsTable tbody");
  const row = document.createElement("tr");
  row.innerHTML = `
    <td><input name="items[${itemIndex}][description]" required></td>
    <td><input name="items[${itemIndex}][qty]" type="number" step="1" min="1" required oninput="calculateTotals()"></td>
    <td><input name="items[${itemIndex}][price]" type="number" step="0.01" min="0" required oninput="calculateTotals()"></td>
    <td class="item-total">0.00</td>
    <td><button type="button" class="remove-row-btn" onclick="removeRow(this)">X</button></td>
  `;
  table.appendChild(row);
  itemIndex++;
}

function removeRow(btn) {
  btn.closest("tr").remove();
  calculateTotals();
}

function calculateTotals() {
  let subtotal = 0;
  const rows = document.querySelectorAll("#itemsTable tbody tr");
  rows.forEach(row => {
    const qty = parseFloat(row.querySelector('input[name*="[qty]"]').value) || 0;
    const price = parseFloat(row.querySelector('input[name*="[price]"]').value) || 0;
    const total = qty * price;
    row.querySelector('.item-total').innerText = total.toFixed(2);
    subtotal += total;
  });

  const vat = subtotal * 0.15;
  const total = subtotal + vat;

  document.getElementById('subtotal').innerText = subtotal.toFixed(2);
  document.getElementById('vat').innerText = vat.toFixed(2);
  document.getElementById('total').innerText = total.toFixed(2);
}
</script>

</body>
</html>
