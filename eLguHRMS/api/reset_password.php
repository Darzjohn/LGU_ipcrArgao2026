<?php
require_once '../db.php';

$id = intval($_POST['id']);
$new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
$stmt->bind_param("si", $new_password, $id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Password reset successfully']);
} else {
    echo json_encode(['status' => 'danger', 'message' => 'Password reset failed']);
}
?>
