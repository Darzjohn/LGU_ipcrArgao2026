<?php
require 'db.php';

// --- Validate payment_id ---
$payment_id = intval($_GET['payment_id'] ?? 0);
if ($payment_id <= 0) {
    die("Invalid Payment ID.");
}

// --- Fetch payment details ---
$sql = "
SELECT 
    pmt.id AS payment_id, 
    pmt.or_no, 
    pmt.payor_name, 
    pmt.payment_date, 
    pmt.amount_paid, 
    tb.id AS taxbill_id,
    pr.id AS property_id, pr.td_no, pr.lot_no, pr.location, pr.barangay, pr.classification,
    o.name AS owner_name
FROM payments pmt
JOIN tax_bills tb ON tb.id = pmt.taxbill_id
JOIN assessments a ON a.id = tb.assessment_id
JOIN properties pr ON pr.id = a.property_id
LEFT JOIN owners o ON o.id = pr.owner_id
WHERE pmt.id = $payment_id
";
$res = $mysqli->query($sql);
if (!$res || $res->num_rows === 0) {
    die("Payment not found.");
}
$data = $res->fetch_assoc();

// --- Previous payment details (if any) ---
$prevRes = $mysqli->query("
    SELECT or_no, payment_date 
    FROM payments 
    WHERE taxbill_id = {$data['taxbill_id']} AND id < $payment_id 
    ORDER BY id DESC LIMIT 1
");
$prev = $prevRes && $prevRes->num_rows > 0 ? $prevRes->fetch_assoc() : null;

// --- Helpers ---
function numberToWords($num) {
    $fmt = new NumberFormatter("en", NumberFormatter::SPELLOUT);
    $whole = floor($num);
    $decimal = round(($num - $whole) * 100);
    $words = strtoupper($fmt->format($whole));
    if ($decimal > 0) {
        $words .= " AND " . $decimal . "/100";
    }
    return $words . " ONLY";
}

$grandTotal = (float) $data['amount_paid'];
$amountWords = numberToWords($grandTotal);

// Values
$orNo       = htmlspecialchars($data['or_no']);
$payor      = htmlspecialchars($data['payor_name'] ?: $data['owner_name']);
$prevOr     = $prev['or_no'] ?? 'N/A';
$prevDate   = $prev['payment_date'] ?? 'N/A';
$prevYear   = $prev['payment_date'] ? date("Y", strtotime($prev['payment_date'])) : 'N/A';
$currDate   = date("F d, Y", strtotime($data['payment_date']));
$ownership  = "PROP-" . str_pad($data['property_id'], 5, "0", STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Official Receipt</title>
<style>
body { font-family: Arial, sans-serif; margin: 40px; }
.receipt { border: 2px solid #000; padding: 20px; max-width: 700px; margin: auto; }
.header { text-align: center; margin-bottom: 20px; }
.header h2 { margin: 0; text-transform: uppercase; }
.table { width: 100%; border-collapse: collapse; margin-top: 10px; }
.table td { padding: 6px; vertical-align: top; }
.label { font-weight: bold; width: 220px; }
.amount { font-size: 1.2em; font-weight: bold; }
.print-btn { margin-bottom: 20px; text-align: center; }
@media print {
    .print-btn { display: none; }
    body { margin: 0; }
}
</style>
</head>
<body>

<div class="print-btn">
    <button onclick="window.print()">🖨 Print Receipt</button>
</div>

<div class="receipt">
    <div class="header">
        <h2>Official Receipt</h2>
        <p><strong>OR No:</strong> <?=$orNo?></p>
    </div>

    <table class="table">
        <tr>
            <td class="label">Received From (Payor):</td>
            <td><?=$payor?></td>
        </tr>
        <tr>
            <td class="label">Ownership No. (Property ID):</td>
            <td><?=$ownership?></td>
        </tr>
        <tr>
            <td class="label">Previous Tax Receipt No.:</td>
            <td><?=$prevOr?></td>
        </tr>
        <tr>
            <td class="label">Last Payment Date:</td>
            <td><?=$prevDate?></td>
        </tr>
        <tr>
            <td class="label">For the Year:</td>
            <td><?=$prevYear?></td>
        </tr>
        <tr>
            <td class="label">Current Payment Date:</td>
            <td><?=$currDate?></td>
        </tr>
        <tr>
            <td class="label">Property Location:</td>
            <td><?=htmlspecialchars($data['location'])?>, Brgy. <?=htmlspecialchars($data['barangay'])?></td>
        </tr>
        <tr>
            <td class="label">Classification:</td>
            <td><?=htmlspecialchars($data['classification'])?></td>
        </tr>
        <tr>
            <td class="label">Amount in Figures & Words:</td>
            <td class="amount">₱<?=number_format($grandTotal, 2)?> (<?=$amountWords?>)</td>
        </tr>
    </table>
</div>

</body>
</html>
