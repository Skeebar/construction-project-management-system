<?php
require 'db.php';
require 'fpdf/fpdf.php'; // Make sure path is correct

$clientId = intval($_GET['id'] ?? 0);
if (!$clientId) die("Client ID missing.");

// Fetch client info
$clientResult = $conn->query("SELECT * FROM clients WHERE id = $clientId");
$client = $clientResult->fetch_assoc();
if (!$client) die("Client not found.");

// Fetch past projects
$projects = $conn->query("SELECT * FROM projects WHERE client_name = '" . $conn->real_escape_string($client['name']) . "' ORDER BY start_date DESC");

// Fetch paid invoices
$invoices = $conn->query("SELECT * FROM invoices WHERE client_id = $clientId AND status = 'Paid' ORDER BY invoice_date DESC");

// Init PDF
$pdf = new FPDF();
$pdf->AddPage();

// Add custom fonts
$pdf->AddFont('SpaceGrotesk','', 'SpaceGrotesk-Regular.php');
$pdf->AddFont('SpaceGrotesk','B','SpaceGrotesk-Bold.php');
$pdf->AddFont('Urbanist','', 'Urbanist-Regular.php');

// Title
$pdf->SetFont('SpaceGrotesk','B',18);
$pdf->Cell(0,10,'Client Report: '.$client['name'],0,1,'C');
$pdf->Ln(5);

// Client Details
$pdf->SetFont('Urbanist','',12);
$pdf->Cell(0,8,'Client Details',0,1);
$pdf->SetFont('Urbanist','',11);
$pdf->Cell(0,8,'Email: '.$client['email'],0,1);
$pdf->Cell(0,8,'Phone: '.$client['phone'],0,1);
$pdf->MultiCell(0,8,'Address: '.$client['address'],0,1);
$pdf->Ln(5);

// Past Projects
$pdf->SetFont('Urbanist','',12);
$pdf->Cell(0,8,'Past Projects',0,1);
$pdf->SetFont('Urbanist','',11);
$pdf->Cell(60,8,'Project Name',1);
$pdf->Cell(30,8,'Start',1);
$pdf->Cell(30,8,'End',1);
$pdf->Cell(40,8,'Status',1);
$pdf->Ln();

$pdf->SetFont('Urbanist','',10);
if ($projects->num_rows > 0) {
    while ($proj = $projects->fetch_assoc()) {
        $pdf->Cell(60,8,$proj['name'],1);
        $pdf->Cell(30,8,$proj['start_date'],1);
        $pdf->Cell(30,8,$proj['end_date'],1);
        $pdf->Cell(40,8,$proj['status'],1);
        $pdf->Ln();
    }
} else {
    $pdf->Cell(160,8,'No projects found',1,1,'C');
}
$pdf->Ln(5);

// Paid Invoices
$pdf->SetFont('Urbanist','',12);
$pdf->Cell(0,8,'Paid Invoices',0,1);
$pdf->SetFont('Urbanist','',11);
$pdf->Cell(40,8,'Invoice #',1);
$pdf->Cell(40,8,'Date',1);
$pdf->Cell(40,8,'Amount (R)',1);
$pdf->Ln();

$pdf->SetFont('Urbanist','',10);
$totalPaid = 0;
if ($invoices->num_rows > 0) {
    while ($inv = $invoices->fetch_assoc()) {
        $pdf->Cell(40,8,($inv['invoice_number'] ?? $inv['id']),1);
        $pdf->Cell(40,8,$inv['invoice_date'],1);
        $pdf->Cell(40,8,number_format($inv['amount'],2),1);
        $pdf->Ln();
        $totalPaid += $inv['amount'];
    }
} else {
    $pdf->Cell(120,8,'No paid invoices',1,1,'C');
}

$pdf->Ln(5);
$pdf->SetFont('Urbanist','',12);
$pdf->Cell(0,8,'Total Paid: R '.number_format($totalPaid,2),0,1);

// Output PDF
$pdf->Output('I','Client_Report_'.$client['name'].'.pdf');
