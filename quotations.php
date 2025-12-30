<?php
require 'db.php';
include 'nav.php';

// Fetch quotations
$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';

$query = "SELECT q.*, c.name AS client_name 
          FROM quotations q 
          LEFT JOIN clients c ON q.client_name = c.name
          WHERE 1 ";


if ($search) {
    $searchEsc = $conn->real_escape_string($search);
    $query .= " AND (c.name LIKE '%$searchEsc%' OR q.quotation_number LIKE '%$searchEsc%')";
}

if ($statusFilter) {
    $statusEsc = $conn->real_escape_string($statusFilter);
    $query .= " AND q.status = '$statusEsc'";
}

$query .= " ORDER BY q.created_at DESC";
$quotations = $conn->query($query);

// Count totals
$totalQuotations = $quotations->num_rows;
$pendingQuotes = $conn->query("SELECT COUNT(*) AS total FROM quotations WHERE status='Pending'")->fetch_assoc()['total'] ?? 0;
$approvedQuotes = $conn->query("SELECT COUNT(*) AS total FROM quotations WHERE status='Approved'")->fetch_assoc()['total'] ?? 0;
$rejectedQuotes = $conn->query("SELECT COUNT(*) AS total FROM quotations WHERE status='Rejected'")->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Quotations - Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600;700&family=Urbanist:wght@400;600;700&display=swap" rel="stylesheet">
<style>
body {font-family:'Space Grotesk',sans-serif; background:#f4f7fc; margin:0; padding:0;}
.container {max-width:1200px; margin:auto; padding:20px;}
h1 {font-family:'Urbanist',sans-serif; margin-bottom:20px;}
.cards {display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:20px; margin-bottom:30px;}
.card {background:white; padding:20px; border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,0.06);}
.card h2 {font-size:1rem; color:#555; margin:0 0 5px;}
.card p {font-family:'Urbanist',sans-serif; font-size:1.3rem; font-weight:700; margin:0;}
.search-filter {display:flex; flex-wrap:wrap; gap:10px; margin-bottom:20px;}
.search-filter input, .search-filter select, .search-filter button {padding:8px 12px; border-radius:6px; border:1px solid #ccc;}
.search-filter button {background:#2563eb; color:white; border:none; cursor:pointer;}
.search-filter button:hover {background:#1e40af;}
table {width:100%; border-collapse:collapse; background:white; border-radius:10px; overflow:hidden; box-shadow:0 6px 18px rgba(0,0,0,0.06);}
th,td {padding:12px; text-align:left; border-bottom:1px solid #eee;}
th {background:#1e3a8a; color:white;}
tr:hover {background:#f1f5f9;}
a.button {padding:6px 12px; background:#10b981; color:white; text-decoration:none; border-radius:6px;}
a.button:hover {background:#059669;}
</style>
</head>
<body>
<div class="container">
  <h1>Quotations</h1>

  <div class="cards">
    <div class="card"><h2>Total Quotations</h2><p><?= $totalQuotations ?></p></div>
    <div class="card"><h2>Pending</h2><p><?= $pendingQuotes ?></p></div>
    <div class="card"><h2>Approved</h2><p><?= $approvedQuotes ?></p></div>
    <div class="card"><h2>Rejected</h2><p><?= $rejectedQuotes ?></p></div>
  </div>

  <div class="search-filter">
    <form method="GET">
      <input type="text" name="search" placeholder="Search by client or quotation #" value="<?= htmlspecialchars($search) ?>">
      <select name="status">
        <option value="">All Status</option>
        <option value="Pending" <?= $statusFilter=='Pending'?'selected':'' ?>>Pending</option>
        <option value="Approved" <?= $statusFilter=='Approved'?'selected':'' ?>>Approved</option>
        <option value="Rejected" <?= $statusFilter=='Rejected'?'selected':'' ?>>Rejected</option>
      </select>
      <button type="submit">Filter</button>
      <a href="add_quotation.php" class="button">+ Add New Quotation</a>
    </form>
  </div>

  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Quotation #</th>
        <th>Client</th>
        <th>Date</th>
        <th>Amount (R)</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if($quotations->num_rows>0): ?>
        <?php while($q = $quotations->fetch_assoc()): ?>
        <tr>
          <td><?= $q['id'] ?></td>
          <td><?= $q['quotation_number'] ?? $q['id'] ?></td>
          <td><?= htmlspecialchars($q['client_name']) ?></td>
          <td><?= $q['quotation_date'] ?></td>
          <td><?= number_format($q['total_amount'],2) ?></td>
          <td><?= $q['status'] ?></td>
          <td>
            <a href="view_quotation.php?id=<?= $q['id'] ?>" class="button">View</a>
            <a href="edit_quotation.php?id=<?= $q['id'] ?>" class="button" style="background:#f59e0b;">Edit</a>
          </td>
        </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="7" style="text-align:center;">No quotations found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
</body>
</html>
