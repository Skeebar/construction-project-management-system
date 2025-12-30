<?php
require 'db.php';
include 'nav.php';

// Fetch projects
$projectsRes = $conn->query("SELECT * FROM projects");
$projects = []; while($row = $projectsRes->fetch_assoc()) $projects[$row['id']] = $row;

// Fetch project costs
$costsRes = $conn->query("SELECT * FROM project_costs ORDER BY date_incurred DESC");
$projectCosts = []; while($row = $costsRes->fetch_assoc()) $projectCosts[] = $row;

// Fetch project-related expenses
$expenseRes = $conn->query("SELECT * FROM expenses WHERE project_id IS NOT NULL ORDER BY expense_date DESC");
$projectExpenses = []; while($row = $expenseRes->fetch_assoc()) $projectExpenses[] = $row;

// Fetch paid invoices per project
// Fetch paid invoices per project
$invoiceRes = $conn->query("
    SELECT project_id, SUM(amount) AS income
    FROM invoices
    WHERE status='Paid' AND project_id IS NOT NULL
    GROUP BY project_id
");
$projectIncome = [];
while($row = $invoiceRes->fetch_assoc()) {
    $pid = intval($row['project_id']);
    $projectIncome[$pid] = floatval($row['income']);
}

// Calculate totals per project
$projectTotals = [];
foreach($projects as $pid => $p){
    $laborCost = 0;
    $otherCosts = 0;

    // Labor cost from team
    $teamRes = $conn->query("SELECT e.salary_per_hour
                             FROM project_team pt
                             JOIN employees e ON pt.employee_id = e.id
                             WHERE pt.project_id = $pid");

    $startDate = $p['start_date'] ?? null;
    $endDate   = $p['end_date'] ?? null;
    $daysWorked = 0;

    if ($startDate && $endDate) {
        $daysWorked = (strtotime($endDate) - strtotime($startDate)) / (60*60*24) + 1;
    }

    while($row = $teamRes->fetch_assoc()){
        $laborCost += $row['salary_per_hour'] * 8 * $daysWorked;
    }

    // Sum other project costs
    foreach($projectCosts as $c) if($c['project_id']==$pid) $otherCosts += $c['cost'];
    foreach($projectExpenses as $e) if($e['project_id']==$pid) $otherCosts += $e['amount'];

    $totalIncome = $projectIncome[$pid] ?? 0; // now should show paid invoices
    $totalCosts = $laborCost + $otherCosts;
    $profitLoss = $totalIncome - $totalCosts;
    $overBudget = $p['estimated_cost']>0 && $totalCosts > $p['estimated_cost'];

    $projectTotals[$pid] = [
        'laborCost' => $laborCost,
        'otherCosts'=> $otherCosts,
        'totalCosts'=> $totalCosts,
        'totalIncome'=> $totalIncome,
        'profitLoss'=> $profitLoss,
        'budget'=> $p['estimated_cost'],
        'overBudget'=> $overBudget
    ];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Project Costing Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Urbanist:wght@400;600&family=Space+Grotesk:wght@600&display=swap" rel="stylesheet">
<style>
body{font-family:'Urbanist',sans-serif;background:#f4f7fb;margin:0;padding:20px;}
.container{max-width:1200px;margin:auto;background:white;padding:20px;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.05);}
h1,h2{font-family:'Space Grotesk',sans-serif;color:#003366;margin:10px 0;}
.add-btn{background:#003366;color:white;padding:6px 12px;border-radius:6px;text-decoration:none;font-weight:600;float:right;}
.add-btn:hover{background:#001f4d;}
table{width:100%;border-collapse:collapse;margin-top:10px;font-size:14px;}
th,td{border:1px solid #ddd;padding:8px;text-align:left;}
th{background:#003366;color:white;}
tbody tr:hover{background:#f9fafc;}
.balance{font-weight:600;padding:4px 8px;border-radius:6px;display:inline-block;}
.balance.positive{background:#e6ffed;color:#16a34a;}
.balance.negative{background:#ffe5e5;color:#b91c1c;}
form{margin-top:15px;margin-bottom:20px;}
form input,form select{padding:6px;margin-right:6px;margin-bottom:6px;border-radius:6px;border:1px solid #ccc;}
form button{padding:6px 12px;border:none;border-radius:6px;background:#003366;color:white;cursor:pointer;}
form button:hover{background:#001f4d;}
</style>
</head>
<body>
<div class="container">
<h1>Project Costing <a href="add_transaction.php" class="add-btn">+ Add Transaction</a></h1>

<h2>Projects Overview</h2>
<table>
<thead>
<tr>
<th>#</th>
<th>Project</th>
<th>Budget (R)</th>
<th>Labor Cost (R)</th>
<th>Other Costs (R)</th>
<th>Total Costs (R)</th>
<th>Total Income (R)</th>
<th>Profit/Loss (R)</th>
<th>Status</th>
</tr>
</thead>
<tbody>
<?php $i=1; foreach($projects as $pid=>$p): ?>
<tr>
<td><?= $i++ ?></td>
<td><?= htmlspecialchars($p['name']) ?></td>
<td><?= number_format($projectTotals[$pid]['budget'],2) ?></td>
<td><?= number_format($projectTotals[$pid]['laborCost'],2) ?></td>
<td><?= number_format($projectTotals[$pid]['otherCosts'],2) ?></td>
<td><?= number_format($projectTotals[$pid]['totalCosts'],2) ?></td>
<td><?= number_format($projectTotals[$pid]['totalIncome'],2) ?></td>
<td><span class="balance <?= $projectTotals[$pid]['profitLoss']>=0?'positive':'negative' ?>">
<?= number_format($projectTotals[$pid]['profitLoss'],2) ?><?= $projectTotals[$pid]['overBudget']?' ⚠':'' ?></span></td>
<td><?= htmlspecialchars($p['status']??'') ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<h2>Add Project Cost</h2>
<form method="POST" enctype="multipart/form-data">
<select name="project_id" required>
<option value="">Select Project</option>
<?php foreach($projects as $pid=>$p): ?>
<option value="<?= $pid ?>"><?= htmlspecialchars($p['name']) ?></option>
<?php endforeach; ?>
</select>
<input type="text" name="category" placeholder="Category" required>
<input type="text" name="description" placeholder="Description" required>
<input type="number" step="0.01" name="cost" placeholder="Cost" required>
<input type="date" name="date_incurred" value="<?= date('Y-m-d') ?>" required>
<button type="submit" name="add_cost">Add Cost</button>
</form>

<h3>Or Upload CSV</h3>
<form method="POST" enctype="multipart/form-data">
<input type="file" name="file" required>
<button type="submit" name="upload">Upload CSV</button>
</form>
</div>
</body>
</html>
