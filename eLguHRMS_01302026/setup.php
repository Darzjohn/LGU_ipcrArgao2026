<?php
/**
 * RPTMS Setup Script
 * ---------------------------------------------
 * Creates the default admin account if it does not exist.
 * Delete or rename this file after successful setup.
 */

require_once __DIR__ . '/config/db.php';

echo "<h2>🛠️ RPTMS Setup</h2>";

// Check if 'users' table exists
$table_check = $mysqli->query("SHOW TABLES LIKE 'users'");
if ($table_check->num_rows === 0) {
    echo "<p style='color:red;'>❌ The <strong>users</strong> table does not exist. Please import the SQL schema first.</p>";
    exit;
}

// Check if admin exists
$check_admin = $mysqli->query("SELECT id FROM users WHERE username='admin' LIMIT 1");

if ($check_admin && $check_admin->num_rows > 0) {
    echo "<p style='color:orange;'>⚠️ Admin account already exists. No changes made.</p>";
} else {
    // Create default admin
    $username = 'admin';
    $password = password_hash('Admin@123', PASSWORD_DEFAULT);
    $role = 'admin';
    $name = 'System Administrator';

    $stmt = $mysqli->prepare("
        INSERT INTO users (username, password, role, name, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param("ssss", $username, $password, $role, $name);

    if ($stmt->execute()) {
        echo "<p style='color:green;'>✅ Default admin account created successfully!</p>";
        echo "<ul>
                <li><strong>Username:</strong> admin</li>
                <li><strong>Password:</strong> Admin@123</li>
                <li><strong>Role:</strong> admin</li>
              </ul>";
        echo "<p>➡️ You can now <a href='auth/login.php'>login here</a>.</p>";
    } else {
        echo "<p style='color:red;'>❌ Error creating admin account: " . htmlspecialchars($stmt->error) . "</p>";
    }

    $stmt->close();
}

echo "<hr><p style='font-size:0.9em;color:#555;'>⚠️ For security, please delete <code>setup.php</code> after successful installation.</p>";
?>
