<?php
/**
 * setup_roles.php
 * One-time setup script to create default user accounts for RPTMS roles.
 * DELETE this file after running once for security.
 */

require_once __DIR__ . '/config/db.php';

// Define roles and names
$default_users = [
    ['username' => 'admin',             'role' => 'admin',             'name' => 'System Administrator'],
    ['username' => 'assessor',          'role' => 'assessor',          'name' => 'Municipal Assessor'],
    ['username' => 'assessment_clerk',  'role' => 'assessment_clerk',  'name' => 'Assessment Clerk'],
    ['username' => 'treasurer',         'role' => 'treasurer',         'name' => 'Municipal Treasurer'],
    ['username' => 'cashier',           'role' => 'cashier',           'name' => 'Cashier'],
    ['username' => 'viewer',            'role' => 'viewer',            'name' => 'Public Viewer'],
];

echo "<pre>=== RPTMS Default User Setup ===\n\n";

foreach ($default_users as $user) {
    $username = $user['username'];
    $role = $user['role'];
    $name = $user['name'];

    // Generate a secure random password (8–10 chars)
    $password_plain = substr(bin2hex(random_bytes(5)), 0, 10);
    $password_hash = password_hash($password_plain, PASSWORD_DEFAULT);

    // Check if user exists
    $stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        // Insert new user
        $insert = $mysqli->prepare("INSERT INTO users (username, password, name, role) VALUES (?, ?, ?, ?)");
        $insert->bind_param("ssss", $username, $password_hash, $name, $role);
        if ($insert->execute()) {
            echo "✅ Created: {$username}\n";
            echo "   Role: {$role}\n";
            echo "   Password: {$password_plain}\n";
            echo "   Hash: {$password_hash}\n\n";
        } else {
            echo "❌ Error inserting {$username}: " . htmlspecialchars($insert->error) . "\n\n";
        }
        $insert->close();
    } else {
        echo "⚠️  Skipped: {$username} (already exists)\n\n";
    }

    $stmt->close();
}

$mysqli->close();

echo "=== Setup complete. Copy credentials above, then DELETE this file! ===\n</pre>";
?>
