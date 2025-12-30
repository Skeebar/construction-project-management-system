<?php
require 'db.php';

$project_id = $_GET['project_id'] ?? 0;

// Get project name
$project_name = "Unknown Project";
if ($project_id) {
    $stmt = $conn->prepare("SELECT name FROM projects WHERE id = ?");
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $stmt->bind_result($project_name);
    $stmt->fetch();
    $stmt->close();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $material = $_POST['material_name'];
    $quantity = $_POST['quantity'];
    $unit_price = $_POST['unit_price'];

    $stmt = $conn->prepare("INSERT INTO materials (project_id, material_name, quantity, unit_price) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isid", $project_id, $material, $quantity, $unit_price);
    $stmt->execute();

    header("Location: view_project.php?id=$project_id");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Material</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f8;
            padding: 40px;
        }

        .form-container {
            background-color: #fff;
            max-width: 500px;
            margin: auto;
            padding: 30px 40px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 8px;
            color: #555;
        }

        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
        }

        button {
            background-color: #0066cc;
            color: #fff;
            padding: 12px;
            width: 100%;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #004a99;
        }

        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
            text-decoration: none;
            color: #666;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="form-container">
        <h2>Add Material to: <br><span style="color:#0066cc"><?= htmlspecialchars($project_name) ?> Project</span></h2>

        <form method="POST">
            <label>Material Name</label>
            <input type="text" name="material_name" required>

            <label>Quantity</label>
            <input type="number" name="quantity" required>

            <label>Unit Price (R)</label>
            <input type="number" step="0.01" name="unit_price" required>

            <button type="submit">➕ Add Material</button>
        </form>

        <a href="view_project.php?id=<?= $project_id ?>" class="back-link">⬅ Back to Project</a>
    </div>

</body>
</html>
