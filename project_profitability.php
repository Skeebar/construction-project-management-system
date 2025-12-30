<?php
require 'db.php';
include 'nav.php';

$projectId = intval($_GET['id'] ?? 0);

// Fetch project
$project = $conn->query("SELECT * FROM projects WHERE id = $projectId")->fetch_assoc();
if (!$project) {
    die("Project not found.");
}

$quoted = $project['quoted_amount'] ?? 0;
$budget = $project['budget'] ?? 0;

// Materials cost
$materials = $conn->query("SELECT * FROM materials WHERE project_id = $projectId");
$totalMaterialCost = 0;
while ($row = $materials->fetch_assoc()) {
    $totalMaterialCost += $row['quantity'] * $row['unit_price'];
}

// Invoices total
$invoices = $conn->query("SELECT SUM(amount) as total_invoiced FROM invoices WHERE project_id = $projectId");
$invoiceData = $invoices->fetch_assoc();
$totalInvoiced = $invoiceData['total_invoiced'] ?? 0;

// Profit/loss
$totalCost = $totalMaterialCost; // You could also add labor/other costs here
$profitOrLoss = $totalInvoiced - $totalCost;
$isLoss = $profitOrLoss < 0;

// Diagnosis
$advice = "";
if ($isLoss) {
    if ($totalMaterialCost > $quoted) {
        $advice .= "⚠️ Materials cost exceeded the quoted amount. Consider stricter procurement controls.<br>";
    }
    if ($quoted < $budget) {
        $advice .= "⚠️ Quoted amount was lower than allocated budget. Revisit estimation practices.<br>";
    }
    if ($totalInvoiced < $quoted) {
        $advice .= "⚠️ You invoiced less than the quoted amount. Ensure all work is billed.<br>";
    }
    if ($advice === "") {
        $advice = "⚠️ The project ran at a loss. Review overheads, labor, and unforeseen expenses.";
    }
} else {
    $advice = "✅ The project is profitable. Keep following your current costing and invoicing practices.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Project Profitability</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;700&family=Urbanist:wght@400;600&display=swap" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Space Grotesk', sans-serif;
            margin: 0;
            padding: 40px;
            background: #f8f9fc;
            color: #222;
        }
        .container {
            max-width: 900px;
            margin: auto;
            background: #fff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
        }
        h1 {
            font-family: 'Urbanist', sans-serif;
            color: #003366;
            margin-bottom: 20px;
        }
        .summary {
            margin-bottom: 25px;
        }
        .summary p {
            font-size: 16px;
            margin: 6px 0;
        }
        .advice {
            margin-top: 20px;
            padding: 15px;
            border-radius: 10px;
            font-weight: 600;
        }
        .loss {
            background: #ffe5e5;
            color: #a30000;
            border: 1px solid #ffcccc;
        }
        .profit {
            background: #e5ffe5;
            color: #006600;
            border: 1px solid #b3ffb3;
        }
        canvas {
            margin-top: 30px;
        }
        a.back-btn {
            display: inline-block;
            margin-top: 25px;
            padding: 10px 20px;
            background: #004080;
            color: #fff;
            border-radius: 8px;
            text-decoration: none;
        }
        a.back-btn:hover {
            background: #00264d;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Project Profitability: <?= htmlspecialchars($project['name']) ?></h1>

    <div class="summary">
        <p><strong>Quoted Amount:</strong> R<?= number_format($quoted, 2) ?></p>
        <p><strong>Budget:</strong> R<?= number_format($budget, 2) ?></p>
        <p><strong>Total Invoiced:</strong> R<?= number_format($totalInvoiced, 2) ?></p>
        <p><strong>Materials Cost:</strong> R<?= number_format($totalMaterialCost, 2) ?></p>
        <p><strong>Total Profit/Loss:</strong> 
            <span style="color:<?= $isLoss ? 'red' : 'green' ?>">
                R<?= number_format($profitOrLoss, 2) ?>
            </span>
        </p>
    </div>

    <div class="advice <?= $isLoss ? 'loss' : 'profit' ?>">
        <?= $advice ?>
    </div>

    <canvas id="profitChart"></canvas>

    <a href="view_project.php?id=<?= $projectId ?>" class="back-btn">⬅ Back to Project</a>
</div>

<script>
const ctx = document.getElementById('profitChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Quoted Amount', 'Invoiced', 'Costs', 'Profit/Loss'],
        datasets: [{
            label: 'Amount (R)',
            data: [
                <?= $quoted ?>,
                <?= $totalInvoiced ?>,
                <?= $totalCost ?>,
                <?= $profitOrLoss ?>
            ],
            backgroundColor: [
                'rgba(54, 162, 235, 0.7)',
                'rgba(75, 192, 192, 0.7)',
                'rgba(255, 99, 132, 0.7)',
                '<?= $isLoss ? "rgba(255, 80, 80, 0.7)" : "rgba(0, 200, 83, 0.7)" ?>'
            ],
            borderWidth: 1
        }]
    },
    options: {
        plugins: {
            title: {
                display: true,
                text: 'Project Profitability Breakdown'
            }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>
</body>
</html>
