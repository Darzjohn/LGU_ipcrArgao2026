<?php
require_once '../db.php';

$id = intval($_POST['id']);
$fullname = $_POST['fullname'];
$role = $_POST['role'];

$stmt = $conn->prepare("UPDATE users SET fullname=?, role=? WHERE id=?");
$stmt->bind_param("ssi", $fullname, $role, $id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'User updated successfully']);
} else {
    echo json_encode(['status' => 'danger', 'message' => 'Update failed']);
}
?>
