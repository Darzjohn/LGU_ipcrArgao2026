<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

// Validate POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$form_no        = trim($_POST['form_no']);
$date_issued    = trim($_POST['date_issued']);
$payor_name     = trim($_POST['payor_name']);
$address        = trim($_POST['address'] ?? '');
$payment_mode   = trim($_POST['payment_mode']);
$total_cash_paid= floatval($_POST['total_cash_paid'] ?? 0);
$check_number   = trim($_POST['check_number'] ?? '');
$bank_name      = trim($_POST['bank_name'] ?? '');
$check_date     = trim($_POST['check_date'] ?? '');
$check_amount   = floatval($_POST['check_amount'] ?? 0);
$treasurer      = trim($_POST['treasurer'] ?? '');
$created_by     = $_SESSION['full_name'] ?? 'System User';

$ngas_codes             = $_POST['ngas_code'] ?? [];
$natures_of_collection  = $_POST['nature_of_collection'] ?? [];
$amounts                = $_POST['amount'] ?? [];

// Compute grand total
$grand_total = array_sum(array_map('floatval', $amounts));

$conn->begin_transaction();

try {
    $stmt = $conn->prepare("INSERT INTO form51 
        (form_no, date_issued, payor_name, address, payment_mode, total_cash_paid, 
        check_number, bank_name, check_date, check_amount, treasurer, grand_total, created_by) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "sssssdsssdsds",
        $form_no, $date_issued, $payor_name, $address, $payment_mode, 
        $total_cash_paid, $check_number, $bank_name, $check_date, 
        $check_amount, $treasurer, $grand_total, $created_by
    );
    $stmt->execute();
    $form51_id = $conn->insert_id;

    // Insert child items
    $item_stmt = $conn->prepare("INSERT INTO form51_items (form51_id, ngas_code, nature_of_collection, amount) VALUES (?, ?, ?, ?)");
    foreach ($ngas_codes as $index => $code) {
        $nature = $natures_of_collection[$index] ?? '';
        $amount = floatval($amounts[$index] ?? 0);
        $item_stmt->bind_param("issd", $form51_id, $code, $nature, $amount);
        $item_stmt->execute();
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Form 51 successfully added.']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
