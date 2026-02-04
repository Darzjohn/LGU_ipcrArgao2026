<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';

// Only admin can fetch
if ($_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

header('Content-Type: application/json');

$result = $mysqli->query("SELECT id, name FROM departments ORDER BY name ASC");
$departments = [];
while ($row = $result->fetch_assoc()) {
    $departments[] = $row;
}

echo json_encode($departments);
