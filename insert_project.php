<?php
require 'db.php';

$client = $_POST['client'];
$project_name = $_POST['project_name'];
$budget = $_POST['budget'];
$estimated_cost = $_POST['estimated_cost'];
$revenue = $_POST['revenue'];
$quoted_amount = $_POST['quoted_amount'] ?? 0;
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];
$status = $_POST['status'];
$description = $_POST['description'];
$team = $_POST['team'] ?? [];

// Insert project into projects table
$stmt = $conn->prepare("INSERT INTO projects (client_name, name, budget, estimated_cost, revenue, quoted_amount, start_date, end_date, status, description)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssddddssss", $client, $project_name, $budget, $estimated_cost, $revenue, $quoted_amount, $start_date, $end_date, $status, $description);
$stmt->execute();
$project_id = $stmt->insert_id;

// Insert into project_team table if team members selected
if (!empty($team)) {
    $stmt_team = $conn->prepare("INSERT INTO project_team (project_id, employee_id) VALUES (?, ?)");
    foreach ($team as $emp_id) {
        $stmt_team->bind_param("ii", $project_id, $emp_id);
        $stmt_team->execute();
    }
}

header("Location: project_list.php");
exit;
?>
