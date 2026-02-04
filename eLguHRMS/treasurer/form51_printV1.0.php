<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../tcpdf/tcpdf.php';

$id = intval($_GET['id']); // always sanitize input

// Fetch Form51 record
$f = $mysqli->query("SELECT * FROM form51 WHERE id='$id'")->fetch_assoc();
if(!$f){
    die("Form 51 record not found.");
}

// Fetch Form51 items/payments
$payments = $mysqli->query("SELECT fi.*, ns.nature_of_collection, ns.ngas_code 
                            FROM form51_items fi
                            LEFT JOIN ngas_settings ns ON fi.ngas_id = ns.id
                            WHERE fi.form51_id='$id'")->fetch_all(MYSQLI_ASSOC);

// Create PDF
$pdf = new TCPDF('P','mm',array(140,266), true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Form51');
$pdf->AddPage();

// Build HTML
$html = "<h3>Form 51</h3>";
$html .= "<p>Payor Name: {$f['payor_name']}<br>Date Issued: {$f['date_issued']}</p>";
$html .= "<table border='1' cellpadding='4'>
<tr>
<th>NGAS Code</th>
<th>Nature of Collection</th>
<th>Amount</th>
</tr>";

$grandTotal = 0;
foreach($payments as $p){
    $html .= "<tr>
    <td>{$p['ngas_code']}</td>
    <td>{$p['nature_of_collection']}</td>
    <td>".number_format($p['amount'],2)."</td>
    </tr>";
    $grandTotal += $p['amount'];
}

$html .= "<tr>
<td colspan='2'><b>Grand Total</b></td>
<td><b>".number_format($grandTotal,2)."</b></td>
</tr>";
$html .= "</table>";

// Output PDF
$pdf->writeHTML($html,true,false,true,false,'');
$pdf->Output('Form51_'.$id.'.pdf','I');
