<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../tcpdf/tcpdf.php';

// === Handle OR Retrieval ===
$or_no = '';
$selected_ids = [];

if (!empty($_POST['selected']) && is_array($_POST['selected'])) {
    $selected_ids = array_map('intval', $_POST['selected']);
} elseif (isset($_GET['or_no']) && $_GET['or_no'] !== '') {
    $or_no = $_GET['or_no'];
}

// === Fetch Municipality ===
$municipality = 'UNKNOWN MUNICIPALITY';
$res = $mysqli->query("SELECT municipality FROM system_settings LIMIT 1");
if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();
    $municipality = strtoupper($row['municipality']);
}

// === Fetch OR Information ===
$data = [];
$total_amount = 0;

if (!empty($selected_ids)) {
    $ids = implode(',', $selected_ids);
    $stmt = $mysqli->prepare("SELECT * FROM collections WHERE id IN ($ids) ORDER BY tax_year DESC");
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $stmt = $mysqli->prepare("SELECT * FROM collections WHERE or_no = ? ORDER BY tax_year DESC");
    $stmt->bind_param("s", $or_no);
    $stmt->execute();
    $result = $stmt->get_result();
}

if (!$result || $result->num_rows === 0) {
    die("No record found.");
}

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
    $total_amount += floatval($row['total_due']);
}

// === Payment Details ===
$first = $data[0];
$date_paid = date('m/d/Y', strtotime($first['payment_date']));
$payor = strtoupper($first['payor_name']);
$amount_words = ucwords(convert_number_to_words($total_amount)) . " Pesos Only";

// === Determine Previous Payment Details (fixed logic) ===
$previous_or_no = '';
$previous_date_paid = '';
$previous_year = '';

$earliest_year = min(array_column($data, 'tax_year'));
$prev_year = $earliest_year - 1;

// ✅ FIXED: safer condition to handle Check All / empty selections
$id_exclusion = !empty($selected_ids) ? implode(',', $selected_ids) : '0';

$stmt_prev = $mysqli->prepare("
    SELECT or_no, payment_date, tax_year
    FROM collections
    WHERE payor_name = ?
      AND tax_year <= ?
      AND id NOT IN ($id_exclusion)
      AND or_no IS NOT NULL
    ORDER BY tax_year DESC, payment_date DESC
    LIMIT 1
");
$stmt_prev->bind_param("si", $first['payor_name'], $prev_year);
$stmt_prev->execute();
$res_prev = $stmt_prev->get_result();

if ($res_prev && $res_prev->num_rows > 0) {
    $prev = $res_prev->fetch_assoc();
    $previous_or_no = $prev['or_no'];
    $previous_date_paid = date('m/d/Y', strtotime($prev['payment_date']));
    $previous_year = $prev['tax_year'];
}

// === Custom TCPDF with background ===
class MYPDF extends TCPDF {
    public $bg_image;
    function Header() {
        if ($this->bg_image && file_exists($this->bg_image)) {
            $this->Image($this->bg_image, 0, 0, 279, 216, '', '', '', false, 300, '', false, false, 0);
        }
    }
}

$pdf = new MYPDF('L', 'mm', 'Letter', true, 'UTF-8', false);
$pdf->bg_image = __DIR__ . '/../assets/or_template.jpg';
$pdf->SetMargins(0, 0, 0);
$pdf->SetAutoPageBreak(false, 0);
$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 8);

// === Municipality ===
$pdf->SetXY(78, 53);
$pdf->writeHTMLCell(0, 0, '', '', '<span style="font-size:13pt;">'.$municipality.'</span>', 0, 1, 0, true, 'L', true);

// === Payor ===
$pdf->SetXY(13, 70);
$pdf->writeHTMLCell(0, 0, '', '', '<span style="font-size:9pt;">'.$payor.'</span>', 0, 1, 0, true, 'L', true);

// === Date Paid ===
$pdf->SetXY(226, 50);
$pdf->writeHTMLCell(0, 0, '', '', '<span style="font-size:12pt;">'.$date_paid.'</span>', 0, 1, 0, true, 'L', true);

// === Amount (Figures) ===
$pdf->SetXY(223, 68);
$pdf->writeHTMLCell(0, 0, '', '', '<span style="font-size:14pt;">'.number_format($total_amount, 2).'</span>', 0, 1, 0, true, 'L', true);

// === Amount (Words) ===
$pdf->SetXY(118, 70);
$pdf->MultiCell(180, 5, $amount_words, 0, 'L', false, 1, '', '', true, 0, false, true, 0);

// === Table Rows ===
$y = 95;
foreach ($data as $row) {
    $pdf->SetXY(13, $y);
    $pdf->writeHTMLCell(50, 0, '', '', '<span style="font-size:6pt;">'.strtoupper($row['owner_name']).'</span>', 0, 0, 0, true, 'L', true);
    $pdf->Cell(20, 0, strtoupper($row['barangay']), 0, 0, 'L');
    $pdf->Cell(15, 0, $row['td_no'], 0, 0, 'L');
    $pdf->Cell(15, 0, $row['lot_no'], 0, 0, 'L');
    $pdf->Cell(3, 0, strtoupper($row['classification']), 0, 0, 'L');
    $pdf->Cell(25, 0, number_format($row['assessed_value'], 2), 0, 0, 'R');
    $pdf->Cell(2, 0, strtoupper($row['tax_year']), 0, 0, 'L');
    $pdf->Cell(22, 0, number_format($row['tax_due'], 2), 0, 0, 'R');
    $pdf->Cell(18, 0, number_format($row['basic_tax'], 2), 0, 0, 'R');
    $pdf->Cell(16, 0, number_format($row['sef_tax'], 2), 0, 0, 'R');
    $pdf->Cell(15, 0, number_format($row['discount'], 2), 0, 0, 'R');
    $pdf->Cell(22, 0, number_format($row['penalty'], 2), 0, 0, 'R');
    $pdf->Cell(22, 0, number_format($row['total_due'], 2), 0, 1, 'R');
    $y += 6;
}

// === Total ===
$pdf->SetXY(230, 136);
$pdf->writeHTMLCell(30, 0, '', '', '<span style="font-size:12pt;">'.number_format($total_amount, 2).'</span>', 0, 1, 0, true, 'R', true);

// // === Collecting Officer ===
// $pdf->SetXY(200, 145);
// $pdf->writeHTMLCell(60, 0, '', '', '<span style="font-size:12pt;">'.strtoupper($first['processed_by']).'</span>', 0, 1, 0, true, 'C', true);


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

// === Always show Previous Payment if found ===
if (!empty($previous_or_no)) {
    $pdf->SetXY(170, 41);
    $pdf->writeHTMLCell(0, 0, '', '', '<span style="font-size:12pt;">'.$previous_or_no.'</span>', 0, 1, 0, true, 'L', true);

    $pdf->SetXY(170, 50);
    $pdf->writeHTMLCell(0, 0, '', '', '<span style="font-size:12pt;">'.$previous_date_paid.'</span>', 0, 1, 0, true, 'L', true);

    $pdf->SetXY(202, 50);
    $pdf->writeHTMLCell(0, 0, '', '', '<span style="font-size:12pt;">'.$previous_year.'</span>', 0, 1, 0, true, 'L', true);
}

// If Cash
if (strtolower($first['payment_mode']) === 'cash') {
    $pdf->SetXY(70, 151);
    $pdf->Cell(40, 0, $first['payment_mode'], 0, 1, 'L');
    $pdf->SetXY(105 , 151);
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

$pdf->Output("Official_Receipt.pdf", 'I');

// === Helper Function ===
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
    if ($fraction && is_numeric($fraction)) {
        $string .= $decimal;
        foreach (str_split((string)$fraction) as $number) $words[] = $dictionary[$number];
        $string .= implode(' ', $words);
    }
    return $string;
}
?>
