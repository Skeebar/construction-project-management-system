<?php
require 'db.php';

if (!isset($_GET['id'])) {
    die("PO ID not specified.");
}

$id = $_GET['id'];

// Fetch PO
$stmt = $conn->prepare("SELECT * FROM purchase_orders WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$po = $result->fetch_assoc();

if (!$po) {
    die("Purchase Order not found.");
}

// Fetch suppliers and projects
$suppliers = $conn->query("SELECT id, company_name FROM suppliers ORDER BY company_name");
$projects = $conn->query("SELECT id, name FROM projects ORDER BY name");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Purchase Order</title>
    <link href="https://fonts.googleapis.com/css2?family=Urbanist:wght@400;600&family=Space+Grotesk:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Urbanist', sans-serif;
            background: #f7f7f7;
            padding: 30px;
        }
        form {
            background: white;
            max-width: 600px;
            margin: auto;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0,0,0,0.08);
        }
        h2 {
            font-family: 'Space Grotesk', sans-serif;
            text-align: center;
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: 600;
        }
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
        }
        button {
            margin-top: 20px;
            background: #263238;
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 6px;
            font-size: 16px;
        }
    </style>
</head>
<body>

<h2>Edit Purchase Order</h2>
<form action="update_po.php" method="POST">
    <input type="hidden" name="id" value="<?= $po['id'] ?>">

    <label for="supplier_id">Supplier</label>
    <select name="supplier_id" required>
        <option value="">Select supplier</option>
        <?php while ($s = $suppliers->fetch_assoc()): ?>
            <option value="<?= $s['id'] ?>" <?= $s['id'] == $po['supplier_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($s['company_name']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label for="project_id">Project</label>
    <select name="project_id" required>
        <option value="">Select project</option>
        <?php while ($p = $projects->fetch_assoc()): ?>
            <option value="<?= $p['id'] ?>" <?= $p['id'] == $po['project_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($p['name']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label for="status">Status</label>
    <select name="status" required>
        <option value="Pending" <?= $po['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
        <option value="Approved" <?= $po['status'] == 'Approved' ? 'selected' : '' ?>>Approved</option>
        <option value="Delivered" <?= $po['status'] == 'Delivered' ? 'selected' : '' ?>>Delivered</option>
        <option value="Paid" <?= $po['status'] == 'Paid' ? 'selected' : '' ?>>Paid</option>
    </select>

    <label for="amount">Amount (R)</label>
    <input type="number" name="amount" step="0.01" value="<?= $po['amount'] ?>" required>

    <button type="submit">Update PO</button>
</form>

</body>
</html>
