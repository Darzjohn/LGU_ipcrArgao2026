<?php
// ============================
// print_ctc_corporation.php
// ============================

ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../tcpdf/tcpdf.php';


// ============================
// Validate ID
// ============================
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Invalid request.');
}

$id = intval($_GET['id']);
$stmt = $mysqli->prepare("SELECT * FROM ctc_corporation WHERE id=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$record = $stmt->get_result()->fetch_assoc();

if (!$record) die('CTC record not found.');

$record['subtotal'] = 
    floatval($record['basic_tax']) +
    floatval($record['rpt_tax_due']) +
    floatval($record['gr_tax_due']);

// ============================
// Custom Paper: 8.5" × 5.5"
// ============================
$pdf = new TCPDF('L', 'mm', [216, 140], true, 'UTF-8', false);
$pdf->SetMargins(0, 0, 0);
$pdf->SetAutoPageBreak(false, 0);
$pdf->AddPage();

// ============================
// Background template
// ============================
$template_file = __DIR__ . '/../assets/or_ctccorptemplate.jpg';
if (file_exists($template_file)) {
    $pdf->Image($template_file, 0, 0, 216, 140, '', '', '', false, 300, '', false, false, 0);
}

// ============================
// Font settings
// ============================
$pdf->SetFont('helvetica', 'B', 26);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetXY(155, 8); $pdf->Cell(40, 5, strtoupper($record['ctccorp_no']), 0, 0, 'L');   // CTC No


// ============================
// Header Section
// ============================
$pdf->SetFont('helvetica', 'B', 13);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetXY(12, 21); $pdf->Cell(20, 5, $record['year'], 0, 0, 'L');                   // Year
// $pdf->SetXY(98, 21); $pdf->Cell(40, 5, date('M d, Y', strtotime($record['date_issued'])), 0, 0, 'L'); // Date Issued

// Convert and format the date
$dateIssued = date("m-d-Y", strtotime($record['date_issued']));

// Output to PDF
$pdf->SetXY(100, 22);
$pdf->Cell(40, 5, $dateIssued);   // Date Issued


$pdf->SetXY(30, 21); $pdf->Cell(40, 5, strtoupper($record['place_of_issue']), 0, 0, 'L');              // Place of Issue
$pdf->SetXY(12, 30); $pdf->Cell(160, 5, strtoupper($record['company_fullname']), 0, 0, 'L');


$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetTextColor(0, 0, 0);
// ============================
// Company Information
// ============================
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetXY(12, 38); $pdf->Cell(160, 5, strtoupper($record['business_address']), 0, 0, 'L');
$pdf->SetXY(15, 47); $pdf->Cell(60, 5, $record['kind_of_org'], 0, 0, 'L');
$pdf->SetXY(100, 47); $pdf->Cell(80, 5, strtoupper($record['incorporation_address'] ?? ''), 0, 0, 'L');

$pdf->SetXY(175, 47); $pdf->Cell(30, 5, $record['date_reg'] ? date('m-d-Y', strtotime($record['date_reg'])) : '', 0, 0, 'L');


$pdf->SetXY(12, 57); $pdf->Cell(160, 5, strtoupper($record['nature_of_business']), 0, 0, 'L');

// ============================
// Tax and Computation Fields
// ============================
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetXY(136, 80); $pdf->Cell(30, 5, number_format($record['rpt_assessedvalue'], 2), 0, 0, 'R');
$pdf->SetXY(136, 88); $pdf->Cell(30, 5, number_format($record['gross_receipts'], 2), 0, 0, 'R');
$pdf->SetXY(171, 64); $pdf->Cell(30, 5, number_format($record['basic_tax'], 2), 0, 0, 'R');
// $pdf->SetXY(165, 88); $pdf->Cell(30, 5, number_format($record['additional_tax'], 2), 0, 0, 'R');
$pdf->SetXY(171, 80); $pdf->Cell(30, 5, number_format($record['rpt_tax_due'], 2), 0, 0, 'R');
$pdf->SetXY(171, 88); $pdf->Cell(30, 5, number_format($record['gr_tax_due'], 2), 0, 0, 'R');


$pdf->SetXY(176, 96); $pdf->Cell(25, 5, number_format($record['subtotal'], 2), 0, 0, 'R');
$pdf->SetXY(171, 104); $pdf->Cell(30, 5, number_format($record['surcharge'], 2), 0, 0, 'R');
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetXY(171, 114); $pdf->Cell(30, 5, number_format($record['total_due'], 2), 0, 0, 'R');


// // $pdf->SetFont('helvetica', 'B', 6);
// // $pdf->SetXY(135, 128);
// $pdf->SetFont('helvetica', '', 6);        // Set small font to fit
// $pdf->SetXY(135, 128);                    // Starting position
// $pdf->MultiCell(
//     70,                                  // Width of the cell (adjust to fit your layout)
//     4,                                   // Height of each line
//     ucwords($amount_words),              // Text to print
//     0,                                   // No border
//     'L',                                 // Left-aligned
//     false,                               // No fill
//     1,                                   // Move cursor to next line after
//     '',                                   // x position (empty = use current)
//     '',                                   // y position (empty = use current)
//     true,                                 // Reset height if needed
//     0,                                    // Max number of lines (0 = unlimited)
//     false,                                // Auto-fit
//     true                                   // Auto-wrap text
// );
// $pdf->MultiCell(160, 5, ucwords(convertToWords($record['total_due'])), 0, 'L', false, 1);

// ============================
// Signatories
// ============================
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetXY(48, 124); $pdf->Cell(60, 5, strtoupper($record['treasurer']), 0, 0, 'L');
// $pdf->SetXY(80, 128); $pdf->Cell(60, 5, strtoupper($record['created_by']), 0, 0, 'L');
$pdf->SetXY(25, 105); $pdf->Cell(60, 5, strtoupper($record['position_authorizedsig']), 0, 0, 'L');


// ============================
// Amount in Words
// ============================
// ============================
// Amount in Words (supports thousands/millions/billions)
// ============================
function convert_amount_to_words($amount) {
    $amount = round($amount, 2);
    $pesos = floor($amount);
    $centavos = round(($amount - $pesos) * 100);

    $words = number_to_words($pesos) . " peso" . ($pesos != 1 ? "s" : "");

    if ($centavos > 0) {
        $words .= " and " . number_to_words($centavos) . " centavo" . ($centavos != 1 ? "s" : "");
    }

    return $words . " only";
}

// Helper function: converts integers to words
function number_to_words($num) {
    $units = ["","One","Two","Three","Four","Five","Six","Seven","Eight","Nine"];
    $teens = ["Ten","Eleven","Twelve","Thirteen","Fourteen","Fifteen","Sixteen","Seventeen","Eighteen","Nineteen"];
    $tens = ["","Ten","Twenty","Thirty","Forty","Fifty","Sixty","Seventy","Eighty","Ninety"];
    $thousands = ["","Thousand","Million","Billion"];

    if ($num == 0) return "Zero";

    $words = "";
    $i = 0;

    while ($num > 0) {
        $chunk = $num % 1000;

        if ($chunk) {
            $chunk_words = "";

            $hundreds = floor($chunk / 100);
            $rem = $chunk % 100;

            if ($hundreds) $chunk_words .= $units[$hundreds] . " Hundred ";

            if ($rem > 0) {
                if ($rem < 10) $chunk_words .= $units[$rem];
                elseif ($rem < 20) $chunk_words .= $teens[$rem-10];
                else {
                    $chunk_words .= $tens[floor($rem/10)];
                    if ($rem % 10) $chunk_words .= "-" . $units[$rem % 10];
                }
            }

            $words = trim($chunk_words) . " " . $thousands[$i] . " " . $words;
        }

        $num = floor($num / 1000);
        $i++;
    }

    return trim($words);
}


$amount_words = convert_amount_to_words($record['total_due']);
// $pdf->SetFont('helvetica', '', 6);
// $pdf->SetXY(135, 128);
// $pdf->MultiCell(150, 5, ucwords($amount_words), 0, 'L', false, 1);

$pdf->SetFont('helvetica', '', 6);        // Set small font to fit
$pdf->SetXY(135, 127);                    // Starting position
$pdf->MultiCell(
    70,                                  // Width of the cell (adjust to fit your layout)
    4,                                   // Height of each line
    ucwords($amount_words),              // Text to print
    0,                                   // No border
    'L',                                 // Left-aligned
    false,                               // No fill
    1,                                   // Move cursor to next line after
    '',                                   // x position (empty = use current)
    '',                                   // y position (empty = use current)
    true,                                 // Reset height if needed
    0,                                    // Max number of lines (0 = unlimited)
    false,                                // Auto-fit
    true                                   // Auto-wrap text
);

// ============================
// Output PDF
// ============================
ob_end_clean();
$pdf->Output('CTC_' . $record['ctccorp_no'] . '.pdf', 'I');
exit;
?>
