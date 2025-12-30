<?php
require 'db.php';
include 'nav.php';

$message = "";

// Handle manual add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_manual'])) {
    $date = $_POST['transaction_date'] ?? date('Y-m-d');
    $description = trim($_POST['description'] ?? '');
    $type = $_POST['type'] ?? 'expense';
    $amount = floatval($_POST['amount'] ?? 0);

    if ($description && $amount != 0) {
        // Insert into transactions table
        $stmt = $conn->prepare("INSERT INTO transactions (transaction_date, description, type, amount) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssd", $date, $description, $type, $amount);
        if ($stmt->execute()) {
            // If it's an expense, also insert into expenses table
            if ($type === 'expense') {
                $stmt2 = $conn->prepare("INSERT INTO expenses (description, amount, category, expense_date) VALUES (?, ?, ?, ?)");
                $category = "General"; // default category
                $stmt2->bind_param("sdss", $description, $amount, $category, $date);
                $stmt2->execute();
                $stmt2->close();
            }
            $message = "Transaction added successfully!";
        } else {
            $message = "Error: " . $conn->error;
        }
        $stmt->close();
    } else {
        $message = "Please provide description and amount.";
    }
}

// Handle CSV upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_csv'])) {
    if (is_uploaded_file($_FILES['file']['tmp_name'])) {
        $file = fopen($_FILES['file']['tmp_name'], 'r');
        fgetcsv($file); // skip header row

        while (($row = fgetcsv($file)) !== false) {
            // Example Capitec format (adjust indexes if needed)
            $transactionDate = date('Y-m-d', strtotime($row[3])); 
            $description     = $row[4];
            $category        = $row[7] ?? "Misc";
            $moneyIn         = trim($row[8]) !== "" ? (float) str_replace([",", " "], "", $row[8]) : 0;
            $moneyOut        = trim($row[9]) !== "" ? (float) str_replace([",", " "], "", $row[9]) : 0;

            $amount = 0;
            $type   = "expense";

            if ($moneyIn > 0) {
                $amount = $moneyIn;
                $type = "income";
            } elseif ($moneyOut > 0) {
                $amount = $moneyOut;
                $type = "expense";
            }

            if ($amount != 0) {
                // Insert into transactions
                $stmt = $conn->prepare("INSERT INTO transactions (transaction_date, description, type, amount, category) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssds", $transactionDate, $description, $type, $amount, $category);
                $stmt->execute();
                $stmt->close();

                // If expense, also add to expenses table
                if ($type === 'expense') {
                    $stmt2 = $conn->prepare("INSERT INTO expenses (description, amount, category, expense_date) VALUES (?, ?, ?, ?)");
                    $stmt2->bind_param("sdss", $description, $amount, $category, $transactionDate);
                    $stmt2->execute();
                    $stmt2->close();
                }
            }
        }

        fclose($file);
        $message = "CSV uploaded and transactions saved!";
    } else {
        $message = "Please select a CSV file.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Transaction</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600&family=Urbanist:wght@400;600&display=swap" rel="stylesheet">
<style>
body {
    font-family: 'Urbanist', sans-serif;
    background:#f4f7fb;
    margin:0; padding:20px;
}
.container {
    background:white;
    padding:30px;
    border-radius:16px;
    max-width:800px;
    margin:auto;
    box-shadow:0 6px 18px rgba(0,0,0,0.08);
}
h1 {
    font-family:'Space Grotesk', sans-serif;
    color:#003366;
    display:flex;
    justify-content:space-between;
    align-items:center;
}
.back-btn {
    text-decoration:none;
    background:#003366;
    color:white;
    padding:8px 14px;
    border-radius:8px;
    font-weight:600;
}
.back-btn:hover { background:#001f4d; }
form { margin-bottom:30px; }
label { display:block; margin:10px 0 5px; font-weight:600; }
input, select {
    width:100%; padding:10px; margin-bottom:15px;
    border:1px solid #ccc; border-radius:8px; font-family:inherit;
}
button {
    background:#003366; color:white; border:none; padding:10px 20px;
    border-radius:8px; font-weight:600; cursor:pointer;
}
button:hover { background:#001f4d; }
.message { margin-bottom:20px; padding:10px; border-radius:8px; font-weight:600; }
.success { background:#e6ffed; color:#16a34a; }
.error { background:#ffe5e5; color:#b91c1c; }
.section-title { font-family:'Space Grotesk', sans-serif; margin-top:20px; }
</style>
</head>
<body>
<div class="container">
    <h1>
        Add Transaction
        <a href="transactions.php" class="back-btn">← Back to Transactions</a>
    </h1>

    <?php if ($message): ?>
        <div class="message <?= strpos($message, 'successfully') !== false || strpos($message, 'saved') !== false ? 'success' : 'error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <h2 class="section-title">Manual Entry</h2>
    <form method="POST">
        <label>Date</label>
        <input type="date" name="transaction_date" value="<?= date('Y-m-d') ?>" required>

        <label>Description</label>
        <input type="text" name="description" required>

        <label>Type</label>
        <select name="type" required>
            <option value="income">Income</option>
            <option value="expense">Expense</option>
        </select>

        <label>Amount (R)</label>
        <input type="number" step="0.01" name="amount" required>

        <button type="submit" name="add_manual">Add Transaction</button>
    </form>

    <h2 class="section-title">Upload CSV Bank Statement</h2>
    <form method="POST" enctype="multipart/form-data">
        <label>Select CSV File</label>
        <input type="file" name="file" accept=".csv" required>
        <button type="submit" name="upload_csv">Upload CSV</button>
    </form>
</div>
</body>
</html>
