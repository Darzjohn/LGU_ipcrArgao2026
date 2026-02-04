<?php
// admin_debug.php — DIAGNOSTIC (DELETE after use)
require_once __DIR__ . '/config/db.php';

echo "<pre>";

// Check DB connection
if ($mysqli->connect_errno) {
    echo "❌ DB connection error: " . $mysqli->connect_error . "\n";
    exit;
}

echo "✅ Connected to: " . $mysqli->host_info . "\n";

// Check if `users` table exists
$table = $mysqli->query("SHOW TABLES LIKE 'users'");
if ($table && $table->num_rows > 0) {
    echo "✅ 'users' table exists.\n\n";
} else {
    echo "❌ 'users' table not found.\n\n";
    exit;
}

// Fetch admin user info
$stmt = $mysqli->prepare("SELECT id, username, password, role FROM users WHERE username = 'admin' LIMIT 1");
$stmt->execute();
$res = $stmt->get_result();

if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();
    echo "🧩 Admin user found:\n";
    echo "  ID: {$row['id']}\n";
    echo "  Username: {$row['username']}\n";
    echo "  Role: {$row['role']}\n";
    echo "  Stored password hash:\n{$row['password']}\n";
} else {
    echo "⚠️ No admin user found.\n";
}

$stmt->close();

echo "\n-- END --";
echo "</pre>";
?>
