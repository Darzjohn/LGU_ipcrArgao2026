<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';

$emp_idno = trim($_GET['emp_idno'] ?? '');

if ($emp_idno) {
    $stmt = $mysqli->prepare("SELECT name FROM employees WHERE emp_idno = ?");
    $stmt->bind_param("s", $emp_idno);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($result) {
        echo json_encode(['success' => true, 'name' => $result['name']]);
        exit;
    }
}

echo json_encode(['success' => false, 'name' => '']);
exit;
