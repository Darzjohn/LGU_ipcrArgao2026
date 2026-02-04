<?php
require_once '../../db.php';
header('Content-Type: application/json');

$id = $_POST['id'] ?? '';
$position = trim($_POST['position']);
$name = trim($_POST['name']);
$office = trim($_POST['office']);
$footer_role = $_POST['footer_role'] ?? 'None';
$is_active = isset($_POST['is_active']) ? 1 : 0;

if (empty($position) || empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Position and Name are required.']);
    exit;
}

// Ensure only one of each footer role is active
if (in_array($footer_role, ['Prepared by', 'Reviewed by', 'Approved by'])) {
    $mysqli->query("UPDATE officials_list SET footer_role='None' WHERE footer_role='$footer_role'");
}

if ($id) {
    $stmt = $mysqli->prepare("UPDATE officials_list 
        SET position=?, name=?, office=?, footer_role=?, is_active=? 
        WHERE id=?");
    $stmt->bind_param('ssssii', $position, $name, $office, $footer_role, $is_active, $id);
} else {
    $stmt = $mysqli->prepare("INSERT INTO officials_list 
        (position, name, office, footer_role, is_active) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('ssssi', $position, $name, $office, $footer_role, $is_active);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Official saved successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
}
$stmt->close();
?>
