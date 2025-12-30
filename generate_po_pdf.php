<?php
require 'db.php';
require('fpdf/fpdf.php');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid PO ID");
}

$po_id = intval($_GET['id']);

// Fetch PO details
$sql = "SELECT po.*, s.company_name AS supplier_name, s.email AS supplier_email, s.phone AS supplier_phone, 
               p.name AS project_name 
        FROM purchase_orders po
        LEFT JOIN suppliers s ON po.supplier_id = s.id
        LEFT JOIN projects p ON po.project_id = p.id
        WHERE po.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $po_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Purchase Order not found.");
}

$po = $result->fetch_assoc();

class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial','B',14);
        $this->Cell(0,10,'Purchase Order',0,1,'C');
        $this->Ln(5);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Generated on '.date("Y-m-d H:i"),0,0,'C');
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial','',12);

// Company Info (you can update with your own info)
$pdf->Cell(100,6,'Yeneliswa Construction',0,1);
$pdf->Cell(100,6,'123 Main Street, Gauteng',0,1);
$pdf->Cell(100,6,'Email: info@yeneliswa.co.za',0,1);
$pdf->Ln(8);

// PO Metadata
$pdf->SetFont('Arial','B',12);
$pdf->Cell(40,6,'PO Number:',0,0);
$pdf->SetFont('Arial','',12);
$pdf->Cell(60,6,$po['id'],0,1);

$pdf->SetFont('Arial','B',12);
$pdf->Cell(40,6,'Status:',0,0);
$pdf->SetFont('Arial','',12);
$pdf->Cell(60,6,ucfirst($po['status']),0,1);

$pdf->SetFont('Arial','B',12);
$pdf->Cell(40,6,'Date:',0,0);
$pdf->SetFont('Arial','',12);
$pdf->Cell(60,6,date("d M Y", strtotime($po['created_at'])),0,1);
$pdf->Ln(8);

// Supplier Info
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,6,'Supplier Details',0,1);
$pdf->SetFont('Arial','',12);
$pdf->Cell(100,6,'Name: '.$po['supplier_name'],0,1);
$pdf->Cell(100,6,'Email: '.$po['supplier_email'],0,1);
$pdf->Cell(100,6,'Phone: '.$po['supplier_phone'],0,1);
$pdf->Ln(8);

// Project Info
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,6,'Project Details',0,1);
$pdf->SetFont('Arial','',12);
$pdf->Cell(100,6,'Project: '.$po['project_name'],0,1);
$pdf->Ln(8);

// Amount
$pdf->SetFont('Arial','B',12);
$pdf->Cell(40,6,'Total Amount:',0,0);
$pdf->SetFont('Arial','',12);
$pdf->Cell(60,6,'R '.number_format($po['amount'], 2),0,1);

$pdf->Ln(20);

// Signature placeholders
$pdf->Cell(90,10,'________________________',0,0);
$pdf->Cell(90,10,'________________________',0,1);
$pdf->Cell(90,6,'Authorized By',0,0);
$pdf->Cell(90,6,'Supplier Representative',0,1);

$pdf->Output('I', 'Purchase_Order_'.$po['id'].'.pdf');
