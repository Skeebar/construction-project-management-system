<?php
require 'db.php';

if (!isset($_GET['id'])) {
    die('Employee ID missing');
}

$id = intval($_GET['id']);
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'];
    $position = $_POST['position'];
    $salary_per_hour = $_POST['salary_per_hour'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE employees SET full_name = ?, position = ?, salary_per_hour = ?, status = ? WHERE id = ?");
    $stmt->bind_param("ssdsi", $full_name, $position, $salary_per_hour, $status, $id);

    if ($stmt->execute()) {
        $message = "✅ Employee updated successfully.";
    } else {
        $message = "❌ Error updating employee: " . $stmt->error;
    }
    $stmt->close();
}

$res = $conn->query("SELECT * FROM employees WHERE id = $id");
if ($res->num_rows !== 1) {
    die('Employee not found');
}
$employee = $res->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Edit Employee</title>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;700&family=Urbanist:wght@400;600&display=swap" rel="stylesheet" />
  <style>
    body {
      margin: 0;
      font-family: 'Space Grotesk', sans-serif;
      background: url('bg.jpg') no-repeat center center fixed;
      background-size: cover;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      color: #fff;
    }
    .container {
      background: rgba(45, 28, 179, 0.7);
      border-radius: 20px;
      padding: 30px 40px;
      max-width: 450px;
      width: 90%;
      backdrop-filter: blur(12px);
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
    }
    h2 {
      font-family: 'Urbanist', sans-serif;
      text-align: center;
      margin-bottom: 24px;
      font-weight: 700;
      font-size: 28px;
    }
    label {
      display: block;
      margin: 15px 0 6px;
      font-weight: 600;
    }
    input, select {
      width: 100%;
      padding: 12px;
      border-radius: 10px;
      border: none;
      font-size: 16px;
      font-family: 'Space Grotesk', sans-serif;
      background: rgba(255, 255, 255, 0.15);
      color: #fff;
      outline-offset: 2px;
      outline-color: transparent;
      transition: outline-color 0.3s ease;
      box-sizing: border-box;
    }
    input:focus, select:focus {
      outline-color: #5a72ff;
      background: rgba(255, 255, 255, 0.25);
    }
    button {
      margin-top: 28px;
      width: 100%;
      padding: 14px;
      border: none;
      background-color: #3f51b5;
      border-radius: 12px;
      color: white;
      font-size: 18px;
      font-weight: 600;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    button:hover {
      background-color: #2c3e91;
    }
    .message {
      margin-bottom: 20px;
      font-weight: 700;
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Edit Employee</h2>
    <?php if ($message): ?>
      <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form action="" method="POST" autocomplete="off">
      <label for="full_name">Full Name *</label>
      <input type="text" id="full_name" name="full_name" required value="<?= htmlspecialchars($employee['full_name']) ?>" />

      <label for="position">Position</label>
      <input type="text" id="position" name="position" value="<?= htmlspecialchars($employee['position']) ?>" />

      <label for="salary_per_hour">Salary per Hour (R)</label>
      <input type="number" id="salary_per_hour" name="salary_per_hour" step="0.01" min="0" value="<?= htmlspecialchars($employee['salary_per_hour']) ?>" />

      <label for="status">Status</label>
      <select id="status" name="status" >
        <option value="Active" <?= $employee['status'] === 'Active' ? 'selected' : '' ?>>Active</option>
        <option value="Inactive" <?= $employee['status'] === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
      </select>

      <button type="submit">Update Employee</button>
    </form>
  </div>
</body>
</html>
