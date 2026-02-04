<?php
// ============================
// print_ctc_individual.php
// ============================

// Start output buffering
ob_start();

// Start session only if none exists
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Include session check, DB, TCPDF
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../tcpdf/tcpdf.php';

// Validate CTC ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Invalid request.');
}

$id = intval($_GET['id']);
$stmt = $mysqli->prepare("SELECT * FROM ctc_individual WHERE id=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$record = $result->fetch_assoc();
if (!$record) die('CTC record not found.');

$record['subtotal'] = 
    floatval($record['basic_tax']) +
    floatval($record['gr_tax_due']) +
    floatval($record['sal_tax_due']) +
    floatval($record['rpt_tax_due']);



// ============================
// Create TCPDF with custom size (8.5 x 5.5 inches)
// ============================
$pdf = new TCPDF('L', 'mm', array(216, 140), true, 'UTF-8', false);
$pdf->SetMargins(0, 0, 0);
$pdf->SetAutoPageBreak(false, 0);
$pdf->AddPage();

// ============================
// Background template (fit perfectly on 8.5x5.5in landscape)
// ============================
$template_file = __DIR__ . '/../assets/or_ctctemplate.jpg';
$pdf->Image($template_file, 0, 0, 216, 140, '', '', '', false, 300, '', false, false, 0);

// ============================
// Font and text setup
// ============================
// ============================
// CTC NO
// ============================
$pdf->SetFont('helvetica', 'B', 26);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetXY(157, 9); $pdf->Cell(40, 5, $record['ctc_no']);          // CTC No

// ============================
// Header Info
// ============================
$pdf->SetFont('helvetica', 'B', 13);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetXY(13, 21); $pdf->Cell(30, 5, $record['year']);            // Year
$pdf->SetXY(45, 21);  $pdf->Cell(80, 5, $record['place_of_issue']);  // Place of Issue
// $pdf->SetXY(100, 22); $pdf->Cell(40, 5, $record['date_issued']);     // Date Issued

// Convert and format the date
$dateIssued = date("m-d-Y", strtotime($record['date_issued']));

// Output to PDF
$pdf->SetXY(100, 22);
$pdf->Cell(40, 5, $dateIssued);   // Date Issued



$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetTextColor(0, 0, 0);







// ============================
// Personal Info
// ============================
$pdf->SetXY(12, 29);  $pdf->Cell(50, 5, strtoupper($record['surname']));
$pdf->SetXY(55, 29);  $pdf->Cell(50, 5, strtoupper($record['firstname']));
$pdf->SetXY(95, 29); $pdf->Cell(50, 5, strtoupper($record['middlename']));

$pdf->SetXY(12, 36);  $pdf->Cell(160, 5, $record['address']);
$pdf->SetXY(12, 44);  $pdf->Cell(40, 5, $record['citizenship']);
$pdf->SetXY(100, 44);  $pdf->Cell(60, 5, $record['place_of_birth']);
$pdf->SetXY(170, 44);  $pdf->Cell(60, 5, $record['height']);
$pdf->SetXY(25, 50); $pdf->Cell(25, 5, $record['civil_status']);
$pdf->SetXY(25, 58); $pdf->Cell(25, 5, $record['profession']);
$pdf->SetXY(140, 51); $pdf->Cell(25, 5, $record['date_of_birth']);
$pdf->SetXY(170, 51); $pdf->Cell(25, 5, $record['weight']);
$pdf->SetXY(145, 36); $pdf->Cell(25, 5, $record['sex']);

// ============================
// Tax Fields
// ============================
$pdf->SetXY(174, 64); $pdf->Cell(25, 5, number_format($record['basic_tax'], 2), 0, 0, 'R');
$pdf->SetXY(140, 79); $pdf->Cell(25, 5, number_format($record['gross_receipts'], 2), 0, 0, 'R');
$pdf->SetXY(140, 86); $pdf->Cell(25, 5, number_format($record['salaries'], 2), 0, 0, 'R');
$pdf->SetXY(140, 93); $pdf->Cell(25, 5, number_format($record['real_property_income'], 2), 0, 0, 'R');

$pdf->SetXY(174, 79); $pdf->Cell(25, 5, number_format($record['gr_tax_due'], 2), 0, 0, 'R');
$pdf->SetXY(174, 86); $pdf->Cell(25, 5, number_format($record['sal_tax_due'], 2), 0, 0, 'R');
$pdf->SetXY(174, 93); $pdf->Cell(25, 5, number_format($record['rpt_tax_due'], 2), 0, 0, 'R');


$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetXY(174, 102); 
$pdf->Cell(25, 5, number_format($record['subtotal'], 2), 0, 0, 'R');


$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetXY(174, 110); $pdf->Cell(25, 5, number_format($record['surcharge'], 2), 0, 0, 'R');
$pdf->SetXY(174, 118); $pdf->Cell(25, 5, number_format($record['total_due'], 2), 0, 0, 'R');

// ============================
// Treasurer / Created by
// ============================
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetXY(68, 124); $pdf->Cell(60, 5, strtoupper($record['treasurer']), 0, 0, 'L');
// $pdf->SetXY(35, 182); $pdf->Cell(70, 5, strtoupper($record['treasurer']));
$pdf->SetXY(110, 182); $pdf->Cell(70, 5, strtoupper($record['created_by']));




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
$pdf->Output('CTC_' . $record['ctc_no'] . '.pdf', 'I');
exit;
?>
