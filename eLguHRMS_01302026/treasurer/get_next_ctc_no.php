<?php
require_once __DIR__ . '/../config/db.php';

$ctccorp_no = $_GET['ctccorp_no'] ?? '';
$exclude_id = $_GET['exclude_id'] ?? 0;

$stmt = $mysqli->prepare("SELECT id FROM ctc_corporation WHERE ctccorp_no=?".($exclude_id ? " AND id!=?" : ""));
if($exclude_id){
    $stmt->bind_param('si', $ctccorp_no, $exclude_id);
}else{
    $stmt->bind_param('s', $ctccorp_no);
}
$stmt->execute();
$res = $stmt->get_result();

echo json_encode(['exists' => $res->num_rows > 0]);
