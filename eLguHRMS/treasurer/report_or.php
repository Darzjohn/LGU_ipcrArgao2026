<?php 
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../tcpdf/tcpdf.php'; // make sure tcpdf is installed

$or_no = $_GET['or_no'] ?? '';
if ($or_no === '') {
    die("Missing OR number.");
}

// ✅ Fetch Municipality
$municipality = 'UNKNOWN MUNICIPALITY';
$res = $mysqli->query("SELECT municipality FROM system_settings LIMIT 1");
if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();
    $municipality = strtoupper($row['municipality']);
}


// Fetch OR info
$stmt = $mysqli->prepare("SELECT * FROM collections WHERE or_no = ? ORDER BY tax_year DESC");
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

// ✅ NEW: Previous payment details
$previous_or_no = $first['previous_or_no'] ?? '';
$previous_date_paid = $first['previous_date_paid'] ?? '';
$previous_year = $first['previous_year'] ?? '';

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
$pdf->SetFont('helvetica', 'B', 8);

// // OR number
// $pdf->SetXY(245, 18);
// $pdf->Cell(0, 0, $or_no, 0, 1, 'L');

// Municipality Name
$pdf->SetXY(78, 53);
$html = '<span style="font-size:13pt;">' . $municipality . '</span>';
$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, 'L', true);


// Payor name
$pdf->SetXY(13, 70);
$html = '<span style="font-size:9pt;">' . $payor . '</span>';
$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, 'L', true);

// Date Paid
$pdf->SetXY(226, 50);
$html = '<span style="font-size:12pt;">' . $date_paid . '</span>';
$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, 'L', true);

// Amount in figures
$pdf->SetXY(223, 68);
$html = '<span style="font-size:14pt;">' . number_format($total_amount, 2) . '</span>';
$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, 'L', true);

// Amount in words
$pdf->SetXY(118, 70);
$pdf->MultiCell(180, 5, $amount_words, 0, 'L', false, 1, '', '', true, 0, false, true, 0);

// Table rows
$y = 95;
foreach ($data as $row) {
    $pdf->SetXY(13, $y);
    // $pdf->Cell(50, 0, strtoupper($row['owner_name']), 0, 0, 'L'); // Owner
    $html = '<span style="font-size:6pt;">' . strtoupper($row['owner_name']) . '</span>';
    $pdf->writeHTMLCell(
    50,       // width
    0,        // height (auto)
    '',       // X (already set with SetXY)
    '',       // Y (already set with SetXY)
    $html,    // HTML content
    0,        // border
    0,        // line after
    0,        // fill
    true,     // reset height
    'L',      // align
    true      // autopadding
);
    $pdf->Cell(20, 0, strtoupper($row['barangay']), 0, 0, 'L'); // Barangay
    $pdf->Cell(15, 0, $row['td_no'], 0, 0, 'L'); // TD No
    $pdf->Cell(15, 0, $row['lot_no'], 0, 0, 'L'); // LOT No
    $pdf->Cell(3, 0, strtoupper($row['classification']), 0, 0, 'L'); // Class
    $pdf->Cell(25, 0, number_format($row['assessed_value'], 2), 0, 0, 'R'); // Assessed
    $pdf->Cell(2, 0, strtoupper($row['year']), 0, 0, 'L'); // Class
    $pdf->Cell(22, 0, number_format($row['tax_due'], 2), 0, 0, 'R'); // Basic
    $pdf->Cell(18, 0, number_format($row['basic_tax'], 2), 0, 0, 'R'); // Basic
    $pdf->Cell(16, 0, number_format($row['sef_tax'], 2), 0, 0, 'R'); // Sef
    $pdf->Cell(15, 0, number_format($row['discount'], 2), 0, 0, 'R'); // Discount
    $pdf->Cell(22, 0, number_format($row['penalty'], 2), 0, 0, 'R'); // Penalty
    $pdf->Cell(22, 0, number_format($row['total_due'], 2), 0, 1, 'R'); // Total
    $y += 6;
}

// Mode of Payment
$pdf->SetXY(60, 166);
// $pdf->Cell(40, 0, $first['payment_mode'], 0, 1, 'L');

// If Cash
if (strtolower($first['payment_mode']) === 'cash') {
    $pdf->SetXY(70, 151);
    $pdf->Cell(40, 0, $first['payment_mode'], 0, 1, 'L');
    $pdf->SetXY(105, 151);
    $pdf->Cell(30, 0, number_format($first['total_cash_amount'], 2), 0, 1, 'L');

}

// If Check
if (strtolower($first['payment_mode']) === 'check') {
    $pdf->SetXY(80, 141);
    $pdf->Cell(40, 0, $first['payment_mode'], 0, 1, 'L');
    $pdf->SetXY(100, 147);
    $pdf->Cell(30, 0, number_format($first['check_amount'], 2), 0, 1, 'L');
    $pdf->SetXY(100, 138);
    $pdf->Cell(40, 0, $first['check_number'], 0, 1, 'L');
    $pdf->SetXY(100, 141);
    $pdf->Cell(40, 0, $first['bank_name'], 0, 1, 'L');
    $pdf->SetXY(100, 144);
    $pdf->Cell(40, 0, $first['check_date'], 0, 1, 'L');
}

// Total box bottom right
$pdf->SetXY(230, 136);
// $pdf->Cell(30, 0, number_format($total_amount, 2), 0, 1, 'R');
$html = '<span style="font-size:12pt;">' . number_format($total_amount, 2) . '</span>';

// Output cell with bigger font
$pdf->writeHTMLCell(
    30,     // width
    0,      // height (auto)
    '',     // X (use current position or SetXY)
    '',     // Y (use current position or SetXY)
    $html,  // content
    0,      // border
    1,      // line break after
    0,      // fill
    true,   // reset height
    'R',    // right alignment
    true    // autopadding
);



// Collecting officer
$pdf->SetXY(180, 145);
// $pdf->Cell(60, 0, strtoupper($first['processed_by']), 0, 1, 'C');
$html = '<span style="font-size:12pt;">' . strtoupper($first['processed_by']) . '</span>';

// Output cell with wrapping and bigger font
$pdf->writeHTMLCell(
    100,     // width
    0,      // height (auto)
    '',     // X (use current position or SetXY)
    '',     // Y (use current position or SetXY)
    $html,  // content
    0,      // border
    1,      // line break after
    0,      // fill
    true,   // reset height
    'C',    // center alignment
    true    // autopadding
);



// ✅ Display Previous Payment Details
$pdf->SetXY(170, 41);
$html = '<span style="font-size:12pt;">' . $previous_or_no . '</span>';
$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, 'L', true);

$pdf->SetXY(170, 50);
$html = '<span style="font-size:12pt;">' . $previous_date_paid . '</span>';
$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, 'L', true);

$pdf->SetXY(202, 50);
$html = '<span style="font-size:12pt;">' . $previous_year . '</span>';
$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, 'L', true);

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
        0 => 'zero', 1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four',
        5 => 'five', 6 => 'six', 7 => 'seven', 8 => 'eight', 9 => 'nine',
        10 => 'ten', 11 => 'eleven', 12 => 'twelve', 13 => 'thirteen',
        14 => 'fourteen', 15 => 'fifteen', 16 => 'sixteen', 17 => 'seventeen',
        18 => 'eighteen', 19 => 'nineteen', 20 => 'twenty', 30 => 'thirty',
        40 => 'forty', 50 => 'fifty', 60 => 'sixty', 70 => 'seventy',
        80 => 'eighty', 90 => 'ninety', 100 => 'hundred', 1000 => 'thousand',
        1000000 => 'million', 1000000000 => 'billion'
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
