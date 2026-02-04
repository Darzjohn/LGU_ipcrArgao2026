<?php
require_once __DIR__ . '/../config/db.php';

if (isset($_GET['ctccorp_no'])) {
    $ctc_no = $_GET['ctccorp_no'];
    $stmt = $mysqli->prepare("SELECT id FROM ctc_corporation WHERE ctccorp_no = ?");
    $stmt->bind_param('s', $ctc_no);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo json_encode(['exists' => true]);
    } else {
        echo json_encode(['exists' => false]);
    }
}
?>
