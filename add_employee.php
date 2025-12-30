<?php
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full_name'];
    $position = $_POST['position'];
    $salary_per_hour = $_POST['salary_per_hour'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("INSERT INTO employees (full_name, position, salary_per_hour, status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $full_name, $position, $salary_per_hour, $status);

    if ($stmt->execute()) {
        $message = "✅ Employee added successfully.";
    } else {
        $message = "❌ Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Add Employee</title>
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
      margin-bottom: 18px;
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
    }
    input:focus, select:focus {
      outline-color: #5a72ff;
      background: rgba(255, 255, 255, 0.25);
    }
    button {
      margin-top: 20px;
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
      margin-top: 15px;
      text-align: center;
      font-weight: 700;
    }
    .back-btn {
      display: inline-block;
      margin-bottom: 15px;
      padding: 6px 14px;
      font-size: 14px;
      background-color: #555;
      border-radius: 8px;
      text-decoration: none;
      color: #fff;
      font-weight: 600;
      transition: background-color 0.3s ease;
    }
    .back-btn:hover {
      background-color: #333;
    }
  </style>
</head>
<body>
  <div class="container">
    <a href="employees.php" class="back-btn">← Back to Employees</a>
    <h2>Add New Employee</h2>
    <?php if (!empty($message)) : ?>
      <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form action="" method="POST" autocomplete="off">
      <label for="full_name">Full Name *</label>
      <input type="text" id="full_name" name="full_name" required />

      <label for="position">Position</label>
      <input type="text" id="position" name="position" />

      <label for="salary_per_hour">Salary per Hour (R)</label>
      <input type="number" id="salary_per_hour" name="salary_per_hour" step="0.01" min="0" />

      <label for="status">Status</label>
      <select id="status" name="status">
        <option value="Active" selected>Active</option>
        <option value="Inactive">Inactive</option>
      </select>

      <button type="submit">Add Employee</button>
    </form>
  </div>
</body>
</html>
