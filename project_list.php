<?php include 'db.php'; ?>
<?php include 'header.html'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Projects</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <style>
    body { font-family: 'Urbanist', sans-serif; background: #f5f7fa; }
    h1 { font-family: 'Space Grotesk', sans-serif; }
    .table thead { background-color: #343a40; color: white; }
    .btn-action { margin-right: 5px; }
    .filter-form .form-control { margin-bottom: 10px; }
  </style>
</head>
<body>
  <div class="container py-5">
    <h1 class="mb-4">Project List</h1>

    <!-- Filter Form -->
    <form method="GET" class="row filter-form mb-4">
      <div class="col-md-3">
        <input type="text" name="client" value="<?php echo $_GET['client'] ?? ''; ?>" class="form-control" placeholder="Client Name">
      </div>
      <div class="col-md-3">
        <select name="status" class="form-control">
          <option value="">All Statuses</option>
          <option value="Ongoing" <?php if(($_GET['status'] ?? '') === 'Ongoing') echo 'selected'; ?>>Ongoing</option>
          <option value="Completed" <?php if(($_GET['status'] ?? '') === 'Completed') echo 'selected'; ?>>Completed</option>
          <option value="Cancelled" <?php if(($_GET['status'] ?? '') === 'Cancelled') echo 'selected'; ?>>Cancelled</option>
        </select>
      </div>
      <div class="col-md-3">
        <input type="date" name="start_date" value="<?php echo $_GET['start_date'] ?? ''; ?>" class="form-control" placeholder="Start Date">
      </div>
      <div class="col-md-3">
        <button type="submit" class="btn btn-primary w-100">Filter</button>
      </div>
    </form>

    <!-- Add Project Button -->
    <a href="add_project.php" class="btn btn-success mb-3">+ Add Project</a>

    <table class="table table-bordered table-hover">
      <thead>
        <tr>
          <th>#</th>
          <th>Project Name</th>
          <th>Client</th>
          <th>Status</th>
          <th>Start Date</th>
          <th>End Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $conditions = [];
        if (!empty($_GET['client'])) {
          $client = $conn->real_escape_string($_GET['client']);
          $conditions[] = "client_name LIKE '%$client%'";
        }
        if (!empty($_GET['status'])) {
          $status = $conn->real_escape_string($_GET['status']);
          $conditions[] = "status = '$status'";
        }
        if (!empty($_GET['start_date'])) {
          $start_date = $conn->real_escape_string($_GET['start_date']);
          $conditions[] = "start_date >= '$start_date'";
        }

        $where = count($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $query = "SELECT * FROM projects $where ORDER BY start_date DESC";
        $result = $conn->query($query);
        $i = 1;
        while ($row = $result->fetch_assoc()):
        ?>
          <tr>
            <td><?php echo $i++; ?></td>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td><?php echo htmlspecialchars($row['client_name']); ?></td>
            <td><?php echo $row['status']; ?></td>
            <td><?php echo $row['start_date']; ?></td>
            <td><?php echo $row['end_date']; ?></td>
            <td>
              <a href="view_project.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm btn-action">View</a>
              <a href="edit_project.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm btn-action">Edit</a>
              <a href="delete_project.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
