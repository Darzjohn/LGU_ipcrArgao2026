<?php
require 'db.php';

// Get payment ID
$payment_id = intval($_GET['payment_id'] ?? 0);

// Fetch payment info with related tax bill + property
$sql = "
    SELECT 
        p.id AS property_id,
        p.location,
        p.tax_dec_no,
        o.name AS owner_name,
        pay.id AS payment_id,
        pay.receipt_no,
        pay.payor_name,
        pay.amount,
        pay.payment_date,
        tb.year,
        tb.tax_due,
        tb.penalty,
        tb.id AS tax_bill_id
    FROM payments pay
    JOIN tax_bills tb ON pay.tax_bill_id = tb.id
    JOIN properties p ON tb.property_id = p.id
    JOIN owners o ON p.owner_id = o.id
    WHERE pay.id = $payment_id
    LIMIT 1
";
$res = $mysqli->query($sql);
$data = $res->fetch_assoc();

if (!$data) {
    die("Payment not found!");
}

// Fetch previous payment (latest before this one)
$prev = $mysqli->query("
    SELECT receipt_no, payment_date, YEAR(payment_date) as year_paid
    FROM payments
    WHERE tax_bill_id = {$data['tax_bill_id']} 
      AND id < $payment_id
    ORDER BY payment_date DESC
    LIMIT 1
")->fetch_assoc();

$previous_receipt = $prev['receipt_no'] ?? "N/A";
$last_payment_date = $prev['payment_date'] ?? "N/A";
$last_year_paid = $prev['year_paid'] ?? "N/A";

// Grand total
$grand_total = $data['amount'];

/** Convert numbers to words (PHP function) */
function convertNumberToWords($number) {
    $hyphen      = '-';
    $conjunction = ' and ';
    $separator   = ', ';
    $negative    = 'negative ';
    $decimal     = ' point ';
    $dictionary  = [
        0 => 'zero', 1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four',
        5 => 'five', 6 => 'six', 7 => 'seven', 8 => 'eight', 9 => 'nine',
        10 => 'ten', 11 => 'eleven', 12 => 'twelve', 13 => 'thirteen',
        14 => 'fourteen', 15 => 'fifteen', 16 => 'sixteen', 17 => 'seventeen',
        18 => 'eighteen', 19 => 'nineteen', 20 => 'twenty',
        30 => 'thirty', 40 => 'forty', 50 => 'fifty', 60 => 'sixty',
        70 => 'seventy', 80 => 'eighty', 90 => 'ninety',
        100 => 'hundred', 1000 => 'thousand', 1000000 => 'million',
        1000000000 => 'billion'
    ];

    if (!is_numeric($number)) return false;
    if ($number < 0) return $negative . convertNumberToWords(abs($number));

    $string = $fraction = null;
    if (strpos((string)$number, '.') !== false) {
        list($number, $fraction) = explode('.', (string)$number);
    }

    switch (true) {
        case $number < 21:
            $string = $dictionary[$number];
            break;
        case $number < 100:
            $tens   = ((int) ($number / 10)) * 10;
            $units  = $number % 10;
            $string = $dictionary[$tens];
            if ($units) $string .= $hyphen . $dictionary[$units];
            break;
        case $number < 1000:
            $hundreds  = (int) ($number / 100);
            $remainder = $number % 100;
            $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
            if ($remainder) $string .= $conjunction . convertNumberToWords($remainder);
            break;
        default:
            $baseUnit = pow(1000, floor(log($number, 1000)));
            $numBaseUnits = (int) ($number / $baseUnit);
            $remainder = $number % $baseUnit;
            $string = convertNumberToWords($numBaseUnits) . ' ' . $dictionary[$baseUnit];
            if ($remainder) $string .= $remainder < 100 ? $conjunction : $separator;
            $string .= convertNumberToWords($remainder);
            break;
    }

    if ($fraction !== null && is_numeric($fraction)) {
        $string .= $decimal;
        $words = [];
        foreach (str_split((string) $fraction) as $digit) {
            $words[] = $dictionary[$digit];
        }
        $string .= implode(' ', $words);
    }
    return $string;
}

$amount_in_words = strtoupper(convertNumberToWords((int)$grand_total)) . " PESOS ONLY";
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Official Receipt</title>
<style>
    @media print {
        @page { size: 8.5in 11in; margin: 0.5in; }
        body { margin: 0; font-family: Arial, sans-serif; font-size: 12px; }
    }
    body { width: 8.5in; margin: auto; padding: 20px; font-family: Arial, sans-serif; }
    .header { text-align: center; font-weight: bold; margin-bottom: 10px; }
    table { width: 100%; border-collapse: collapse; font-size: 12px; }
    .border td, .border th { border: 1px solid black; padding: 3px; text-align: center; }
    .right { text-align: right; }
    .left { text-align: left; }
    .center { text-align: center; }
</style>
</head>
<body>

<div class="header">
    Republic of the Philippines <br>
    Province of Cebu <br>
    <strong>Office of the Treasurer</strong>
</div>

<table style="margin-bottom:10px;">
    <tr>
        <td>RECEIVED FROM:</td>
        <td><strong><?= htmlspecialchars($data['payor_name']) ?></strong></td>
        <td class="right">No. <strong><?= $data['receipt_no'] ?></strong></td>
    </tr>
    <tr>
        <td>PREVIOUS TAX RECEIPT NO.:</td>
        <td><?= $previous_receipt ?></td>
        <td class="right">OWNERSHIP NO.: <?= $data['property_id'] ?></td>
    </tr>
    <tr>
        <td>DATED (Last Payment):</td>
        <td><?= $last_payment_date ?></td>
        <td class="right">FOR THE YEAR: <?= $last_year_paid ?></td>
    </tr>
    <tr>
        <td>CURRENT PAYMENT DATE:</td>
        <td><?= $data['payment_date'] ?></td>
        <td></td>
    </tr>
</table>

<table class="border">
    <tr>
        <th>NAME OF DECLARED OWNER</th>
        <th>LOCATION</th>
        <th>TAX DEC. NO.</th>
        <th>TAX DUE</th>
        <th>PENALTY</th>
        <th>TOTAL</th>
    </tr>
    <tr>
        <td><?= $data['owner_name'] ?></td>
        <td><?= $data['location'] ?></td>
        <td><?= $data['tax_dec_no'] ?></td>
        <td class="right"><?= number_format($data['tax_due'], 2) ?></td>
        <td class="right"><?= number_format($data['penalty'], 2) ?></td>
        <td class="right"><?= number_format($grand_total, 2) ?></td>
    </tr>
</table>

<br>

<table class="border">
    <tr>
        <th>AMOUNT IN FIGURES (GRAND TOTAL)</th>
    </tr>
    <tr>
        <td class="right"><strong>₱<?= number_format($grand_total, 2) ?> (<?= $amount_in_words ?>)</strong></td>
    </tr>
</table>

<br><br>
<table style="width:100%;">
    <tr>
        <td class="center">_________________________<br>Deputy/Collecting Agent</td>
        <td class="center">_________________________<br>Collecting Officer</td>
    </tr>
</table>

</body>
</html>
