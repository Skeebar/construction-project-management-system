<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db.php';
include 'nav.php';

// Handle manual expense submission
$addError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_expense'])) {
    $project_id = intval($_POST['project_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);
    $expense_date = $_POST['expense_date'] ?? date('Y-m-d'); 
    $category = $_POST['category'] ?? 'Misc';

    if ($description && $amount > 0) {
        if ($project_id > 0) {
            // Project selected
            $stmt = $conn->prepare("
                INSERT INTO expenses (description, amount, category, expense_date, project_id)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("sdssi", $description, $amount, $category, $expense_date, $project_id);
        } else {
            // No project → insert NULL
            $stmt = $conn->prepare("
                INSERT INTO expenses (description, amount, category, expense_date, project_id)
                VALUES (?, ?, ?, ?, NULL)
            ");
            $stmt->bind_param("sdss", $description, $amount, $category, $expense_date);
        }

        if ($stmt->execute()) {
            header("Location: add_expense.php?success=1");
            exit;
        } else {
            $addError = "Error saving expense: " . $stmt->error;
        }
    } else {
        $addError = "Description and Amount are required.";
    }
}

// Handle bank statement upload
$uploadError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['bank_statement'])) {
    $file = $_FILES['bank_statement'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $allowed = ['csv', 'pdf'];
        if (in_array(strtolower($ext), $allowed)) {
            $newName = uniqid() . '.' . $ext;
            $uploadDir = 'uploads/bank_statements/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $targetPath = $uploadDir . $newName;
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                header("Location: add_expense.php?success=1");
                exit;
            } else {
                $uploadError = "Failed to move uploaded file.";
            }
        } else {
            $uploadError = "Invalid file type. Allowed: CSV, PDF.";
        }
    } else {
        $uploadError = "File upload error code: " . $file['error'];
    }
}

// Fetch projects for dropdown
$projects = $conn->query("SELECT id, name FROM projects ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Expense</title>
<style>
body { font-family: 'Space Grotesk', sans-serif; background:#f4f7fb; margin:0; padding:20px;}
.container { max-width: 800px; margin:auto; background:#fff; padding:30px; border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,0.08);}
h1 { color:#003366; margin-bottom:20px; }
label { display:block; margin-top:15px; font-weight:600;}
input, select { width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; margin-top:5px;}
button { padding:12px 20px; margin-top:20px; border:none; border-radius:8px; cursor:pointer; font-weight:600;}
button.save { background:#003366; color:white;}
button.save:hover { background:#001f4d;}
.error { color:red; margin-top:10px; }
.success { color:green; margin-top:10px; }
</style>
</head>
<body>
<div class="container">
<h1>Add Expense</h1>

<?php if(!empty($_GET['success'])) echo "<p class='success'>Expense saved successfully!</p>"; ?>

<!-- Manual Expense Form -->
<form method="POST">
    <input type="hidden" name="add_expense" value="1">
    
    <label>Project</label>
    <select name="project_id">
        <option value="0">-- General / No Project --</option>
        <?php while($p = $projects->fetch_assoc()): ?>
            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
        <?php endwhile; ?>
    </select>

    <label>Description</label>
    <input type="text" name="description" required>

    <label>Amount (R)</label>
    <input type="number" step="0.01" name="amount" required>

    <label>Expense Date</label>
    <input type="date" name="expense_date" value="<?= date('Y-m-d') ?>" required>

    <label>Category</label>
    <select name="category">
        <option value="Material">Material</option>
        <option value="Salary">Salary</option>
        <option value="Misc">Miscellaneous</option>
    </select>

    <?php if($addError) echo "<p class='error'>$addError</p>"; ?>
    <button type="submit" class="save">💾 Save Expense</button>
</form>

<hr>

<!-- Bank Statement Upload -->
<h2>Upload Bank Statement</h2>
<form method="POST" enctype="multipart/form-data">
    <input type="file" name="bank_statement" required>
    <?php if($uploadError) echo "<p class='error'>$uploadError</p>"; ?>
    <button type="submit" class="save">Upload</button>
</form>

</div>
</body>
</html>
