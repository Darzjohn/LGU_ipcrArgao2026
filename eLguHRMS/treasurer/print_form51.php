<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../tcpdf/tcpdf.php';

// --- Sanitize & fetch Form 51 record ---
$id = intval($_GET['id']);
$f = $mysqli->query("SELECT * FROM form51 WHERE id='$id'")->fetch_assoc();
if (!$f) die("Form 51 record not found.");

// --- Fetch itemized collections ---
$payments = $mysqli->query("SELECT fi.*, ns.nature_of_collection, ns.ngas_code 
                            FROM form51_items fi
                            LEFT JOIN ngas_settings ns ON fi.ngas_id = ns.id
                            WHERE fi.form51_id='$id'")->fetch_all(MYSQLI_ASSOC);

// --- Fetch municipality from system_settings ---
$sys = $mysqli->query("SELECT municipality FROM system_settings LIMIT 1")->fetch_assoc();
$municipality = strtoupper($sys['municipality'] ?? '');

// --- Number-to-words functions ---
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

function convert_amount_to_words($amount) {
    $amount = round($amount, 2);
    $pesos = floor($amount);
    $centavos = round(($amount - $pesos) * 100);

    $words = number_to_words($pesos) . " Peso" . ($pesos != 1 ? "s" : "");
    if ($centavos > 0) {
        $words .= " and " . number_to_words($centavos) . " Centavo" . ($centavos != 1 ? "s" : "");
    }
    return $words . " only";
}

// --- Compute totals ---
$grandTotal = $f['grand_total'] ?: 0.00;
$amountInWords = convert_amount_to_words($grandTotal);

// --- Create PDF ---
$pdf = new TCPDF('P', 'mm', [140, 267], true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Official Receipt - Form 51');
$pdf->AddPage();
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// --- Background template ---
$templatePath = __DIR__ . '/../assets/or_form51template.jpg';
if (!file_exists($templatePath)) die("❌ Background not found at: $templatePath");

// Fit background to full page
$pdf->Image($templatePath, 0, 10, 157, 267, 'JPG', '', '', false, 300, '', false, false, 0);

// --- Field placements ---

// Municipality
$pdf->SetFont('Helvetica','B',13);
$pdf->SetXY(39, 36);
$pdf->Cell(90, 5, $municipality, 0, 0, 'L');

// Date Issued in mm/dd/yyyy format
$pdf->SetFont('Helvetica','B',14);
$pdf->SetXY(28, 66);
$pdf->Cell(40, 5, date('m-d-Y', strtotime($f['date_issued'])), 0, 0, 'L');


// OR No.
$pdf->SetFont('Helvetica','B',24);
$pdf->SetXY(85.2, 60);
$pdf->Cell(35, 5, ' ' . strtoupper($f['or_no']), 0, 0, 'L');

// Payor Name with wrap
$pdf->SetFont('Helvetica','B',10);
$pdf->SetXY(18, 78);
$pdf->MultiCell(90, 5, strtoupper($f['payor_name']), 0, 'L', false, 1);

// Address with wrap
if (!empty($f['address'])) {
    $pdf->SetXY(18, 87);
    $pdf->SetFont('Helvetica','B',8.5);
    $pdf->MultiCell(100, 4, strtoupper($f['address']), 0, 'L');
}

// Collection items table
$pdf->SetFont('Helvetica','B',9);
$y = 104;
foreach ($payments as $p) {
    $pdf->SetXY(15, $y);
    $pdf->Cell(60, 4.8, strtoupper($p['nature_of_collection']), 0, 0, 'L');
    $pdf->SetXY(83, $y);
    $pdf->Cell(25, 4.8, $p['ngas_code'], 0, 0, 'L');
    $pdf->SetXY(105, $y);
    $pdf->Cell(20, 4.8, number_format($p['amount'],2), 0, 1, 'R');
    $y += 6;
}

// Grand Total
$pdf->SetFont('Helvetica','B',13);
$pdf->SetXY(105, 164);
$pdf->Cell(20, 5, number_format($grandTotal,2), 0, 1, 'R');

// Amount in Words
$pdf->SetFont('Helvetica','B',8.8);
$pdf->SetXY(16, 176);
$pdf->MultiCell(115, 5, $amountInWords, 0, 'L');

// Payment Mode
$pdf->SetFont('Helvetica','',8.8);
if ($f['payment_mode'] === 'cash') {
    $pdf->SetXY(20, 207);
    $pdf->Cell(10, 5, '✔', 0, 0, 'L');
} else {
    $pdf->SetXY(20, 213);
    $pdf->Cell(10, 5, '✔', 0, 0, 'L');
    $pdf->SetXY(45, 213);
    $pdf->Cell(35, 5, strtoupper($f['bank_name']), 0, 0, 'L');
    $pdf->SetXY(90, 213);
    $pdf->Cell(25, 5, strtoupper($f['check_number']), 0, 0, 'L');
    if (!empty($f['check_date']) && $f['check_date'] != '0000-00-00') {
        $pdf->SetXY(120, 213);
        $pdf->Cell(25, 5, date('m/d/Y', strtotime($f['check_date'])), 0, 0, 'L');
    }
}

// Treasurer signature
$pdf->SetFont('Helvetica','B',10);
$pdf->SetXY(78, 221);
$pdf->Cell(45, 5, strtoupper($f['treasurer']), 0, 0, 'C');

// --- Output PDF ---
$pdf->Output('Form51_' . $id . '.pdf', 'I');
exit;
?>
