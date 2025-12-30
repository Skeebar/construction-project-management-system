<?php
require 'db.php';

// Get project ID from GET
$projectId = intval($_GET['id'] ?? 0);
if (!$projectId) {
    die("Invalid project ID");
}

// Fetch employees for the team select list
$employees = $conn->query("SELECT id, full_name FROM employees");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client = $_POST['client'];
    $project_name = $_POST['project_name'];
    $budget = $_POST['budget'];
    $estimated_cost = $_POST['estimated_cost'];
    $revenue = $_POST['revenue'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $status = $_POST['status'];
    $description = $_POST['description'];
    $team = $_POST['team'] ?? [];

    // Update projects table
    $stmt = $conn->prepare("UPDATE projects SET client_name=?, name=?, budget=?, estimated_cost=?, revenue=?, start_date=?, end_date=?, status=?, description=? WHERE id=?");
$stmt->bind_param("ssddsssssi", $client, $project_name, $budget, $estimated_cost, $revenue, $start_date, $end_date, $status, $description, $projectId);
$stmt->execute();


    // Delete existing team members for this project
    $conn->query("DELETE FROM project_team WHERE project_id = $projectId");

    // Insert new team members if any
    if (!empty($team)) {
        $stmt_team = $conn->prepare("INSERT INTO project_team (project_id, employee_id) VALUES (?, ?)");
        foreach ($team as $emp_id) {
            $stmt_team->bind_param("ii", $projectId, $emp_id);
            $stmt_team->execute();
        }
    }

    header("Location: view_project.php?id=$projectId");
    exit;
}

// Fetch current project data
$project = $conn->query("SELECT * FROM projects WHERE id = $projectId")->fetch_assoc();

// Fetch current team member IDs for pre-select
$currentTeamResult = $conn->query("SELECT employee_id FROM project_team WHERE project_id = $projectId");
$currentTeam = [];
while ($row = $currentTeamResult->fetch_assoc()) {
    $currentTeam[] = $row['employee_id'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Edit Project</title>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;700&family=Urbanist:wght@400;600&display=swap" rel="stylesheet" />
  <style>
    body {
      font-family: 'Space Grotesk', sans-serif;
      background: #f4f7f8;
      padding: 30px;
    }
    .container {
      max-width: 700px;
      background: white;
      padding: 25px 35px;
      border-radius: 10px;
      box-shadow: 0 6px 18px rgba(0,0,0,0.1);
      margin: auto;
    }
    h2 {
      font-family: 'Urbanist', sans-serif;
      margin-bottom: 20px;
      color: #2c3e50;
    }
    label {
      display: block;
      margin-top: 15px;
      font-weight: 600;
      color: #34495e;
    }
    input, select, textarea {
      width: 100%;
      padding: 10px;
      margin-top: 6px;
      border-radius: 6px;
      border: 1px solid #ccc;
      font-size: 15px;
      font-family: 'Space Grotesk', sans-serif;
    }
    select[multiple] {
      height: 120px;
    }
    button {
      margin-top: 25px;
      width: 100%;
      padding: 12px;
      background-color: #3498db;
      border: none;
      border-radius: 8px;
      color: white;
      font-size: 17px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    button:hover {
      background-color: #2980b9;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Edit Project: <?= htmlspecialchars($project['name']) ?></h2>
    <form method="POST" action="">
      <label for="client">Client Name *</label>
      <input type="text" id="client" name="client" required value="<?= htmlspecialchars($project['client_name']) ?>" />

      <label for="project_name">Project Name *</label>
      <input type="text" id="project_name" name="project_name" required value="<?= htmlspecialchars($project['name']) ?>" />

      <label for="budget">Budget (R)</label>
      <input type="number" id="budget" name="budget" step="0.01" value="<?= htmlspecialchars($project['budget']) ?>" />

      <label for="estimated_cost">Estimated Cost (R)</label>
      <input type="number" id="estimated_cost" name="estimated_cost" step="0.01" value="<?= htmlspecialchars($project['estimated_cost']) ?>" />

      <label for="revenue">Revenue (R)</label>
      <input type="number" id="revenue" name="revenue" step="0.01" value="<?= htmlspecialchars($project['revenue']) ?>" />

      <label for="start_date">Start Date</label>
      <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($project['start_date']) ?>" />

      <label for="end_date">End Date</label>
      <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($project['end_date']) ?>" />

      <label for="status">Status</label>
      <select id="status" name="status">
        <option value="Pending" <?= $project['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
        <option value="Ongoing" <?= $project['status'] === 'Ongoing' ? 'selected' : '' ?>>Ongoing</option>
        <option value="Completed" <?= $project['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
      </select>

      <label for="description">Project Description</label>
      <textarea id="description" name="description" rows="3"><?= htmlspecialchars($project['description']) ?></textarea>

      <label for="team">Assign Team (Ctrl+Click to select multiple)</label>
      <select id="team" name="team[]" multiple>
        <?php while ($row = $employees->fetch_assoc()): ?>
          <option value="<?= $row['id'] ?>" <?= in_array($row['id'], $currentTeam) ? 'selected' : '' ?>>
            <?= htmlspecialchars($row['full_name']) ?>
          </option>
        <?php endwhile; ?>
      </select>

      <button type="submit">Update Project</button>
    </form>
  </div>
</body>
</html>
