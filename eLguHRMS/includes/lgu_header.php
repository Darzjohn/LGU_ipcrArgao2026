<?php
require_once __DIR__ . '/../db.php';

$settings = [];
$res = $mysqli->query("SELECT setting_key, setting_value FROM settings");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}

$municipal_logo = isset($settings['municipal_logo']) ? $settings['municipal_logo'] : 'uploads/default_logo.png';
$municipality_name = isset($settings['municipality_name']) ? $settings['municipality_name'] : 'Unknown Municipality';
$province_name = isset($settings['province_name']) ? $settings['province_name'] : 'Unknown Province';

// ✅ Signatories
$prepared_by = isset($settings['prepared_by']) ? $settings['prepared_by'] : 'N/A';
$reviewed_by = isset($settings['reviewed_by']) ? $settings['reviewed_by'] : 'N/A';
$approved_by = isset($settings['approved_by']) ? $settings['approved_by'] : 'N/A';
?>
