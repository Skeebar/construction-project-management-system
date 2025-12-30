<?php
require 'db.php';
include 'nav.php'; // ✅ include navigation

$result = $conn->query("SELECT * FROM clients ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Client List</title>
<style>
  body {
    font-family: Arial, sans-serif;
    background: #eef2f7;
    margin: 0; /* remove margin to align with nav */
  }
  .container {
    max-width: 900px;
    margin: 100px auto 30px; /* push down below nav */
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
  }
  h1 {
    text-align: center;
    color: #004080;
  }
  a.button {
    display: inline-block;
    margin-bottom: 20px;
    background-color: #004080;
    color: white;
    padding: 10px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: bold;
    transition: background-color 0.3s ease;
  }
  a.button:hover {
    background-color: #00264d;
  }
  table {
    width: 100%;
    border-collapse: collapse;
  }
  th, td {
    padding: 12px 15px;
    border-bottom: 1px solid #ddd;
    text-align: left;
  }
  th {
    background-color: #004080;
    color: white;
    text-transform: uppercase;
  }
  tr:hover {
    background-color: #f5f9ff;
  }
  a.view-link {
    color: #004080;
    font-weight: 600;
    text-decoration: none;
  }
  a.view-link:hover {
    text-decoration: underline;
  }
</style>
</head>
<body>

<div class="container">
  <h1>Clients</h1>
  <a href="add_client.php" class="button">+ Add New Client</a>

  <table>
    <thead>
      <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Address</th>
        <th>Profile</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
        <td><?= htmlspecialchars($row['phone']) ?></td>
        <td><?= htmlspecialchars($row['address']) ?></td>
        <td><a href="client_profile.php?id=<?= $row['id'] ?>" class="view-link">View</a></td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

</body>
</html>
