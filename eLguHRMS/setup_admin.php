<?php
// setup_admin.php
require_once __DIR__ . '/config/db.php';

$username = 'admin';
$password_plain = 'Admin@123';
$name = 'System Administrator';
$role = 'admin';

$hash = password_hash($password_plain, PASSWORD_DEFAULT);

$stmt = $mysqli->prepare("
    INSERT INTO users (username, password, name, role)
    VALUES (?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE password = VALUES(password), name = VALUES(name), role = VALUES(role)
");
$stmt->bind_param("ssss", $username, $hash, $name, $role);

if ($stmt->execute()) {
    echo "<pre>✅ Admin account created or updated successfully.\n";
    echo "Username: {$username}\n";
    echo "Password: {$password_plain}\n";
    echo "Role: {$role}\n\n";
    echo "Stored hash:\n{$hash}\n</pre>";
} else {
    echo "<pre>❌ Error: " . htmlspecialchars($stmt->error) . "</pre>";
}

$stmt->close();
$mysqli->close();
