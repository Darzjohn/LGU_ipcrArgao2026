<?php
require_once __DIR__.'/../config/db.php';
require_once __DIR__.'/../libraries/tcpdf/tcpdf.php';

if(!isset($_GET['id'])) exit;

$emp_id = intval($_GET['id']);
$stmt = $mysqli->prepare("SELECT * FROM employees WHERE id=?");
$stmt->bind_param("i",$emp_id);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();

$pdf = new TCPDF();
$pdf->AddPage();
$pdf->SetFont('helvetica','B',16);
$pdf->Cell(0,10,'Employee Profile',0,1,'C');

$pdf->SetFont('helvetica','',12);
$pdf->Ln(5);
$pdf->Cell(50,8,'Employee ID:',0,0);
$pdf->Cell(0,8,$employee['emp_idno'],0,1);
$pdf->Cell(50,8,'Name:',0,0);
$pdf->Cell(0,8,$employee['surname'].' '.$employee['first_name'].' '.$employee['middle_name'],0,1);
$pdf->Cell(50,8,'Department:',0,0);
$pdf->Cell(0,8,$employee['department_id'],0,1);
$pdf->Cell(50,8,'Position:',0,0);
$pdf->Cell(0,8,$employee['position_id'],0,1);
$pdf->Cell(50,8,'DOB:',0,0);
$pdf->Cell(0,8,$employee['dob'],0,1);
$pdf->Cell(50,8,'Email:',0,0);
$pdf->Cell(0,8,$employee['email'],0,1);

$pdf->Output('employee_profile.pdf','I');
