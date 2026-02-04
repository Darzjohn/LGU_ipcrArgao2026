<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';

function jsonErr($msg){ echo json_encode(['success'=>false,'message'=>$msg]); exit; }

if ($action === 'get_items') {
    $id = intval($_GET['id'] ?? 0);
    $stmt = $conn->prepare("SELECT id, ngas_code, nature_of_collection, amount FROM form51_items WHERE form51_id = ? ORDER BY id ASC");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    $res = $stmt->get_result();
    $items = $res->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success'=>true,'items'=>$items]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonErr('Invalid request method.');

if ($action === 'add') {
    // collect
    $form_no = trim($_POST['form_no'] ?? '');
    $date_issued = trim($_POST['date_issued'] ?? null);
    $payor_name = trim($_POST['payor_name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $payment_mode = trim($_POST['payment_mode'] ?? 'cash');
    $total_cash_paid = floatval($_POST['total_cash_paid'] ?? 0);
    $check_number = trim($_POST['check_number'] ?? null);
    $bank_name = trim($_POST['bank_name'] ?? null);
    $check_date = trim($_POST['check_date'] ?? null);
    $check_amount = floatval($_POST['check_amount'] ?? 0);
    $treasurer = trim($_POST['treasurer'] ?? $_SESSION['full_name'] ?? '');
    $created_by = $_SESSION['full_name'] ?? 'System';
    $ngas = $_POST['add_ngas_code'] ?? [];
    $natures = $_POST['add_nature'] ?? [];
    $amounts = $_POST['add_amount'] ?? [];

    // compute grand total from items (server-side)
    $grand_total = 0;
    foreach ($amounts as $a) $grand_total += floatval($a);

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT INTO form51 (form_no,date_issued,payor_name,address,payment_mode,total_cash_paid,check_number,bank_name,check_date,check_amount,treasurer,grand_total,created_by,created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())");
        $stmt->bind_param("sssssdsssdsds", $form_no,$date_issued,$payor_name,$address,$payment_mode,$total_cash_paid,$check_number,$bank_name,$check_date,$check_amount,$treasurer,$grand_total,$created_by);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $form51_id = $conn->insert_id;

        $item_stmt = $conn->prepare("INSERT INTO form51_items (form51_id, ngas_code, nature_of_collection, amount) VALUES (?, ?, ?, ?)");
        foreach ($ngas as $i => $code) {
            $nature = $natures[$i] ?? '';
            $amt = floatval($amounts[$i] ?? 0);
            $item_stmt->bind_param("issd", $form51_id, $code, $nature, $amt);
            if (!$item_stmt->execute()) throw new Exception($item_stmt->error);
        }

        $conn->commit();
        echo json_encode(['success'=>true,'message'=>'Form 51 added.']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success'=>false,'message'=>'Error: '.$e->getMessage()]);
    }
    exit;
}

if ($action === 'update') {
    $id = intval($_POST['id'] ?? 0);
    if (!$id) jsonErr('Missing id.');
    $form_no = trim($_POST['form_no'] ?? '');
    $date_issued = trim($_POST['date_issued'] ?? null);
    $payor_name = trim($_POST['payor_name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $payment_mode = trim($_POST['payment_mode'] ?? 'cash');
    $total_cash_paid = floatval($_POST['total_cash_paid'] ?? 0);
    $check_number = trim($_POST['check_number'] ?? null);
    $bank_name = trim($_POST['bank_name'] ?? null);
    $check_date = trim($_POST['check_date'] ?? null);
    $check_amount = floatval($_POST['check_amount'] ?? 0);
    $treasurer = trim($_POST['treasurer'] ?? $_SESSION['full_name'] ?? '');
    $updated_by = $_SESSION['full_name'] ?? 'System';
    $ngas = $_POST['edit_ngas_code'] ?? [];
    $natures = $_POST['edit_nature'] ?? [];
    $amounts = $_POST['edit_amount'] ?? [];

    // compute grand total from items
    $grand_total = 0;
    foreach ($amounts as $a) $grand_total += floatval($a);

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("UPDATE form51 SET form_no=?, date_issued=?, payor_name=?, address=?, payment_mode=?, total_cash_paid=?, check_number=?, bank_name=?, check_date=?, check_amount=?, treasurer=?, grand_total=?, updated_by=?, updated_at=NOW() WHERE id=?");
        $stmt->bind_param("sssssdsssdsdsi", $form_no,$date_issued,$payor_name,$address,$payment_mode,$total_cash_paid,$check_number,$bank_name,$check_date,$check_amount,$treasurer,$grand_total,$updated_by,$id);
        if (!$stmt->execute()) throw new Exception($stmt->error);

        // delete old items and insert new ones
        $del = $conn->prepare("DELETE FROM form51_items WHERE form51_id = ?");
        $del->bind_param("i", $id);
        if (!$del->execute()) throw new Exception($del->error);

        $item_stmt = $conn->prepare("INSERT INTO form51_items (form51_id, ngas_code, nature_of_collection, amount) VALUES (?, ?, ?, ?)");
        foreach ($ngas as $i => $code) {
            $nature = $natures[$i] ?? '';
            $amt = floatval($amounts[$i] ?? 0);
            $item_stmt->bind_param("issd", $id, $code, $nature, $amt);
            if (!$item_stmt->execute()) throw new Exception($item_stmt->error);
        }

        $conn->commit();
        echo json_encode(['success'=>true,'message'=>'Form 51 updated.']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success'=>false,'message'=>'Error: '.$e->getMessage()]);
    }
    exit;
}

if ($action === 'delete') {
    $id = intval($_POST['id'] ?? 0);
    if (!$id) jsonErr('Missing id.');
    $conn->begin_transaction();
    try {
        $delItems = $conn->prepare("DELETE FROM form51_items WHERE form51_id = ?");
        $delItems->bind_param("i", $id);
        if (!$delItems->execute()) throw new Exception($delItems->error);

        $del = $conn->prepare("DELETE FROM form51 WHERE id = ?");
        $del->bind_param("i", $id);
        if (!$del->execute()) throw new Exception($del->error);

        $conn->commit();
        echo json_encode(['success'=>true,'message'=>'Record deleted.']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success'=>false,'message'=>'Error: '.$e->getMessage()]);
    }
    exit;
}

jsonErr('Unknown action.');
