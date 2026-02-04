<?php
require_once __DIR__ . '/../config/db.php';

function log_access_attempt($type, $user_id = null, $username = null, $role = null) {
    global $mysqli;

    $page = $_SERVER['REQUEST_URI'] ?? 'unknown';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

    $stmt = $mysqli->prepare("
        INSERT INTO access_logs (user_id, username, role, page_accessed, access_type, ip_address, user_agent)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("issssss", $user_id, $username, $role, $page, $type, $ip, $agent);
    $stmt->execute();
    $stmt->close();
}
