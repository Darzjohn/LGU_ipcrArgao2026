<?php
require_once '../db.php';
$data = [];
$res = $conn->query("SELECT id, username, fullname, role, created_at FROM users ORDER BY id DESC");
while ($row = $res->fetch_assoc()) $data[] = $row;
echo json_encode(['data' => $data]);
?>
