<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../tcpdf/tcpdf.php'; // make sure tcpdf is installed

$or_no = $_GET['or_no'] ?? '';
if ($or_no === '') {
    die("Missing OR number.");
}

// Fetch OR info
$stmt = $mysqli->prepare("SELECT * FROM collections WHERE or_no = ? ORDER BY tax_year ASC");
$stmt->bind_param("s", $or_no);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("No record found for OR No. $or_no");
}

$data = [];
$total_amount = 0;
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
    $total_amount += floatval($row['total_due']);
}
$first = $data[0];
$date_paid = date('m/d/Y', strtotime($first['payment_date']));
$payor = strtoupper($first['payor_name']);
$amount_words = ucwords(convert_number_to_words($total_amount)) . " Pesos Only";

class MYPDF extends TCPDF {
    public $bg_image;
    function Header() {
        if ($this->bg_image) {
            $this->Image($this->bg_image, 0, 0, 279, 216, '', '', '', false, 300, '', false, false, 0);
        }
    }
}

$pdf = new MYPDF('L', 'mm', 'Letter', true, 'UTF-8', false);
$pdf->bg_image = __DIR__ . '/../assets/or_template.jpg'; // your blank OR form
$pdf->SetMargins(0, 0, 0);
$pdf->SetAutoPageBreak(false, 0);
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 10);

// OR number
$pdf->SetXY(245, 18);
$pdf->Cell(0, 0, $or_no, 0, 1, 'L');

// Payor name
$pdf->SetXY(35, 32);
$pdf->Cell(0, 0, $payor, 0, 1, 'L');

// Date
$pdf->SetXY(245, 30);
$pdf->Cell(0, 0, $date_paid, 0, 1, 'L');

// Amount in figures
$pdf->SetXY(215, 45);
$pdf->Cell(0, 0, number_format($total_amount, 2), 0, 1, 'L');

// Amount in words
$pdf->SetXY(65, 38);
$pdf->MultiCell(180, 5, $amount_words, 0, 'L', false, 1, '', '', true, 0, false, true, 0);

// Table rows
$y = 70;
foreach ($data as $row) {
    $pdf->SetXY(15, $y);
    $pdf->Cell(55, 0, strtoupper($row['owner_name']), 0, 0, 'L'); // Owner
    $pdf->Cell(35, 0, strtoupper($row['barangay']), 0, 0, 'L'); // Barangay
    $pdf->Cell(25, 0, $row['td_no'], 0, 0, 'L'); // TD No
    $pdf->Cell(15, 0, strtoupper($row['classification']), 0, 0, 'L'); // Class
    $pdf->Cell(22, 0, number_format($row['assessed_value'], 2), 0, 0, 'R'); // Assessed
    $pdf->Cell(22, 0, number_format($row['tax_due'], 2), 0, 0, 'R'); // Tax
    $pdf->Cell(22, 0, number_format($row['penalty'], 2), 0, 0, 'R'); // Penalty
    $pdf->Cell(22, 0, number_format($row['total_due'], 2), 0, 1, 'R'); // Total
    $y += 6;
}

// Mode of Payment
$pdf->SetXY(60, 166);
$pdf->Cell(40, 0, $first['payment_mode'], 0, 1, 'L');

// If check
if (strtolower($first['payment_mode']) === 'check') {
    $pdf->SetXY(60, 172);
    $pdf->Cell(30, 0, number_format($first['check_amount'], 2), 0, 1, 'L');
    $pdf->SetXY(110, 172);
    $pdf->Cell(40, 0, $first['bank_name'], 0, 1, 'L');
    $pdf->SetXY(160, 172);
    $pdf->Cell(40, 0, $first['check_date'], 0, 1, 'L');
}

// Total box bottom right
$pdf->SetXY(230, 158);
$pdf->Cell(30, 0, number_format($total_amount, 2), 0, 1, 'R');

// Collecting officer
$pdf->SetXY(180, 189);
$pdf->Cell(60, 0, strtoupper($first['processed_by']), 0, 1, 'C');

// Output PDF
$pdf->Output("OR_{$or_no}.pdf", 'I');

// ===== Helper function =====
function convert_number_to_words($number) {
    $hyphen = '-';
    $conjunction = ' and ';
    $separator = ', ';
    $negative = 'negative ';
    $decimal = ' point ';
    $dictionary = [
        0 => 'zero',
        1 => 'one',
        2 => 'two',
        3 => 'three',
        4 => 'four',
        5 => 'five',
        6 => 'six',
        7 => 'seven',
        8 => 'eight',
        9 => 'nine',
        10 => 'ten',
        11 => 'eleven',
        12 => 'twelve',
        13 => 'thirteen',
        14 => 'fourteen',
        15 => 'fifteen',
        16 => 'sixteen',
        17 => 'seventeen',
        18 => 'eighteen',
        19 => 'nineteen',
        20 => 'twenty',
        30 => 'thirty',
        40 => 'forty',
        50 => 'fifty',
        60 => 'sixty',
        70 => 'seventy',
        80 => 'eighty',
        90 => 'ninety',
        100 => 'hundred',
        1000 => 'thousand',
        1000000 => 'million',
        1000000000 => 'billion'
    ];

    if (!is_numeric($number)) return false;
    if (($number >= 0 && (int)$number < 0) || (int)$number < 0 - PHP_INT_MAX) return false;
    if ($number < 0) return $negative . convert_number_to_words(abs($number));
    $string = $fraction = null;
    if (strpos($number, '.') !== false) [$number, $fraction] = explode('.', $number);
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
    if (null !== $fraction && is_numeric($fraction)) {
        $string .= $decimal;
        $words = [];
        foreach (str_split((string)$fraction) as $number) $words[] = $dictionary[$number];
        $string .= implode(' ', $words);
    }
    return $string;
}
?>
