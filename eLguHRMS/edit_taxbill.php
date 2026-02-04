<?php
require 'db.php';
header('Content-Type: application/json');

$bill_id = (int)($_POST['bill_id'] ?? 0);
$basic   = floatval($_POST['basic_tax'] ?? 0);
$sef     = floatval($_POST['sef_tax'] ?? 0);
$adj     = floatval($_POST['adjustments'] ?? 0);

if(!$bill_id) die(json_encode(['success'=>false,'message'=>'Invalid Bill ID']));

$stmt = $mysqli->prepare("UPDATE tax_bills SET basic_tax=?, sef_tax=?, adjustments=? WHERE id=?");
$stmt->bind_param("dddi", $basic, $sef, $adj, $bill_id);
if($stmt->execute()){
    echo json_encode(['success'=>true]);
}else{
    echo json_encode(['success'=>false,'message'=>$stmt->error]);
}
