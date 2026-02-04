<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

// ✅ Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'message'=>'Invalid request method.']);
    exit;
}

// Validate inputs
$id = intval($_POST['id'] ?? 0);
$field = trim($_POST['field'] ?? '');
$value = trim($_POST['value'] ?? '');

$allowedFields = [
    'barangay','location','classification','assessed_value','basic_tax',
    'sef_tax','adjustments','discount','penalty','total_due','processed_by'
];

if($id <= 0 || !in_array($field, $allowedFields)){
    echo json_encode(['success'=>false,'message'=>'Invalid input.']);
    exit;
}

// Normalize numeric values
$numericFields = ['assessed_value','basic_tax','sef_tax','adjustments','discount','penalty','total_due'];
if(in_array($field,$numericFields)){
    $value = str_replace(['₱',','],'',$value);
    $value = is_numeric($value) ? floatval($value) : 0;
}

// Update
$stmt = $mysqli->prepare("UPDATE payments_list SET $field=? WHERE id=?");
$stmt->bind_param(in_array($field,$numericFields)?'di':'si', $value, $id);
if($stmt->execute()){
    $normalized = in_array($field,$numericFields) ? number_format($value,2,'.','') : $value;
    echo json_encode(['success'=>true,'normalized'=>$normalized]);
} else {
    echo json_encode(['success'=>false,'message'=>$stmt->error]);
}
$stmt->close();
?>
