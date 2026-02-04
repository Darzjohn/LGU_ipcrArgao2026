<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $field = $_POST['field'] ?? '';
    $value = trim($_POST['value'] ?? '');

    $allowed = ['classification','assessed_value','basic_tax','sef_tax','discount','penalty','total_due'];
    if (!in_array($field, $allowed)) {
        exit('invalid');
    }

    if (in_array($field, ['assessed_value','basic_tax','sef_tax','discount','penalty','total_due'])) {
        $value = floatval($value);
        $stmt = $mysqli->prepare("UPDATE payments_list SET $field = ? WHERE id = ?");
        $stmt->bind_param('di', $value, $id);
    } else {
        $stmt = $mysqli->prepare("UPDATE payments_list SET $field = ? WHERE id = ?");
        $stmt->bind_param('si', $value, $id);
    }

    if ($stmt->execute()) echo 'success';
    else echo 'error';

    $stmt->close();
}
?>
