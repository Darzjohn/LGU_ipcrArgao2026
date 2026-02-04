<?php
require_once __DIR__ . '/../config/db.php';

$ctccorp_no = $_GET['ctccorp_no'] ?? '';
$exists = false;

if ($ctccorp_no !== '') {
    $stmt = $mysqli->prepare("SELECT id FROM ctc_corporation WHERE ctccorp_no = ?");
    $stmt->bind_param("s", $ctccorp_no);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) $exists = true;
}

header('Content-Type: application/json');
echo json_encode(['exists' => $exists]);
