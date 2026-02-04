<?php
require 'db.php';
session_start(); // ✅ Ensure we can capture logged-in user info

if (!isset($_GET['assessment_id'])) {
    die('Assessment ID is required.');
}

$assessment_id = (int)$_GET['assessment_id'];
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null; // ✅ Logged in user

// Step 1: Verify the assessment exists and is draft
$stmt = $mysqli->prepare("SELECT id, property_id, tax_year, assessed_value, basic_tax_rate, basic_tax, sef_tax, adjustments, status 
                          FROM assessments WHERE id=?");
$stmt->bind_param("i", $assessment_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    die('Assessment not found!');
}

$stmt->bind_result($id, $property_id, $tax_year, $assessed_value, $basic_tax_rate, $basic_tax, $sef_tax, $adjustments, $status);
$stmt->fetch();
$stmt->close();

if ($status !== 'draft') {
    die('Only draft assessments can be finalized.');
}

// Step 2: Insert into tax_bills safely (without rptsp_no first)
$stmt = $mysqli->prepare("
    INSERT INTO tax_bills (
        assessment_id, property_id, tax_year, assessed_value, basic_tax_rate, basic_tax, sef_tax, adjustments
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");
$stmt->bind_param(
    "iidddddd",
    $assessment_id,
    $property_id,
    $tax_year,
    $assessed_value,
    $basic_tax_rate,
    $basic_tax,
    $sef_tax,
    $adjustments
);

if (!$stmt->execute()) {
    die("❌ Error inserting into tax_bills: " . $stmt->error);
}
$tax_bill_id = $stmt->insert_id;
$stmt->close();

if (!$tax_bill_id) {
    die("❌ Failed to generate Tax Bill ID. RPTSP number cannot be created.");
}

// Step 3: Generate RPTSP number
$rptsp_no = "RPTSP-" . $property_id . "-" . $tax_bill_id;

// Step 4: Update tax_bills with rptsp_no
$stmt = $mysqli->prepare("UPDATE tax_bills SET rptsp_no=? WHERE id=?");
$stmt->bind_param("si", $rptsp_no, $tax_bill_id);
if (!$stmt->execute()) {
    die("❌ Failed to update RPTSP No: " . $stmt->error);
}
$stmt->close();

// Step 5: Update assessment status
$stmt = $mysqli->prepare("UPDATE assessments SET status='finalized' WHERE id=?");
$stmt->bind_param("i", $assessment_id);
$stmt->execute();
$stmt->close();

// Step 6: Insert log entry
$action = "Finalized assessment & generated RPTSP";
$stmt = $mysqli->prepare("INSERT INTO logs (user_id, assessment_id, tax_bill_id, rptsp_no, action) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("iiiss", $user_id, $assessment_id, $tax_bill_id, $rptsp_no, $action);
$stmt->execute();
$stmt->close();

// Step 7: Redirect
header("Location: assessments.php?msg=Assessment+finalized+successfully&RPTSP=" . urlencode($rptsp_no));
exit;
