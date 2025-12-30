<?php
require 'db.php';

// Fetch employee list for assigning to project team
$employees = $conn->query("SELECT id, full_name, salary_per_hour FROM employees");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Add New Project</title>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;700&family=Urbanist:wght@400;600&display=swap" rel="stylesheet">
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
    }

    .container {
      background: rgba(45, 28, 179, 0.7);
      border-radius: 20px;
      padding: 30px;
      max-width: 600px;
      width: 90%;
      backdrop-filter: blur(12px);
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
      color: #fff;
    }

    h2 {
      text-align: center;
      font-family: 'Urbanist', sans-serif;
      margin-bottom: 20px;
    }

    label {
      display: block;
      margin: 12px 0 5px;
      font-weight: 600;
    }

    input, select, textarea {
      width: 100%;
      padding: 10px;
      border-radius: 8px;
      border: none;
      font-size: 16px;
      font-family: 'Space Grotesk', sans-serif;
    }

    select[multiple] {
      height: 120px;
    }

    .employee-rate {
      font-size: 12px;
      color: #e0e0e0;
    }

    button, .back-btn {
      margin-top: 20px;
      width: 100%;
      padding: 12px;
      border: none;
      background-color: #1f1fff;
      color: white;
      border-radius: 10px;
      font-size: 16px;
      cursor: pointer;
      transition: 0.3s ease;
      display: block;
      text-align: center;
      text-decoration: none;
    }

    button:hover, .back-btn:hover {
      background-color: #0000cc;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Add New Project</h2>
    <form action="insert_project.php" method="POST">
      <label for="client">Client Name *</label>
      <input type="text" id="client" name="client" required>

      <label for="project_name">Project Name *</label>
      <input type="text" id="name" name="project_name" required>

      <label for="budget">Budget (R)</label>
      <input type="number" id="budget" name="budget" step="0.01">

      <label>Estimated Cost (R)*</label>
      <input type="number" step="0.01" name="estimated_cost" required>

      <label>Revenue (R)*</label>
      <input type="number" step="0.01" name="revenue" required>

      <label for="quoted_amount">Quoted Amount (R)</label>
      <input type="number" id="quoted_amount" name="quoted_amount" step="0.01" required>

      <label for="start_date">Start Date</label>
      <input type="date" id="start_date" name="start_date">

      <label for="end_date">End Date</label>
      <input type="date" id="end_date" name="end_date">

      <label for="status">Status</label>
      <select name="status" id="status">
        <option value="Pending">Pending</option>
        <option value="Ongoing">Ongoing</option>
        <option value="Completed">Completed</option>
      </select>

      <label for="description">Project Description</label>
      <textarea id="description" name="description" rows="3"></textarea>

      <label for="team">Assign Team (Hold Ctrl to select multiple)</label>
      <select id="team" name="team[]" multiple>
        <?php while($row = $employees->fetch_assoc()): ?>
          <option value="<?= $row['id'] ?>">
            <?= htmlspecialchars($row['full_name']) ?> (R<?= number_format($row['salary_per_hour'],2) ?>/hr)
          </option>
        <?php endwhile; ?>
      </select>

      <button type="submit">Create Project</button>
    </form>

    <!-- Back to Projects button -->
    <a href="project_list.php" class="back-btn">← Back to Projects</a>
  </div>
</body>
</html>
