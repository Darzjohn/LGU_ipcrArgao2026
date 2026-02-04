<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid access.");
}

$selectedBills = $_POST['selected_bills'] ?? '';
$selectedBills = array_map('intval', explode(',', $selectedBills));

$orNumbers    = $_POST['or_number'] ?? [];
$payorNames   = $_POST['payor_name'] ?? [];
$prevOR       = $_POST['prev_or'] ?? [];
$prevPaid     = $_POST['prev_paid'] ?? [];
$datePaidArr  = $_POST['date_paid'] ?? [];

$today = new DateTime();
$curYear = (int)$today->format('Y');
$curMonth = (int)$today->format('n');

foreach($selectedBills as $bill_id) {

    // Fetch bill details
    $billRes = $mysqli->query("SELECT tb.id AS bill_id, tb.tax_year,
        a.basic_tax, a.sef_tax, a.adjustments
        FROM tax_bills tb
        JOIN assessments a ON a.id = tb.assessment_id
        WHERE tb.id=$bill_id");
    $bill = $billRes->fetch_assoc();

    $basic_tax = (float)$bill['basic_tax'];
    $sef_tax   = (float)$bill['sef_tax'];
    $adjustments = (float)$bill['adjustments'];
    $tax_year = (int)$bill['tax_year'];

    // Discount
    $discount = 0;
    if($tax_year == $curYear && $curMonth <= 3) {
        $discount = 0.10 * ($basic_tax + $sef_tax);
    }

    // Penalty
    if($tax_year == $curYear) {
        $months_due = max(0, $curMonth);
    } elseif($tax_year < $curYear) {
        $months_due = ($curYear - $tax_year) * 12 + $curMonth;
    } else {
        $months_due = 0;
    }
    $penalty = min(0.02 * $months_due * ($basic_tax + $sef_tax), 0.72 * ($basic_tax + $sef_tax));

    $total_due = $basic_tax + $sef_tax + $adjustments - $discount + $penalty;

    // Insert payment record
    $stmt = $mysqli->prepare("INSERT INTO payments 
        (bill_id, or_number, payor_name, prev_or, prev_paid, date_paid, basic_tax, sef_tax, adjustments, discount, penalty, total_due)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("issssddddddd",
        $bill_id,
        $orNumbers[$bill_id],
        $payorNames[$bill_id],
        $prevOR[$bill_id],
        $prevPaid[$bill_id],
        $datePaidArr[$bill_id],
        $basic_tax,
        $sef_tax,
        $adjustments,
        $discount,
        $penalty,
        $total_due
    );
    $stmt->execute();
    $stmt->close();

    // Insert audit log
    $auditStmt = $mysqli->prepare("INSERT INTO payment_audit 
        (bill_id, action, or_number, payor_name, date) VALUES (?,?,?,?,?)");
    $action = "Payment Recorded";
    $auditStmt->bind_param("issss",
        $bill_id,
        $action,
        $orNumbers[$bill_id],
        $payorNames[$bill_id],
        $datePaidArr[$bill_id]
    );
    $auditStmt->execute();
    $auditStmt->close();
}

header("Location: confirm_payment.php?success=1");
exit;
