<?php
require_once __DIR__ . '/../config/db.php';

$ctc_no = $_GET['ctccorp_no'] ?? '';

$response = [
    'exists' => false,
    'suggested' => ''
];

// Check if CTC number already exists
$stmt = $mysqli->prepare("SELECT COUNT(*) as cnt FROM ctc_corporation WHERE ctccorp_no = ?");
$stmt->bind_param('s', $ctc_no);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
if ($res['cnt'] > 0) {
    $response['exists'] = true;
}

// Get next suggested CTC number
$stmtLast = $mysqli->query("SELECT ctccorp_no FROM ctc_corporation ORDER BY id DESC LIMIT 1");
$lastCTC = $stmtLast->fetch_assoc()['ctccorp_no'] ?? '0000';
$lastNumber = intval($lastCTC);
$nextCTC = str_pad($lastNumber + 1, strlen($lastCTC), '0', STR_PAD_LEFT);

$response['suggested'] = $nextCTC;

echo json_encode($response);
