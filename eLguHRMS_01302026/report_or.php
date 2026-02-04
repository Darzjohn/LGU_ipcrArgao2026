<?php
require_once 'db.php';
require_once 'vendor/autoload.php';
require_once 'includes/lgu_header.php'; // ✅ Contains $municipal_logo, $municipality_name, $province_name

use TCPDF;

if (empty($_GET['or_no'])) {
    die("No OR number provided.");
}

$or_no = $mysqli->real_escape_string($_GET['or_no']);
$sql = "SELECT * FROM payments_list WHERE or_no = '$or_no' LIMIT 1";
$result = $mysqli->query($sql);

if (!$result || $result->num_rows == 0) {
    die("Receipt not found.");
}

$data = $result->fetch_assoc();

// --- Create new PDF document ---
$pdf = new TCPDF('P', 'mm', 'A5', true, 'UTF-8', false);
$pdf->SetCreator('RPTMS');
$pdf->SetAuthor('Municipality of ' . $municipality_name);
$pdf->SetTitle('Official Receipt - ' . $data['or_no']);
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(true, 10);
$pdf->AddPage();

// --- Header Section ---
$html = '
<style>
    body { font-family: DejaVuSans, sans-serif; font-size: 10pt; }
    .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 4px; }
    .header img { height: 50px; }
    .header h3, .header h4, .header h5 { margin: 2px 0; }
    .details, .table, .signature, .footer-sig {
        width: 100%; border-collapse: collapse; margin-top: 8px;
    }
    .details td { padding: 3px; vertical-align: top; }
    .table th, .table td {
        border: 1px solid #000; padding: 4px; text-align: right;
    }
    .table th { background-color: #f2f2f2; text-align: left; }
    .sig-line { border-top: 1px solid #000; width: 140px; margin: 0 auto; }
    .signature td, .footer-sig td {
        text-align: center; vertical-align: top; padding-top: 15px;
    }
    .footer-sig { margin-top: 20px; }
</style>

<div class="header">
    <img src="'.$municipal_logo.'" alt="Logo"><br>
    <h4>Republic of the Philippines</h4>
    <h4><strong>Municipality of '.$municipality_name.'</strong></h4>
    <h5>Province of '.$province_name.'</h5>
    <h4 style="margin-top:5px; text-decoration: underline;">OFFICIAL RECEIPT</h4>
</div>

<table class="details">
<tr>
    <td><strong>O.R. No.:</strong> '.$data['or_no'].'</td>
    <td><strong>Date Paid:</strong> '.date('F d, Y h:i A', strtotime($data['payment_date'])).'</td>
</tr>
<tr>
    <td><strong>Payor:</strong> '.$data['payor_name'].'</td>
    <td><strong>Owner:</strong> '.$data['owner_name'].'</td>
</tr>
<tr>
    <td><strong>Barangay:</strong> '.$data['barangay'].'</td>
    <td><strong>Location:</strong> '.$data['location'].'</td>
</tr>
<tr>
    <td><strong>Tax Year:</strong> '.$data['tax_year'].'</td>
    <td><strong>TD No.:</strong> '.$data['td_no'].'</td>
</tr>
<tr>
    <td><strong>Classification:</strong> '.$data['classification'].'</td>
    <td><strong>Status:</strong> '.$data['status'].'</td>
</tr>
</table>

<table class="table">
<thead>
<tr>
    <th>Description</th>
    <th>Amount (₱)</th>
</tr>
</thead>
<tbody>
<tr><td>Basic Tax</td><td>'.number_format($data['basic_tax'], 2).'</td></tr>
<tr><td>SEF Tax</td><td>'.number_format($data['sef_tax'], 2).'</td></tr>
<tr><td>Adjustments</td><td>'.number_format($data['adjustments'], 2).'</td></tr>
<tr><td>Penalty</td><td>'.number_format($data['penalty'], 2).'</td></tr>
<tr><td>Discount</td><td>('.number_format($data['discount'], 2).')</td></tr>
<tr style="font-weight:bold; background:#f8f8f8;">
    <td>Total Amount Paid</td>
    <td>'.number_format($data['total_amount_paid'], 2).'</td>
</tr>
</tbody>
</table>

<table class="signature">
<tr>
    <td>
        <div class="sig-line"></div>
        <div>Payor<br>'.$data['payor_name'].'</div>
    </td>
    <td>
        <div class="sig-line"></div>
        <div>Municipal Treasurer<br>'.$data['processed_by'].'</div>
    </td>
</tr>
</table>

<table class="footer-sig">
<tr>
    <td>
        <div class="sig-line"></div>
        <div><strong>Prepared by</strong><br>'.$prepared_by.'</div>
    </td>
    <td>
        <div class="sig-line"></div>
        <div><strong>Reviewed by</strong><br>'.$reviewed_by.'</div>
    </td>
    <td>
        <div class="sig-line"></div>
        <div><strong>Approved by</strong><br>'.$approved_by.'</div>
    </td>
</tr>
</table>
';

// --- Render HTML ---
$pdf->writeHTML($html, true, false, true, false, '');

// --- Output to Browser ---
$pdf->Output('Official_Receipt_'.$data['or_no'].'.pdf', 'I');
exit;
?>
