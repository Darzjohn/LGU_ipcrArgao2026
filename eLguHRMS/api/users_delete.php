<?php
require_once '../db.php';
$id = intval($_POST['id']);
$conn->query("DELETE FROM users WHERE id = $id");
echo json_encode(['status' => 'success', 'message' => 'User deleted successfully']);
?>
