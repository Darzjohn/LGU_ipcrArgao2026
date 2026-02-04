<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../tcpdf/tcpdf.php';

$id = intval($_GET['id']); // sanitize input

// Fetch Form58 record
$f = $mysqli->query("SELECT * FROM form58 WHERE id='$id'")->fetch_assoc();
if (!$f) {
    die("Form 58 record not found.");
}

// Create PDF (same size as Form51)
$pdf = new TCPDF('P','mm',array(140,266), true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Form58');
$pdf->AddPage();

// ✅ Set a Unicode font that supports ₱
$pdf->SetFont('dejavusans', '', 10);

// Format dates
$date_paid = !empty($f['date_paid']) ? date("l, F j, Y", strtotime($f['date_paid'])) : '';
$date_of_death = !empty($f['date_of_death']) ? date("l, F j, Y", strtotime($f['date_of_death'])) : '';
$payment_date = !empty($f['payment_date']) ? date("l, F j, Y", strtotime($f['payment_date'])) : '';

// Build HTML
$html = "<h3>Form 58</h3>";
$html .= "<p><b>OR No:</b> {$f['or_no']}<br>
<b>Date Paid:</b> {$date_paid}<br>
<b>Payor Name:</b> {$f['payor_name']}<br>
<b>City/Municipality:</b> {$f['city_or_municipality']}<br>
<b>Province:</b> {$f['province']}</p>";

$html .= "<p><b>Name of Deceased:</b> {$f['name_of_deceased']}<br>
<b>Nationality:</b> {$f['nationality']}<br>
<b>Age:</b> {$f['age']}<br>
<b>Sex:</b> {$f['sex']}<br>
<b>Date of Death:</b> {$date_of_death}<br>
<b>Case of Death:</b> {$f['case_of_death']}<br>
<b>Name of Cemetery:</b> {$f['name_of_cemetery']}<br>
<b>Infectious/Non-Infectious:</b> {$f['infectious_or_noninfectious']}<br>
<b>Embalmed/Not Embalmed:</b> {$f['embalmed_or_notembalmed']}<br>
<b>Disposition of Remains:</b> {$f['disposition_of_remains']}</p>";

$html .= "<table border='1' cellpadding='4'>
<tr>
<th>Amount of Fee</th>
<th>Payment Date</th>
<th>Amount Received</th>
<th>Treasurer</th>
</tr>";

$html .= "<tr>
<td>₱".number_format($f['amount_of_fee'],2)."</td>
<td>{$payment_date}</td>
<td>₱".number_format($f['amount_received'],2)."</td>
<td>{$f['treasurer']}</td>
</tr>";

$html .= "</table>";

// Output PDF
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('Form58_'.$id.'.pdf','I');
?>
