<?php
require 'db.php';
include 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $billIds    = $_POST['bill_ids']    ?? [];
    $discounts  = $_POST['discounts']   ?? [];
    $penalties  = $_POST['penalties']   ?? [];
    $totals     = $_POST['totals']      ?? [];
    $payorName  = trim($_POST['payor_name'] ?? '');
    $datePaid   = $_POST['date_paid']   ?? date('Y-m-d');

    if (empty($billIds) || !$payorName) {
        die("❌ Missing required data.");
    }

    $mysqli->begin_transaction();
    try {
        $stmt = $mysqli->prepare("
            INSERT INTO payments 
            (or_no, payor_name, date_paid, tax_bill_id, tax_year, basic_tax, sef_tax, discount, penalty, total_paid) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($billIds as $i => $billId) {
            // Generate OR Number: e.g., OR-202509-0001
            $orNo = 'OR-' . date('Ym') . '-' . str_pad(mt_rand(1,9999),4,'0',STR_PAD_LEFT);

            // Fetch tax year, basic, sef from tax_bills + assessments
            $billRes = $mysqli->prepare("
                SELECT tb.tax_year, a.basic_tax, a.sef_tax
                FROM tax_bills tb
                JOIN assessments a ON a.id = tb.assessment_id
                WHERE tb.id=?
            ");
            $billRes->bind_param('i', $billId);
            $billRes->execute();
            $billData = $billRes->get_result()->fetch_assoc();
            $billRes->close();

            $taxYear = $billData['tax_year'];
            $basic   = $billData['basic_tax'];
            $sef     = $billData['sef_tax'];
            $discount = $discounts[$i] ?? 0;
            $penalty  = $penalties[$i] ?? 0;
            $total    = $totals[$i] ?? ($basic + $sef - $discount + $penalty);

            $stmt->bind_param(
                'sssidddddd', 
                $orNo, $payorName, $datePaid, $billId, $taxYear, $basic, $sef, $discount, $penalty, $total
            );
            $stmt->execute();
        }

        $mysqli->commit();
        echo "<div class='alert alert-success'>✅ Payment recorded successfully.</div>";
        echo "<a href='payments.php' class='btn btn-primary'>Back to Payments</a>";

    } catch (Exception $e) {
        $mysqli->rollback();
        die("❌ Payment failed: " . $e->getMessage());
    }
} else {
    die("❌ Invalid access method.");
}
