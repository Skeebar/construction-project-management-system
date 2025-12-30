<?php
include 'db.php';
include 'nav.php';

// Fetch supplier for editing
$edit_supplier = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $result = $conn->query("SELECT * FROM suppliers WHERE id = $edit_id");
    if ($result->num_rows > 0) {
        $edit_supplier = $result->fetch_assoc();
    }
}

// Add or update supplier
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $company = $_POST['company_name'];
    $contact = $_POST['contact_person'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $category = $_POST['category'];

    if (isset($_POST['supplier_id']) && $_POST['supplier_id'] != '') {
        $id = intval($_POST['supplier_id']);
        $stmt = $conn->prepare("UPDATE suppliers SET company_name=?, contact_person=?, phone=?, email=?, category=? WHERE id=?");
        $stmt->bind_param("sssssi", $company, $contact, $phone, $email, $category, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO suppliers (company_name, contact_person, phone, email, category) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $company, $contact, $phone, $email, $category);
    }

    $stmt->execute();
    header("Location: suppliers.php");
    exit;
}

// Delete supplier
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM suppliers WHERE id = $id");
    header("Location: suppliers.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suppliers</title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&family=Urbanist:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Urbanist', sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1000px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2, h3 {
            font-family: 'Space Grotesk', sans-serif;
            margin-bottom: 20px;
        }
        form label {
            display: block;
            margin-top: 10px;
            font-weight: 600;
        }
        form input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-family: 'Urbanist', sans-serif;
        }
        form button {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            margin-top: 15px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s ease;
        }
        form button:hover {
            background: #45a049;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
            font-family: 'Urbanist', sans-serif;
        }
        table th {
            background: #f0f0f0;
        }
        a {
            text-decoration: none;
            color: #2196F3;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Suppliers</h2>

    <form method="POST">
        <h3><?= $edit_supplier ? "Edit Supplier" : "Add New Supplier" ?></h3>

        <input type="hidden" name="supplier_id" value="<?= $edit_supplier['id'] ?? '' ?>">

        <label>Company Name:</label>
        <input type="text" name="company_name" value="<?= $edit_supplier['company_name'] ?? '' ?>" required>

        <label>Contact Person:</label>
        <input type="text" name="contact_person" value="<?= $edit_supplier['contact_person'] ?? '' ?>" required>

        <label>Phone:</label>
        <input type="text" name="phone" value="<?= $edit_supplier['phone'] ?? '' ?>" required>

        <label>Email:</label>
        <input type="email" name="email" value="<?= $edit_supplier['email'] ?? '' ?>" required>

        <label>Category:</label>
        <input type="text" name="category" value="<?= $edit_supplier['category'] ?? '' ?>" required>

        <button type="submit"><?= $edit_supplier ? "Update Supplier" : "Add Supplier" ?></button>
        <?php if ($edit_supplier): ?>
            <a href="suppliers.php" style="margin-left:10px;">Cancel</a>
        <?php endif; ?>
    </form>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Company Name</th>
                <th>Contact Person</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Category</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result = $conn->query("SELECT * FROM suppliers ORDER BY id DESC");
            while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['company_name'] ?></td>
                    <td><?= $row['contact_person'] ?></td>
                    <td><?= $row['phone'] ?></td>
                    <td><?= $row['email'] ?></td>
                    <td><?= $row['category'] ?></td>
                    <td>
                        <a href="suppliers.php?edit=<?= $row['id'] ?>">Edit</a> |
                        <a href="suppliers.php?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this supplier?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>

