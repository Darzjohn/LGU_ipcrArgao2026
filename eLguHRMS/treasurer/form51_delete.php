<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$id = intval($_POST['id']);
$conn->begin_transaction();

try {
    $conn->query("DELETE FROM form51_items WHERE form51_id = $id");
    $conn->query("DELETE FROM form51 WHERE id = $id");

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Record deleted successfully.']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
