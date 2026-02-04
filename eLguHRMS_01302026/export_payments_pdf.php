<?php
require 'db_connection.php';
require('fpdf/fpdf.php'); // adjust path

$where = [];
if (!empty($_GET['or_no'])) {
    $or_no = $conn->real_escape_string($_GET['or_no']);
    $where[] = "p.or_no LIKE '%$or_no%'";
}
if (!empty($_GET['payor_name'])) {
    $payor_name = $conn->real_escape_string($_GET['payor_name']);
    $where[] = "p.payor_name LIKE '%$payor_name%'";
}
if (!empty($_GET['tax_year'])) {
    $tax_year = $conn->real_escape_string($_GET['tax_year']);
    $where[] = "p.tax_year = '$tax_year'";
}
$whereSQL = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

$sql = "SELECT p.or_no, p.payor_name, p.tax_year, p.assessed_value,
               p.basic_tax, p.sef_tax, p.discount, p.penalty, 
               p.total_paid, p.date_paid
        FROM payments p $whereSQL ORDER BY p.date_paid DESC";
$result = $conn->query($sql);

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Payments Report', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(20, 8, 'OR No', 1);
$pdf->Cell(40, 8, 'Payor', 1);
$pdf->Cell(20, 8, 'Year', 1);
$pdf->Cell(25, 8, 'Assessed', 1);
$pdf->Cell(20, 8, 'Basic', 1);
$pdf->Cell(20, 8, 'SEF', 1);
$pdf->Cell(20, 8, 'Disc', 1);
$pdf->Cell(20, 8, 'Penalty', 1);
$pdf->Cell(25, 8, 'Total Paid', 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 10);
while ($row = $result->fetch_assoc()) {
    $pdf->Cell(20, 8, $row['or_no'], 1);
    $pdf->Cell(40, 8, $row['payor_name'], 1);
    $pdf->Cell(20, 8, $row['tax_year'], 1);
    $pdf->Cell(25, 8, $row['assessed_value'], 1);
    $pdf->Cell(20, 8, $row['basic_tax'], 1);
    $pdf->Cell(20, 8, $row['sef_tax'], 1);
    $pdf->Cell(20, 8, $row['discount'], 1);
    $pdf->Cell(20, 8, $row['penalty'], 1);
    $pdf->Cell(25, 8, $row['total_paid'], 1);
    $pdf->Ln();
}

$pdf->Output();
