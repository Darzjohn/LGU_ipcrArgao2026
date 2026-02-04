<?php 
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';

$date = $_GET['date'] ?? '';
$collector = $_GET['collector'] ?? '';

if (!$date || !$collector) {
    die("Invalid request.");
}

/* ============================================================
   GET TREASURER VERIFIER (from signatories table)
   ============================================================ */

$treasurerName = "";
$treasurerTitle = "";

$q = $mysqli->prepare("SELECT name, title FROM signatories WHERE position = 'treasurer verifier' LIMIT 1");
$q->execute();
$q->bind_result($treasurerName, $treasurerTitle);
$q->fetch();
$q->close();

/* Fallback if not found */
if (!$treasurerName) $treasurerName = "__________________________";
if (!$treasurerTitle) $treasurerTitle = "Treasurer Verifier";


// Fetch all remittances for that date and collector
$stmt = $mysqli->prepare("
    SELECT * FROM remittance
    WHERE remittance_date = ?
    AND created_by = ?
    ORDER BY form_no ASC, or_no ASC
");
$stmt->bind_param("ss", $date, $collector);
$stmt->execute();
$records = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Compute totals + OR ranges
$formSummary = [];
$totalAmount = 0;

foreach ($records as $r) {
    $form = $r['form_no'];
    $or = $r['or_no'];
    $amt = $r['total_paid'];

    if (!isset($formSummary[$form])) {
        $formSummary[$form] = [
            'from' => $or,
            'to' => $or,
            'amount' => 0
        ];
    }

    if ($or < $formSummary[$form]['from']) $formSummary[$form]['from'] = $or;
    if ($or > $formSummary[$form]['to']) $formSummary[$form]['to'] = $or;

    $formSummary[$form]['amount'] += $amt;
    $totalAmount += $amt;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>RCD Per Collector</title>

<style>
    @page {
        size: 8.5in 11in;
        margin: 0.5in;
    }
    @media print {
        body {
            margin: 0;
            padding: 0.3in;
        }
    }
    body { font-family: Arial, sans-serif; font-size: 13px; }
    table { width: 100%; border-collapse: collapse; }
    td, th { border: 1px solid black; padding: 6px; }
    .no-border td { border: 0 !important; }
    .center { text-align:center; }
    .right { text-align:right; }
    .bold { font-weight:bold; }
</style>

</head>
<body onload="window.print();">

<h2 class="center">REPORT OF COLLECTIONS AND DEPOSITS</h2>

<table class="no-border">
<tr>
    <td><strong>Collector: </strong><?= htmlspecialchars($collector) ?></td>
    <td class="right"><strong>Date: </strong><?= htmlspecialchars($date) ?></td>
</tr>
<tr>
    <td><strong>Accountable Officer: </strong><?= htmlspecialchars($collector) ?></td>
    <td></td>
</tr>
</table>

<h3>A. COLLECTIONS</h3>
<b>1. For Collectors</b>

<table>
<thead>
<tr class="center bold">
    <th>Type (Form No.)</th>
    <th colspan="2">Official Receipt / Serial Number</th>
    <th>Amount (₱)</th>
</tr>
<tr class="center bold">
    <th></th>
    <th>From</th>
    <th>To</th>
    <th></th>
</tr>
</thead>
<tbody>

<?php foreach ($formSummary as $form => $d): ?>
<tr class="center">
    <td><?= $form ?></td>
    <td><?= $d['from'] ?></td>
    <td><?= $d['to'] ?></td>
    <td class="right">₱<?= number_format($d['amount'], 2) ?></td>
</tr>
<?php endforeach; ?>

<tr class="center bold">
    <td colspan="3">TOTAL</td>
    <td class="right">₱<?= number_format($totalAmount, 2) ?></td>
</tr>

</tbody>
</table>

<br><br>

<table class="no-border" style="margin-top:20px; width:100%;">
<tr>

<td style="width:50%; vertical-align:top; padding-right:20px;">
    <strong>CERTIFICATION:</strong><br><br>
    I do hereby certify that the forgoing report of collections and deposits 
    and accountability for accountable form is true and correct.<br><br><br>

    _______________________________<br>
    <?= htmlspecialchars($collector) ?><br>
    Collecting Officer<br>
    Accountable Officer<br>
    Date: <?= htmlspecialchars($date) ?>
</td>

<td style="width:50%; vertical-align:top; padding-left:20px;">
    <strong>VERIFIED AND ACKNOWLEDGED:</strong><br><br>
    I do hereby certify that the forgoing report of collections has been verified 
    and acknowledged as correct collection of  
    <strong>₱<?= number_format($totalAmount, 2) ?></strong>.<br><br><br>

    _______________________________<br>
    <?= htmlspecialchars($treasurerName) ?><br>
    <?= htmlspecialchars($treasurerTitle) ?><br>
    Date: <?= htmlspecialchars($date) ?>
</td>

</tr>
</table>


</body>
</html>
