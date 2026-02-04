<?php
require_once __DIR__.'/../auth/session_check.php';
require_once __DIR__.'/../config/db.php';
require_once __DIR__.'/../vendor/autoload.php';

use TCPDF;

$id = (int)($_GET['id'] ?? 0);
$res = $mysqli->query("SELECT * FROM employees WHERE id=$id");
$emp = $res->fetch_assoc();

$pdf = new TCPDF();
$pdf->AddPage();
$pdf->SetFont('helvetica','B',16);
$pdf->Cell(0,10,'Employee Profile',0,1,'C');

$pdf->SetFont('helvetica','',12);
$pdf->Ln(5);
$pdf->Cell(50,6,'Name:',0,0);
$pdf->Cell(0,6,$emp['name'],0,1);
$pdf->Cell(50,6,'Employee ID:',0,0);
$pdf->Cell(0,6,$emp['emp_idno'],0,1);
$pdf->Cell(50,6,'Department:',0,0);
$pdf->Cell(0,6,get_department_name($emp['department_id']),0,1);
$pdf->Cell(50,6,'Position:',0,0);
$pdf->Cell(0,6,get_position_name($emp['position_id']),0,1);

// Add more fields as required

// Photo
if(!empty($emp['photo']) && file_exists('../uploads/employees/'.$emp['photo'])){
    $pdf->Image('../uploads/employees/'.$emp['photo'],150,20,40,50);
}

$pdf->Output('employee_profile_'.$emp['emp_idno'].'.pdf','I');
