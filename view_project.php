<?php
require 'db.php';
include 'nav.php';

$projectId = intval($_GET['id'] ?? 0);

$project = $conn->query("SELECT * FROM projects WHERE id = $projectId")->fetch_assoc();
$quoted = $project['quoted_amount'] ?? 0;
$budget = $project['budget'] ?? 0;

$teamResult = $conn->query("
    SELECT e.full_name 
    FROM project_team pt 
    JOIN employees e ON pt.employee_id = e.id 
    WHERE pt.project_id = $projectId
");

$materials = $conn->query("SELECT * FROM materials WHERE project_id = $projectId");

$totalMaterialCost = 0;
$materialRows = '';
while ($row = $materials->fetch_assoc()) {
    $lineTotal = $row['quantity'] * $row['unit_price'];
    $totalMaterialCost += $lineTotal;
    $materialRows .= "
        <tr>
            <td>{$row['material_name']}</td>
            <td>{$row['quantity']}</td>
            <td>R" . number_format($row['unit_price'], 2) . "</td>
            <td>R" . number_format($lineTotal, 2) . "</td>
        </tr>
    ";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Project</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;700&family=Urbanist:wght@400;600&display=swap" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Space Grotesk', sans-serif;
            margin: 0;
            padding: 40px;
            background: url('bg.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #222;
        }
        .container {
            max-width: 1000px;
            margin: auto;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
            color: #222;
        }
        h1, h2 {
            font-family: 'Urbanist', sans-serif;
            color: #003366;
            margin-bottom: 20px;
        }
        .section {
            margin-bottom: 40px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fefefe;
            border-radius: 12px;
            overflow: hidden;
            margin-top: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        th, td {
            padding: 12px 18px;
            border-bottom: 1px solid #ddd;
            text-align: left;
            color: #333;
        }
        th {
            background: #004080;
            color: #fff;
            text-transform: uppercase;
            font-weight: 600;
        }
        ul {
            padding-left: 20px;
            color: #222;
        }
        a.btn {
            display: inline-block;
            margin-top: 15px;
            margin-right: 10px;
            padding: 10px 25px;
            background-color: #004080;
            color: #fff;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }
        a.btn:hover {
            background-color: #00264d;
        }
        canvas {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 30px auto 0 auto;
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Project: <?= htmlspecialchars($project['name']) ?></h1>

        <div class="section">
            <h2>Project Details</h2>
            <p><strong>Client:</strong> <?= htmlspecialchars($project['client_name']) ?></p>
            <p><strong>Start Date:</strong> <?= htmlspecialchars($project['start_date']) ?></p>
            <p><strong>End Date:</strong> <?= htmlspecialchars($project['end_date']) ?></p>
            <p><strong>Budget:</strong> R<?= number_format($budget, 2) ?></p>
            <p><strong>Quoted Amount:</strong> R<?= number_format($quoted, 2) ?></p>
        </div>

        <div class="section">
            <h2>Assigned Team</h2>
            <ul>
                <?php while ($member = $teamResult->fetch_assoc()): ?>
                    <li><?= htmlspecialchars($member['full_name']) ?></li>
                <?php endwhile; ?>
            </ul>
        </div>

        <div class="section">
            <h2>Materials Used</h2>
            <table>
                <thead>
                    <tr>
                        <th>Material</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?= $materialRows ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3">Total Material Cost</th>
                        <th>R<?= number_format($totalMaterialCost, 2) ?></th>
                    </tr>
                </tfoot>
            </table>
            <a href="add_material.php?project_id=<?= $projectId ?>" class="btn">➕ Add Material</a>
        </div>

        <div class="section">
            <h2>Actions</h2>
            <a href="project_profitability.php?id=<?= $projectId ?>" class="btn">📊 Project Profitability</a>
            <a href="project_invoices.php?project_id=<?= $projectId ?>" class="btn">🧾 Invoice Project</a>
        </div>

        <div class="section">
            <h2>Cost vs Quoted Overview</h2>
            <canvas id="profitChart"></canvas>
        </div>
    </div>

    <script>
    const ctx = document.getElementById('profitChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Quoted Amount', 'Spent on Materials', 'Estimated Profit'],
            datasets: [{
                label: 'Amount (R)',
                data: [<?= $quoted ?>, <?= $totalMaterialCost ?>, <?= max(0, $quoted - $totalMaterialCost) ?>],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(75, 192, 192, 0.7)'
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(75, 192, 192, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            plugins: {
                title: {
                    display: true,
                    text: 'Profitability Breakdown'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    </script>
</body>
</html>
