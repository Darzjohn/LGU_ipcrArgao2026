<?php
require_once '../db.php';
$username = $_POST['username'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$fullname = $_POST['fullname'];
$role = $_POST['role'];

$stmt = $conn->prepare("INSERT INTO users (username, password, fullname, role, created_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("ssss", $username, $password, $fullname, $role);
if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'User added successfully']);
} else {
    echo json_encode(['status' => 'danger', 'message' => 'Failed to add user']);
}
?>
