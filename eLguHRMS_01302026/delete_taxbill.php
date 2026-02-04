<?php
require 'db.php';
header('Content-Type: application/json');

$bill_id = (int)($_POST['bill_id'] ?? 0);
if(!$bill_id) die(json_encode(['success'=>false,'message'=>'Invalid Bill ID']));

$stmt = $mysqli->prepare("DELETE FROM tax_bills WHERE id=?");
$stmt->bind_param("i", $bill_id);
if($stmt->execute()){
    echo json_encode(['success'=>true]);
}else{
    echo json_encode(['success'=>false,'message'=>$stmt->error]);
}
