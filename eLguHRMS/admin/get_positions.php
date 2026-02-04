<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';

$dept_id = (int)($_GET['department_id'] ?? 0);
$res = $mysqli->query("SELECT id, name FROM positions WHERE department_id=$dept_id ORDER BY name ASC");

$positions = [];
while($row=$res->fetch_assoc()) $positions[] = $row;

header('Content-Type: application/json');
echo json_encode($positions);
