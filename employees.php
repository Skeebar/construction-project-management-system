<?php
require 'db.php';
include 'nav.php';

// Handle search query
$search = $_GET['search'] ?? '';
$search_sql = "";
if ($search) {
    $search_esc = $conn->real_escape_string($search);
    $search_sql = " WHERE full_name LIKE '%$search_esc%' OR position LIKE '%$search_esc%' ";
}

$sql = "SELECT * FROM employees $search_sql ORDER BY full_name ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Employee List</title>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;700&family=Urbanist:wght@400;600&display=swap" rel="stylesheet" />
  <style>
    body {
      margin: 0;
      font-family: 'Space Grotesk', sans-serif;
      background: url('bg.jpg') no-repeat center center fixed;
      background-size: cover;
      color: #fff;
      min-height: 100vh;
      padding: 20px;
    }
    h1 {
      font-family: 'Urbanist', sans-serif;
      font-weight: 700;
      text-align: center;
      margin-bottom: 15px;
      color: #fff;
      text-shadow: 0 0 5px rgba(0,0,0,0.7);
    }
    .top-bar {
      max-width: 1000px;
      margin: 0 auto 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 10px;
    }
    .search-box {
      flex-grow: 1;
      display: flex;
      gap: 10px;
    }
    .search-box input[type="text"] {
      flex-grow: 1;
      padding: 10px 15px;
      border-radius: 10px;
      border: none;
      font-size: 16px;
      font-family: 'Space Grotesk', sans-serif;
      outline-offset: 2px;
      outline-color: transparent;
      transition: outline-color 0.3s ease;
    }
    .search-box input[type="text"]:focus {
      outline-color: #5a72ff;
    }
    .search-box button {
      padding: 10px 20px;
      border-radius: 10px;
      border: none;
      background-color: #3f51b5;
      color: white;
      font-weight: 600;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    .search-box button:hover {
      background-color: #2c3e91;
    }
    .add-btn {
      background: #28a745;
      color: white;
      padding: 10px 15px;
      border-radius: 8px;
      text-decoration: none;
      font-size: 14px;
      font-weight: 600;
      transition: background-color 0.3s ease;
      white-space: nowrap;
    }
    .add-btn:hover {
      background: #218838;
    }
    .table-container {
      max-width: 1000px;
      margin: 0 auto 40px;
      background: rgba(45, 28, 179, 0.7);
      border-radius: 20px;
      padding: 20px;
      backdrop-filter: blur(12px);
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
    }
    table {
      width: 100%;
      border-collapse: collapse;
      color: #fff;
    }
    th, td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      vertical-align: middle;
    }
    th {
      background: rgba(0, 0, 0, 0.25);
      font-weight: 600;
    }
    tr:hover {
      background: rgba(255, 255, 255, 0.1);
      cursor: default;
    }
    .actions button, .actions a {
      background: #3f51b5;
      border: none;
      color: white;
      padding: 6px 12px;
      border-radius: 6px;
      text-decoration: none;
      font-size: 14px;
      cursor: pointer;
      margin-right: 6px;
      transition: background-color 0.3s ease;
    }
    .actions button:hover, .actions a:hover {
      background: #2c3e91;
    }
    @media (max-width: 700px) {
      .top-bar {
        flex-direction: column;
        align-items: stretch;
      }
      table, thead, tbody, th, td, tr {
        display: block;
      }
      thead tr {
        display: none;
      }
      tr {
        margin-bottom: 15px;
        border-radius: 12px;
        background: rgba(0,0,0,0.3);
        padding: 10px;
      }
      td {
        padding-left: 50%;
        position: relative;
        text-align: right;
      }
      td::before {
        position: absolute;
        top: 12px;
        left: 15px;
        width: 45%;
        white-space: nowrap;
        font-weight: 700;
        text-align: left;
        content: attr(data-label);
        color: #ddd;
      }
      .actions {
        text-align: right;
      }
    }
  </style>
</head>
<body>
  <h1>Employee List</h1>

  <div class="top-bar">
    <div class="search-box">
      <form method="GET" action="">
        <input type="text" name="search" placeholder="Search by name or position" value="<?= htmlspecialchars($search) ?>" />
        <button type="submit">Search</button>
      </form>
    </div>
    <a href="add_employee.php" class="add-btn">+ Add Employee</a>
  </div>

  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>Full Name</th>
          <th>Position</th>
          <th>Salary per Hour (R)</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td data-label="Full Name"><?= htmlspecialchars($row['full_name']) ?></td>
              <td data-label="Position"><?= htmlspecialchars($row['position']) ?></td>
              <td data-label="Salary per Hour"><?= number_format($row['salary_per_hour'], 2) ?></td>
              <td data-label="Status"><?= htmlspecialchars($row['status']) ?></td>
              <td data-label="Actions" class="actions">
                <a href="edit_employee.php?id=<?= $row['id'] ?>">Edit</a>
                <form action="delete_employee.php" method="POST" style="display:inline;" onsubmit="return confirm('Delete this employee?');">
                  <input type="hidden" name="id" value="<?= $row['id'] ?>">
                  <button type="submit">Delete</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="5" style="text-align:center;">No employees found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
