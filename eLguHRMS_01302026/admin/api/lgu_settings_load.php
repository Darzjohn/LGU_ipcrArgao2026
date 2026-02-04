<?php
require_once '../../db.php';
header('Content-Type: application/json');

$res = $mysqli->query("SELECT setting_key, setting_value FROM settings");
$settings = [];
while ($row = $res->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

echo json_encode(['success' => true, 'settings' => $settings]);
?>
