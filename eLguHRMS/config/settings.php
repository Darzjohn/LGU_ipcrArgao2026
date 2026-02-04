<?php
// config/settings.php
require_once __DIR__ . '/db.php';

// ✅ Load settings from the database
$settings = [
    'system_name' => 'eLGU Human Resource Management System',
    'municipality' => 'Default Municipality',
    'logo' => 'logo.png',
    'background' => 'background.jpg'
];

$res = $mysqli->query("SELECT * FROM system_settings LIMIT 1");
if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();
    $settings['system_name'] = $row['system_name'] ?: $settings['system_name'];
    $settings['municipality'] = $row['municipality'] ?: $settings['municipality'];
    $settings['logo'] = $row['logo'] ?: $settings['logo'];
    $settings['background'] = $row['background'] ?: $settings['background'];
}

// ✅ Define constants for easier access
define('SYSTEM_NAME', $settings['system_name']);
define('MUNICIPALITY', $settings['municipality']);
define('SYSTEM_LOGO', 'uploads/' . $settings['logo']);
define('SYSTEM_BACKGROUND', 'uploads/' . $settings['background']);
