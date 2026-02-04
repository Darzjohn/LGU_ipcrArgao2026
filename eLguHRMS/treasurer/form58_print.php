<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../tcpdf/tcpdf.php';

// --- Sanitize & fetch Form 58 record ---
$id = intval($_GET['id']);
$f = $mysqli->query("SELECT * FROM form58 WHERE id='$id'")->fetch_assoc();
if (!$f) die("Form 58 record not found.");

$treasurerQuery = $mysqli->query("SELECT name FROM signatories WHERE position = 'Treasurer' LIMIT 1");
$treasurerRow = $treasurerQuery->fetch_assoc();
$treasurerName = $treasurerRow['name'] ?? '';



// --- Number-to-words function ---
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

$amountInWords = convert_amount_to_words($f['amount_of_fee']);

// --- Create PDF ---
$pdf = new TCPDF('P', 'mm', [140, 267], true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('City Burial Permit & Fee Receipt');
$pdf->AddPage();
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// --- Background template ---
$templatePath = __DIR__ . '/../assets/or_burialtemplate.jpg';
if (!file_exists($templatePath)) die("❌ Background not found at: $templatePath");
$pdf->Image($templatePath, 0, 10, 157, 267, '', '', '', false, 300, '', false, false, 0);

// --- Fields placement (adjust coordinates to fit your template) ---
$pdf->SetFont('Helvetica','B',24);

// OR No.
$pdf->SetXY(81, 59);
$pdf->Cell(40, 5, strtoupper($f['or_no']), 0, 0, 'L');

// Date Paid
$pdf->SetFont('Helvetica','B',13);
$pdf->SetXY(32, 61);
$pdf->Cell(40, 5, date('m-d-Y', strtotime($f['date_paid'])), 0, 0, 'L');

// Payor Name
$pdf->SetXY(24, 87);
$pdf->MultiCell(90, 5, strtoupper($f['payor_name']), 0, 'L', false, 1);


$pdf->SetFont('Helvetica','B',13);


// City / Municipality
$pdf->SetXY(40, 100);
$pdf->MultiCell(80, 5, strtoupper($f['city_or_municipality']), 0, 'L', false, 1);


// Province
$pdf->SetXY(40, 111);
$pdf->MultiCell(80, 5, strtoupper($f['province']), 0, 'L', false, 1);


$pdf->SetFont('Helvetica','B',10);

// Name of Deceased
$pdf->SetXY(43, 130);
$pdf->MultiCell(90, 5, strtoupper($f['name_of_deceased']), 0, 'L', false, 1);

// Nationality
$pdf->SetXY(55, 135);
$pdf->Cell(50, 5, strtoupper($f['nationality']), 0, 0, 'L');

// Age
$pdf->SetXY(40, 140);
$pdf->Cell(15, 5, $f['age'], 0, 0, 'L');

// Sex
$pdf->SetXY(80, 140);
$pdf->Cell(20, 5, strtoupper($f['sex']), 0, 0, 'L');

// Date of Death
$pdf->SetXY(60, 146);
$pdf->Cell(50, 5, !empty($f['date_of_death']) ? date('m/d/Y', strtotime($f['date_of_death'])) : '', 0, 0, 'L');

// Cause of Death
$pdf->SetXY(60, 151);
$pdf->MultiCell(90, 5, strtoupper($f['case_of_death']), 0, 'L', false, 1);

// Name of Cemetery
$pdf->SetXY(65, 157);
$pdf->MultiCell(90, 5, strtoupper($f['name_of_cemetery']), 0, 'L', false, 1);

// Infectious or Non-infectious
$pdf->SetXY(82, 167);
$pdf->Cell(50, 5, strtoupper($f['infectious_or_noninfectious']), 0, 0, 'L');

// Embalmed or Not Embalmed
$pdf->SetXY(92, 172);
$pdf->Cell(50, 5, strtoupper($f['embalmed_or_notembalmed']), 0, 0, 'L');

// Disposition of Remains
$pdf->SetXY(74, 177);
$pdf->MultiCell(90, 5, strtoupper($f['disposition_of_remains']), 0, 'L', false, 1);



// Amount of Fee
$pdf->SetXY(100, 182);
$pdf->Cell(30, 5, number_format($f['amount_of_fee'], 2), 0, 0, 'L');






// Amount of Fee
$pdf->SetXY(25, 195);
$pdf->Cell(30, 5, number_format($f['amount_of_fee'], 2), 0, 0, 'L');

// Payment Date
$pdf->SetFont('Helvetica','B',13);
$pdf->SetXY(71, 209);
$pdf->Cell(40, 5, date('F j, Y', strtotime($f['payment_date'])), 0, 0, 'L');


// Amount Received
$pdf->SetXY(60, 225);
$pdf->Cell(30, 5, number_format($f['amount_received'], 2), 0, 0, 'L');



$pdf->SetFont('Helvetica','B',10);
// Amount in Words
$pdf->SetXY(15, 232);
$pdf->MultiCell(90, 5, strtoupper($amountInWords), 0, 'L', false, 1);



// Treasurer
$pdf->SetXY(74, 237);
$pdf->MultiCell(90, 5, strtoupper($treasurerName), 0, 'L', false, 1);


// // Treasurer
// $pdf->SetXY(74, 237);
// $pdf->MultiCell(90, 5, strtoupper($f['treasurer']), 0, 'L', false, 1);

// --- Output PDF ---
$pdf->Output('Form58_' . $id . '.pdf', 'I');
exit;
?>
