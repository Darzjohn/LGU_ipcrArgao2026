<?php
// verify_password_direct.php (DELETE after use)

require_once __DIR__ . '/config/db.php';

$plain = 'Admin@123'; // the password you expect
$sql = "SELECT password FROM users WHERE username='admin' LIMIT 1";
$res = $mysqli->query($sql);

if (!$res) {
    echo "DB error: " . htmlspecialchars($mysqli->error);
    exit;
}

$row = $res->fetch_assoc();
if (!$row) {
    echo "No admin user found.\n";
    exit;
}

$hash = $row['password'];

echo "<pre>Stored hash length: " . strlen($hash) . "\n\n";
echo "Stored hash:\n" . htmlspecialchars($hash) . "\n\n";
echo "password_verify('{$plain}', hash) returns: ";
var_export(password_verify($plain, $hash));
echo "\n\n-- END --</pre>";
