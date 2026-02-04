<?php
ob_start();

require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../tcpdf/tcpdf.php';

// --- Validate and fetch record ---
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) die("Invalid request.");

$f = $mysqli->query("SELECT * FROM form51 WHERE id='$id'")->fetch_assoc();
if (!$f) die("Form 51 record not found.");

// --- Fetch itemized collections ---
$payments = $mysqli->query("
    SELECT fi.*, ns.nature_of_collection, ns.ngas_code 
    FROM form51_items fi
    LEFT JOIN ngas_settings ns ON fi.ngas_id = ns.id
    WHERE fi.form51_id='$id'
")->fetch_all(MYSQLI_ASSOC);

// --- Number to words ---
function convert_number_to_words($number) {
    $hyphen = '-'; $conjunction = ' and '; $separator = ', ';
    $dictionary = [
        0=>'zero',1=>'one',2=>'two',3=>'three',4=>'four',5=>'five',6=>'six',7=>'seven',8=>'eight',9=>'nine',
        10=>'ten',11=>'eleven',12=>'twelve',13=>'thirteen',14=>'fourteen',15=>'fifteen',16=>'sixteen',
        17=>'seventeen',18=>'eighteen',19=>'nineteen',20=>'twenty',30=>'thirty',40=>'forty',50=>'fifty',
        60=>'sixty',70=>'seventy',80=>'eighty',90=>'ninety',100=>'hundred',1000=>'thousand',
        1000000=>'million',1000000000=>'billion'
    ];
    if (!is_numeric($number)) return false;
    if ($number < 0) return 'negative ' . convert_number_to_words(abs($number));

    $string = $fraction = null;
    if (strpos((string)$number, '.') !== false)
        list($number, $fraction) = explode('.', (string)$number);

    switch (true) {
        case $number < 21:
            $string = $dictionary[$number];
            break;
        case $number < 100:
            $tens = ((int)($number / 10)) * 10;
            $units = $number % 10;
            $string = $dictionary[$tens];
            if ($units) $string .= $hyphen . $dictionary[$units];
            break;
        case $number < 1000:
            $hundreds = (int)($number / 100);
            $remainder = $number % 100;
            $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
            if ($remainder) $string .= $conjunction . convert_number_to_words($remainder);
            break;
        default:
            $baseUnit = pow(1000, floor(log($number, 1000)));
            $numBaseUnits = (int)($number / $baseUnit);
            $remainder = $number % $baseUnit;
            $string = convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
            if ($remainder) $string .= $remainder < 100 ? $conjunction : $separator;
            $string .= convert_number_to_words($remainder);
            break;
    }
    return strtoupper($string . ' PESOS ONLY');
}

// --- Totals ---
$grandTotal = floatval($f['grand_total'] ?? 0);
$amountInWords = convert_number_to_words($grandTotal);

// --- Initialize PDF ---
$pdf = new TCPDF('P', 'mm', [140, 267], true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Official Receipt - Form 51');
$pdf->SetMargins(0, 0, 0);
$pdf->SetAutoPageBreak(false, 0);
$pdf->AddPage();
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// --- Background (auto-detect JPG or PNG) ---
$bgJpg = __DIR__ . '/../assets/or_form51template.jpg';
$bgPng = __DIR__ . '/../assets/or_form51template.png';

if (file_exists($bgJpg)) {
    $templatePath = realpath($bgJpg);
    $imgType = 'JPG';
} elseif (file_exists($bgPng)) {
    $templatePath = realpath($bgPng);
    $imgType = 'PNG';
} else {
    die("❌ Background not found (or_form51template.jpg/png)");
}

// --- Auto-resize to fit page while keeping aspect ratio ---
list($imgWidthPx, $imgHeightPx) = getimagesize($templatePath);
$pageWidth = $pdf->getPageWidth();
$pageHeight = $pdf->getPageHeight();

$imgRatio = $imgWidthPx / $imgHeightPx;
$pageRatio = $pageWidth / $pageHeight;

if ($imgRatio > $pageRatio) {
    $newWidth = $pageWidth;
    $newHeight = $pageWidth / $imgRatio;
} else {
    $newHeight = $pageHeight;
    $newWidth = $pageHeight * $imgRatio;
}

// Center the image
$x = ($pageWidth - $newWidth) / 2;
$y = ($pageHeight - $newHeight) / 2;

// Draw background (no alpha for PNG)
$pdf->Image($templatePath, $x, $y, $newWidth, $newHeight, $imgType, '', '', false, 300, '', false, false, 0);

// --- Fields ---
$pdf->SetFont('Helvetica','',9);
$pdf->SetXY(26, 34);
$pdf->Cell(40, 5, date('F d, Y', strtotime($f['date_issued'])), 0, 0, 'L');

// OR No.
$pdf->SetFont('Helvetica','B',10.5);
$pdf->SetXY(95, 34);
$pdf->Cell(35, 5, ' ' . strtoupper($f['or_no']), 0, 0, 'L');

// Payor Name
$pdf->SetFont('Helvetica','',9.5);
$pdf->SetXY(26, 42);
$pdf->Cell(90, 5, strtoupper($f['payor_name']), 0, 0, 'L');

// Address
if (!empty($f['address'])) {
    $pdf->SetXY(26, 47);
    $pdf->SetFont('Helvetica','',8.5);
    $pdf->MultiCell(100, 4, strtoupper($f['address']), 0, 'L');
}

// Collection items
$pdf->SetFont('Helvetica','',8.8);
$y = 82;
foreach ($payments as $p) {
    $pdf->SetXY(20, $y);
    $pdf->Cell(60, 4.8, strtoupper($p['nature_of_collection']), 0, 0, 'L');
    $pdf->SetXY(88, $y);
    $pdf->Cell(25, 4.8, $p['ngas_code'], 0, 0, 'L');
    $pdf->SetXY(118, $y);
    $pdf->Cell(20, 4.8, number_format($p['amount'],2), 0, 1, 'R');
    $y += 6;
}

// Grand Total
$pdf->SetFont('Helvetica','B',10);
$pdf->SetXY(118, 153);
$pdf->Cell(20, 5, number_format($grandTotal,2), 0, 1, 'R');

// Amount in Words
$pdf->SetFont('Helvetica','',8.8);
$pdf->SetXY(20, 168);
$pdf->MultiCell(115, 5, $amountInWords, 0, 'L');

// Payment Mode
// $pdf->SetFont('Helvetica','',8.8);
// if ($f['payment_mode'] === 'cash') {
//     $pdf->SetXY(20, 207); // Cash checkbox
//     $pdf->Cell(10, 5, '✔', 0, 0, 'L');
// } else {
//     $pdf->SetXY(20, 213); // Check checkbox
//     $pdf->Cell(10, 5, '✔', 0, 0, 'L');
//     $pdf->SetXY(45, 213);
//     $pdf->Cell(35, 5, strtoupper($f['bank_name']), 0, 0, 'L');
//     $pdf->SetXY(90, 213);
//     $pdf->Cell(25, 5, strtoupper($f['check_number']), 0, 0, 'L');
//     if (!empty($f['check_date']) && $f['check_date'] != '0000-00-00') {
//         $pdf->SetXY(120, 213);
//         $pdf->Cell(25, 5, date('m/d/Y', strtotime($f['check_date'])), 0, 0, 'L');
//     }
// }

// Treasurer signature
$pdf->SetFont('Helvetica','',9);
$pdf->SetXY(85, 240);
$pdf->Cell(45, 5, strtoupper($f['treasurer']), 0, 0, 'C');

// --- Output ---
ob_end_clean();
$pdf->Output('Form51_' . $id . '.pdf', 'I');
exit;
?>
