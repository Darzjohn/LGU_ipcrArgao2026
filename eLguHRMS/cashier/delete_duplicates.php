<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

// ✅ Only POST allowed
if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    echo json_encode(['success'=>false,'message'=>'Invalid request method.']);
    exit;
}

$ids = trim($_POST['ids'] ?? '');
if(empty($ids)){
    echo json_encode(['success'=>false,'message'=>'No records selected.']);
    exit;
}

$ids_arr = array_map('intval', explode(',', $ids));
if(count($ids_arr) === 0){
    echo json_encode(['success'=>false,'message'=>'No valid IDs.']);
    exit;
}

// Delete only unpaid rows
$ids_str = implode(',', $ids_arr);
$mysqli->query("DELETE FROM payments_list WHERE id IN ($ids_str) AND LOWER(status) != 'paid'");
echo json_encode(['success'=>true,'message'=>'Duplicates deleted successfully.']);
?>
