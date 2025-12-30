<?php
include 'db.php';
include 'nav.php';

// Active Projects
$res = $conn->query("SELECT COUNT(*) AS total FROM projects WHERE status = 'Ongoing'");
$activeProjects = $res->fetch_assoc()['total'] ?? 0;

// Pending Quotations
$res = $conn->query("SELECT COUNT(*) AS total FROM quotations WHERE status = 'Pending'");
$pendingQuotes = $res->fetch_assoc()['total'] ?? 0;

// Total Quotations
$res = $conn->query("SELECT COUNT(*) AS total FROM quotations");
$totalQuotes = $res->fetch_assoc()['total'] ?? 0;

// Employees
$res = $conn->query("SELECT COUNT(*) AS total FROM employees");
$totalEmployees = $res->fetch_assoc()['total'] ?? 0;

// Clients
$res = $conn->query("SELECT COUNT(*) AS total FROM clients");
$totalClients = $res->fetch_assoc()['total'] ?? 0;

// Calculate total income: transactions + paid invoices
$res = $conn->query("
    SELECT SUM(amount) AS total FROM transactions WHERE type='income'
");
$transactionIncome = $res->fetch_assoc()['total'] ?? 0;

$res = $conn->query("
    SELECT SUM(amount) AS total FROM invoices WHERE status='Paid'
");
$paidInvoices = $res->fetch_assoc()['total'] ?? 0;

$totalIncome = $transactionIncome + $paidInvoices;

// Calculate total expenses: transactions + expenses table
$res = $conn->query("
    SELECT SUM(amount) AS total FROM transactions WHERE type='expense'
");
$transactionExpense = $res->fetch_assoc()['total'] ?? 0;

$res = $conn->query("SELECT SUM(amount) AS total FROM expenses");
$totalExpenses = $res->fetch_assoc()['total'] ?? 0;

$totalExpense = $transactionExpense + $totalExpenses;

// Profit/Loss
$profitLoss = $totalIncome - $totalExpense;

// Monthly Expenses & Income (this month)
$currentMonth = date('Y-m');
$res = $conn->query("
    SELECT SUM(amount) AS total 
    FROM (
        SELECT amount, transaction_date as date FROM transactions
        UNION ALL
        SELECT amount, invoice_date as date FROM invoices WHERE status='Paid'
        UNION ALL
        SELECT amount, expense_date as date FROM expenses
    ) t
    WHERE DATE_FORMAT(date, '%Y-%m') = '$currentMonth'
    AND amount < 0
");
$monthlyExpenses = abs($res->fetch_assoc()['total'] ?? 0);

$res = $conn->query("
    SELECT SUM(amount) AS total 
    FROM (
        SELECT amount, transaction_date as date FROM transactions WHERE type='income'
        UNION ALL
        SELECT amount, invoice_date as date FROM invoices WHERE status='Paid'
    ) t
    WHERE DATE_FORMAT(date, '%Y-%m') = '$currentMonth'
");
$monthlyIncome = $res->fetch_assoc()['total'] ?? 0;

// Project Status Breakdown
$statusCounts = ['Ongoing'=>0, 'Completed'=>0, 'Delayed'=>0];
$res = $conn->query("SELECT status, COUNT(*) AS count FROM projects GROUP BY status");
while ($row = $res->fetch_assoc()) {
    $statusCounts[$row['status']] = $row['count'];
}

// Income & Expense Data for past 6 months
$incomeData = [];
$expenseData = [];
$monthsLabel = [];
for ($i=5; $i>=0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $monthsLabel[] = date('M', strtotime("-$i months"));

    // Income for month
    $res = $conn->query("
        SELECT SUM(amount) AS total
        FROM (
            SELECT amount, transaction_date AS date FROM transactions WHERE type='income'
            UNION ALL
            SELECT amount, invoice_date AS date FROM invoices WHERE status='Paid'
        ) t
        WHERE DATE_FORMAT(date, '%Y-%m') = '$month'
    ");
    $incomeData[] = round($res->fetch_assoc()['total'] ?? 0,2);

    // Expenses for month
    $res = $conn->query("
        SELECT SUM(amount) AS total
        FROM (
            SELECT amount, transaction_date AS date FROM transactions WHERE type='expense'
            UNION ALL
            SELECT amount, expense_date AS date FROM expenses
        ) t
        WHERE DATE_FORMAT(date, '%Y-%m') = '$month'
    ");
    $expenseData[] = round($res->fetch_assoc()['total'] ?? 0,2);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - Construction Management</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600;700&family=Urbanist:wght@400;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
  body { margin:0; font-family:'Space Grotesk',sans-serif; background:#f4f7fc; color:#333;}
  nav { background:#1e3a8a; padding:10px 20px; display:flex; gap:15px; flex-wrap:wrap; }
  nav a { color:white; text-decoration:none; font-weight:600; font-family:'Urbanist',sans-serif; }
  nav a:hover { text-decoration:underline; }
  .container { max-width:1200px; margin:auto; padding:20px; }
  header { display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; }
  header h1 { font-family:'Urbanist',sans-serif; font-size:2rem; }
  .cards { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:20px; margin-bottom:40px; }
  .card { background:white; padding:20px; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.05); display:flex; align-items:center; gap:10px;}
  .card h2 { font-size:1.2rem; margin-bottom:5px; }
  .card .number { font-family:'Urbanist',sans-serif; font-weight:700; font-size:1.5rem; }
  .charts-row { display:flex; flex-wrap:wrap; gap:20px; }
  .chart-container { flex:1; min-width:300px; background:white; padding:20px; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.05); margin-bottom:30px; }
  .quick-actions { display:flex; flex-wrap:wrap; gap:10px; margin-bottom:30px; }
  .quick-actions a { background:#2563eb; color:white; text-decoration:none; padding:10px 16px; border-radius:8px; font-family:'Urbanist',sans-serif; }
  .quick-actions a:hover { background:#1e40af; }
</style>
</head>
<body>
<div class="container">
  <header><h1>Dashboard</h1></header>

  <div class="cards">
    <div class="card"><div>🏗️</div><div><h2>Active Projects</h2><p class="number"><?= $activeProjects ?></p></div></div>
    <div class="card"><div>📄</div><div><h2>Pending Quotations</h2><p class="number"><?= $pendingQuotes ?></p></div></div>
    <div class="card"><div>💰</div><div><h2>Total Income</h2><p class="number">R <?= number_format($totalIncome,2) ?></p></div></div>
    <div class="card"><div>📊</div><div><h2>Profit / Loss</h2><p class="number">R <?= number_format($profitLoss,2) ?></p></div></div>
    <div class="card"><div>👥</div><div><h2>Clients</h2><p class="number"><?= $totalClients ?></p></div></div>
    <div class="card"><div>📝</div><div><h2>Total Quotations</h2><p class="number"><?= $totalQuotes ?></p></div></div>
  </div>

  <div class="charts-row">
    <div class="chart-container">
      <h2>Project Status Breakdown</h2>
      <canvas id="projectStatusChart"></canvas>
    </div>
    <div class="chart-container">
      <h2>Income vs Expenses (Last 6 Months)</h2>
      <canvas id="incomeExpenseChart"></canvas>
    </div>
  </div>

  <div class="quick-actions">
    <a href="add_project.php">Add New Project</a>
    <a href="add_quotation.php">Add Quotation</a>
    <a href="add_site_log.php">Log Site Activity</a>
    <a href="add_lead.php">Add Lead</a>
  </div>
</div>

<script>
const projectStatusCtx = document.getElementById('projectStatusChart').getContext('2d');
new Chart(projectStatusCtx, {
  type:'pie',
  data:{ labels:['Ongoing','Completed','Delayed'], datasets:[{ data:[<?= $statusCounts['Ongoing'] ?>,<?= $statusCounts['Completed'] ?>,<?= $statusCounts['Delayed'] ?>], backgroundColor:['#3b82f6','#10b981','#f59e0b'] }]},
  options:{ responsive:true, plugins:{ legend:{ position:'bottom' } } }
});

const incomeExpenseCtx = document.getElementById('incomeExpenseChart').getContext('2d');
new Chart(incomeExpenseCtx, {
  type:'bar',
  data:{ labels:<?= json_encode($monthsLabel) ?>, datasets:[ { label:'Income', backgroundColor:'#22c55e', data:<?= json_encode($incomeData) ?> }, { label:'Expenses', backgroundColor:'#ef4444', data:<?= json_encode($expenseData) ?> } ] },
  options:{ responsive:true, scales:{ y:{ beginAtZero:true } } }
});
</script>
</body>
</html>
