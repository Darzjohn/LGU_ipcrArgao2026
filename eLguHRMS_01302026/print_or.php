<?php
require_once 'db.php';
require_once 'includes/lgu_header.php'; // must define $municipality_name, $province_name, $municipal_logo
require_once 'tcpdf/tcpdf.php';

if (empty($_GET['or_no'])) {
    die("No OR number provided.");
}

$or_no = $mysqli->real_escape_string($_GET['or_no']);
$sql = "SELECT * FROM payments_list WHERE or_no = '$or_no' LIMIT 1";
$result = $mysqli->query($sql);

if (!$result || $result->num_rows == 0) {
    die("Receipt not found for OR No. $or_no");
}

$data = $result->fetch_assoc();

// Create PDF (Landscape Short Bond = Letter)
$pdf = new TCPDF('L', 'mm', 'LETTER', true, 'UTF-8', false);
$pdf->SetCreator('RPTMS');
$pdf->SetTitle('Official Receipt - ' . $data['or_no']);
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(true, 10);
$pdf->AddPage();

// Reusable function to draw the OR layout
function drawOR($pdf, $x, $data, $copyType, $municipality_name, $province_name, $municipal_logo)
{
    $pdf->SetFont('helvetica', '', 10);
    $startY = 15;
    $width = 130; // half of page minus margins
    $pdf->SetXY($x, $startY);

    // --- Header ---
    if (file_exists($municipal_logo)) {
        $pdf->Image($municipal_logo, $x + 5, $startY - 3, 20, 20, '', '', '', false, 300);
    }
    $pdf->SetXY($x, $startY);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell($width, 6, 'Republic of the Philippines', 0, 1, 'C');
    $pdf->SetX($x);
    $pdf->Cell($width, 6, 'Municipality of ' . strtoupper($municipality_name), 0, 1, 'C');
    $pdf->SetX($x);
    $pdf->Cell($width, 6, 'Province of ' . strtoupper($province_name), 0, 1, 'C');
    $pdf->Ln(2);
    $pdf->SetX($x);
    $pdf->SetFont('helvetica', 'BU', 13);
    $pdf->Cell($width, 7, 'OFFICIAL RECEIPT', 0, 1, 'C');
    $pdf->Ln(3);

    // --- OR Info ---
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetX($x);
    $pdf->Cell($width / 2, 6, 'O.R. No.: ' . $data['or_no'], 0, 0, 'L');
    $pdf->Cell($width / 2, 6, 'Date: ' . date('F d, Y h:i A', strtotime($data['payment_date'])), 0, 1, 'L');
    $pdf->SetX($x);
    $pdf->Cell($width / 2, 6, 'Payor: ' . $data['payor_name'], 0, 0, 'L');
    $pdf->Cell($width / 2, 6, 'Owner: ' . $data['owner_name'], 0, 1, 'L');
    $pdf->SetX($x);
    $pdf->Cell($width / 2, 6, 'Barangay: ' . $data['barangay'], 0, 0, 'L');
    $pdf->Cell($width / 2, 6, 'Location: ' . $data['location'], 0, 1, 'L');
    $pdf->SetX($x);
    $pdf->Cell($width / 2, 6, 'Tax Year: ' . $data['tax_year'], 0, 0, 'L');
    $pdf->Cell($width / 2, 6, 'TD No.: ' . $data['td_no'], 0, 1, 'L');
    $pdf->SetX($x);
    $pdf->Cell($width / 2, 6, 'Classification: ' . $data['classification'], 0, 0, 'L');
    $pdf->Cell($width / 2, 6, 'Status: ' . $data['status'], 0, 1, 'L');
    $pdf->Ln(3);

    // --- Table ---
    $pdf->SetX($x);
    $html = '
    <table border="1" cellpadding="3">
        <tr style="background-color:#f0f0f0; font-weight:bold;">
            <th width="70%">Description</th>
            <th width="30%" align="right">Amount (₱)</th>
        </tr>
        <tr><td>Basic Tax</td><td align="right">'.number_format($data['basic_tax'], 2).'</td></tr>
        <tr><td>SEF Tax</td><td align="right">'.number_format($data['sef_tax'], 2).'</td></tr>
        <tr><td>Adjustments</td><td align="right">'.number_format($data['adjustments'], 2).'</td></tr>
        <tr><td>Penalty</td><td align="right">'.number_format($data['penalty'], 2).'</td></tr>
        <tr><td>Discount</td><td align="right">('.number_format($data['discount'], 2).')</td></tr>
        <tr style="font-weight:bold; background-color:#eaeaea;">
            <td>Total Amount Paid</td>
            <td align="right">'.number_format($data['total_amount_paid'], 2).'</td>
        </tr>
    </table>';
    $pdf->writeHTMLCell($width, '', $x, '', $html, 0, 1, false, true, 'L', true);
    $pdf->Ln(4);

    // --- Signatures ---
    $pdf->SetX($x);
    $pdf->Cell($width / 2, 6, '_____________________________', 0, 0, 'C');
    $pdf->Cell($width / 2, 6, '_____________________________', 0, 1, 'C');
    $pdf->SetX($x);
    $pdf->Cell($width / 2, 6, 'Payor: ' . $data['payor_name'], 0, 0, 'C');
    $pdf->Cell($width / 2, 6, 'Municipal Treasurer: ' . $data['processed_by'], 0, 1, 'C');

    // --- Copy Type Footer ---
    $pdf->Ln(4);
    $pdf->SetX($x);
    $pdf->SetFont('helvetica', 'I', 9);
    $pdf->Cell($width, 6, "This is the $copyType copy.", 0, 1, 'C');
    $pdf->SetX($x);
    $pdf->Cell($width, 5, 'This document is system-generated and does not require a signature.', 0, 1, 'C');
}

// Draw both copies
drawOR($pdf, 10, $data, 'PAYOR\'S', $municipality_name, $province_name, $municipal_logo);
drawOR($pdf, 150, $data, 'LGU ACCOUNTING', $municipality_name, $province_name, $municipal_logo);

// Output file
$pdf->Output('Official_Receipt_'.$data['or_no'].'.pdf', 'I');
exit;
?>
