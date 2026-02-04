<?php
require_once __DIR__.'/../auth/session_check.php';
require_once __DIR__.'/../config/db.php';
require_once __DIR__.'/../vendor/autoload.php'; // TCPDF

use TCPDF;

$id = (int)($_GET['id'] ?? 0);
$res = $mysqli->query("SELECT * FROM employees WHERE id=$id");
$emp = $res->fetch_assoc();

$pdf = new TCPDF('P','mm',[85.6,54]); // Standard ID size
$pdf->SetMargins(2,2,2);
$pdf->AddPage();

// Background
$pdf->SetFillColor(255,255,255);
$pdf->Rect(0,0,85.6,54,'F');

// Add Photo
if(!empty($emp['photo']) && file_exists('../uploads/employees/'.$emp['photo'])){
    $pdf->Image('../uploads/employees/'.$emp['photo'],5,5,24,30);
}

// Add Name & Info
$pdf->SetXY(30,5);
$pdf->SetFont('helvetica','B',10);
$pdf->Cell(0,5,$emp['name'],0,1);
$pdf->SetFont('helvetica','',8);
$pdf->Cell(0,5,"ID: ".$emp['emp_idno'],0,1);
$pdf->Cell(0,5,"Department: ".get_department_name($emp['department_id']),0,1);
$pdf->Cell(0,5,"Position: ".get_position_name($emp['position_id']),0,1);

// QR Code
$style = ['border'=>0,'vpadding'=>0,'hpadding'=>0,'fgcolor'=>[0,0,0],'bgcolor'=>false];
$pdf->write2DBarcode($emp['emp_idno'],'QRCODE,H',60,5,20,20,$style,'N');

$pdf->Output('employee_id_'.$emp['emp_idno'].'.pdf','I');
