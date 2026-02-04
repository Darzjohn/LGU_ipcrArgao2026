<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/session_check.php';

header('Content-Type: application/json');

// ✅ Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'message'=>'Invalid request method.']);
    exit;
}

// ✅ Validate selected_ids
if (empty($_POST['selected_ids'])) {
    echo json_encode(['success'=>false,'message'=>'No selected records.']);
    exit;
}

$selected_ids = array_map('intval', explode(',', $_POST['selected_ids']));
if (count($selected_ids) === 0) {
    echo json_encode(['success'=>false,'message'=>'No selected records.']);
    exit;
}

// ✅ Validate payor and OR
$payor = trim($_POST['payor_name'] ?? '');
$or_no = trim($_POST['or_no'] ?? '');
if ($payor === '') { echo json_encode(['success'=>false,'message'=>'Payor name is required.']); exit; }
if ($or_no === '') { echo json_encode(['success'=>false,'message'=>'Official Receipt Number is required.']); exit; }

// ✅ Check for duplicate OR
$stmt_check = $mysqli->prepare("SELECT COUNT(*) FROM collections WHERE or_no=?");
$stmt_check->bind_param('s',$or_no);
$stmt_check->execute();
$stmt_check->bind_result($count); $stmt_check->fetch(); $stmt_check->close();
if($count>0){ echo json_encode(['success'=>false,'message'=>'OR number already exists.']); exit; }

// ✅ Payment details
$payment_method = strtolower(trim($_POST['payment_method'] ?? 'cash')) === 'check' ? 'check' : 'cash';
$bank_name = $_POST['bank_name'] ?? null;
$check_date = !empty($_POST['check_date']) ? $_POST['check_date'] : null;
$check_amount = !empty($_POST['check_amount']) ? floatval($_POST['check_amount']) : null;
$check_number = $_POST['check_number'] ?? null;
$total_cash_amount = !empty($_POST['total_cash_amount']) ? floatval($_POST['total_cash_amount']) : 0.00;
$payment_date = !empty($_POST['payment_date']) ? $_POST['payment_date'] : date('Y-m-d H:i:s');
$previous_or_no = $_POST['previous_or_no'] ?? null;
$previous_date_paid = $_POST['previous_date_paid'] ?? null;
$previous_year = !empty($_POST['previous_year']) ? intval($_POST['previous_year']) : null;

// ✅ User info
$collecting_officer = 'System User';
if(!empty($_SESSION['user_id'])){
    $uid = intval($_SESSION['user_id']);
    $stmt_user = $mysqli->prepare("SELECT name FROM users WHERE id=? LIMIT 1");
    $stmt_user->bind_param('i',$uid);
    $stmt_user->execute();
    $stmt_user->bind_result($db_name);
    if($stmt_user->fetch() && !empty($db_name)) $collecting_officer = $db_name;
    $stmt_user->close();
}

// ✅ Begin transaction
$mysqli->begin_transaction();
try {
    $selected_ids_str = implode(',',$selected_ids);
    $grand_total = 0.00;
    $rptsp_numbers = [];

    // Lock selected records
    $result = $mysqli->query("SELECT * FROM payments_list WHERE id IN ($selected_ids_str) FOR UPDATE");
    if(!$result || $result->num_rows===0) throw new Exception("Selected payment records not found.");

    // Prepare insert
    $stmt = $mysqli->prepare("
        INSERT INTO collections (
            or_no, previous_or_no, previous_date_paid, previous_year,
            assessment_id, tax_year, rptsp_no, td_no, lot_no, owner_name,
            barangay, location, classification, assessed_value, basic_tax,
            sef_tax, tax_due, adjustments, discount, penalty, total_due,
            payment_date, status, processed_by, owner, payor_name, year,
            owner_id, amount, total_amount_paid, payment_mode, bank_name,
            check_date, check_amount, check_number, total_cash_amount
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        )
    ");

    $types = "ssssiisssssssddddddddsssssidddssssdd";

    while($row=$result->fetch_assoc()){
        $assessment_id   = $row['id'];
        $tax_year        = $row['tax_year'];
        $rptsp_no        = $row['rptsp_no'];
        $td_no           = $row['td_no'];
        $lot_no          = $row['lot_no'];
        $owner_name_row  = $row['owner_name'];
        $barangay        = $row['barangay'];
        $location        = $row['location'];
        $classification  = $row['classification'];
        $assessed_value  = $row['assessed_value'];
        $basic_tax       = $row['basic_tax'];
        $sef_tax         = $row['sef_tax'];
        $tax_due         = $row['tax_due'];
        $adjustments     = $row['adjustments'];
        $discount        = $row['discount'];
        $penalty         = $row['penalty'];
        $total_due       = $row['total_due'];
        $owner_id        = $row['owner_id'] ?? null;
        $amount          = $row['total_due'];
        $status          = 'Paid';
        $owner_col       = $owner_name_row;

        $grand_total += floatval($total_due);

        $stmt->bind_param(
            $types,
            $or_no,
            $previous_or_no,
            $previous_date_paid,
            $previous_year,
            $assessment_id,
            $tax_year,
            $rptsp_no,
            $td_no,
            $lot_no,
            $owner_name_row,
            $barangay,
            $location,
            $classification,
            $assessed_value,
            $basic_tax,
            $sef_tax,
            $tax_due,
            $adjustments,
            $discount,
            $penalty,
            $total_due,
            $payment_date,
            $status,
            $collecting_officer,
            $owner_col,
            $payor,
            $tax_year,
            $owner_id,
            $amount,
            $amount,
            $payment_method,
            $bank_name,
            $check_date,
            $check_amount,
            $check_number,
            $total_cash_amount
        );

        if(!$stmt->execute()) throw new Exception("Insert failed: ".$stmt->error);

        $mysqli->query("UPDATE payments_list SET status='Paid' WHERE id=$assessment_id");
        if(!empty($rptsp_no)) $rptsp_numbers[]=$mysqli->real_escape_string($rptsp_no);
    }

    // Update tax bills
    if(!empty($rptsp_numbers)){
        $rptsp_list = "'".implode("','",$rptsp_numbers)."'";
        $mysqli->query("UPDATE tax_bills SET status='paid' WHERE rptsp_no IN ($rptsp_list)");
    }

    // Remove from payments_list
    $mysqli->query("DELETE FROM payments_list WHERE id IN ($selected_ids_str)");

    $mysqli->commit();
    echo json_encode([
        'success'=>true,
        'message'=>'Payment processed successfully.',
        'or_no'=>$or_no,
        'payment_mode'=>$payment_method,
        'processed_by'=>$collecting_officer,
        'grand_total'=>number_format($grand_total,2,'.','')
    ]);

}catch(Exception $e){
    $mysqli->rollback();
    echo json_encode(['success'=>false,'message'=>'Transaction failed: '.$e->getMessage()]);
}
?>
