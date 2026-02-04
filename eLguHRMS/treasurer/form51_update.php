<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$id = intval($_POST['id']);
$form_no = trim($_POST['form_no']);
$date_issued = trim($_POST['date_issued']);
$payor_name = trim($_POST['payor_name']);
$address = trim($_POST['address'] ?? '');
$payment_mode = trim($_POST['payment_mode']);
$total_cash_paid = floatval($_POST['total_cash_paid'] ?? 0);
$check_number = trim($_POST['check_number'] ?? '');
$bank_name = trim($_POST['bank_name'] ?? '');
$check_date = trim($_POST['check_date'] ?? '');
$check_amount = floatval($_POST['check_amount'] ?? 0);
$treasurer = trim($_POST['treasurer'] ?? '');
$updated_by = $_SESSION['full_name'] ?? 'System User';
$updated_at = date('Y-m-d H:i:s');

$conn->begin_transaction();

try {
    $stmt = $conn->prepare("UPDATE form51 SET 
        form_no=?, date_issued=?, payor_name=?, address=?, payment_mode=?, total_cash_paid=?, 
        check_number=?, bank_name=?, check_date=?, check_amount=?, treasurer=?, updated_by=?, updated_at=? 
        WHERE id=?");
    $stmt->bind_param(
        "sssssdsssdsssi",
        $form_no, $date_issued, $payor_name, $address, $payment_mode, 
        $total_cash_paid, $check_number, $bank_name, $check_date, 
        $check_amount, $treasurer, $updated_by, $updated_at, $id
    );
    $stmt->execute();

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Form 51 updated successfully.']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
