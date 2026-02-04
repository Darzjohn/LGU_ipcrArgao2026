<?php
require_once __DIR__ . '/../config/db.php';

$ctc_no = intval($_GET['ctc_no']);
$exclude_id = intval($_GET['exclude_id']);

// Check duplicate excluding current ID
$stmt = $mysqli->prepare("SELECT COUNT(*) AS count FROM ctc_individual WHERE ctc_no=? AND id<>?");
$stmt->bind_param('ii', $ctc_no, $exclude_id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();

// Get next available CTC No
$next_ctc = $mysqli->query("SELECT MAX(ctc_no) AS max_no FROM ctc_individual")->fetch_assoc()['max_no'] + 1;

echo json_encode([
    'exists' => $res['count'] > 0,
    'next_ctc_no' => $next_ctc
]);
