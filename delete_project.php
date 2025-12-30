<?php
require 'db.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    // No project ID provided
    header("Location: project_list.php?msg=No+project+ID+provided");
    exit;
}

$projectId = (int)$_GET['id'];

// Check if project exists
$stmt = $conn->prepare("SELECT name FROM projects WHERE id = ?");
$stmt->bind_param("i", $projectId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Project not found
    header("Location: project_list.php?msg=Project+not+found");
    exit;
}

$project = $result->fetch_assoc();
$projectName = $project['name'];

// Begin transaction to delete project and related data
$conn->begin_transaction();

try {
    // Delete assigned team members
    $stmtDelTeam = $conn->prepare("DELETE FROM project_team WHERE project_id = ?");
    $stmtDelTeam->bind_param("i", $projectId);
    $stmtDelTeam->execute();

    // Delete materials
    $stmtDelMaterials = $conn->prepare("DELETE FROM materials WHERE project_id = ?");
    $stmtDelMaterials->bind_param("i", $projectId);
    $stmtDelMaterials->execute();

    // Delete the project itself
    $stmtDelProject = $conn->prepare("DELETE FROM projects WHERE id = ?");
    $stmtDelProject->bind_param("i", $projectId);
    $stmtDelProject->execute();

    $conn->commit();

    header("Location: project_list.php?msg=Project+\"".urlencode($projectName)."\"+deleted+successfully");
    exit;
} catch (Exception $e) {
    $conn->rollback();
    header("Location: project_list.php?msg=Error+deleting+project");
    exit;
}
?>
