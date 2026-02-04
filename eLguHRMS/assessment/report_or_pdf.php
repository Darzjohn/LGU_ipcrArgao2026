<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../tcpdf/tcpdf.php';

$or_no = $_GET['or_no'] ?? '';
if(!$or_no) die("OR Number required.");

$res = $mysqli->query("SELECT * FROM collections WHERE or_no='".$mysqli->real_escape_string($or_no)."'");
if(!$res || $res->num_rows==0) die("No records found for OR: ".$or_no);
$payments = [];
while($row=$res->fetch_assoc()) $payments[]=$row;

$pdf = new TCPDF();
$pdf->AddPage();
$pdf->SetFont('helvetica','B',16);
$pdf->Cell(0,10,"Official Receipt - ".$or_no,0,1,'C');

$pdf->SetFont('helvetica','',12);
$pdf->Cell(0,8,"Payor: ".$payments[0]['payor_name'],0,1);
$pdf->Cell(0,8,"Payment Date: ".$payments[0]['payment_date'],0,1);

$html = '<table border="1" cellpadding="4">
<thead>
<tr>
<th>RPTSP</th><th>TD No</th><th>Lot No</th><th>Owner</th><th>Tax Year</th><th>Total Due</th>
</tr>
</thead>
<tbody>';
foreach($payments as $p){
    $html.='<tr>
    <td>'.htmlspecialchars($p['rptsp_no']).'</td>
    <td>'.htmlspecialchars($p['td_no']).'</td>
    <td>'.htmlspecialchars($p['lot_no']).'</td>
    <td>'.htmlspecialchars($p['owner_name']).'</td>
    <td>'.htmlspecialchars($p['tax_year']).'</td>
    <td>₱'.number_format($p['total_due'],2).'</td>
    </tr>';
}
$html.='</tbody></table>';
$total = array_sum(array_column($payments,'total_due'));
$html.='<p>Total Amount Paid: ₱'.number_format($total,2).'</p>';
$html.='<p>Collected By: '.htmlspecialchars($payments[0]['processed_by']).'</p>';

$pdf->writeHTML($html,true,false,true,false,'');
$pdf->Output('OR_'.$or_no.'.pdf','I');
