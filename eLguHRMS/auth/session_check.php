<?php
session_start();
require_once __DIR__ . '/../config/db.php'; // Ensure $mysqli is available

// 1️⃣ Check if user is logged in and has a role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    session_destroy();
    header("Location: ../auth/login.php?error=unauthorized");
    exit;
}

// 2️⃣ Determine current folder (role-based protection)
$pathParts = explode(DIRECTORY_SEPARATOR, $_SERVER['SCRIPT_FILENAME']);
$roleFolders = ['admin','assessor','assessment_clerk','treasurer','cashier','viewer','hr','hr_staff','employee'];
$currentRoleFolder = null;

foreach ($roleFolders as $folder) {
    if (in_array($folder, $pathParts)) {
        $currentRoleFolder = $folder;
        break;
    }
}

// 3️⃣ Map folder → role in DB
$roleMap = [
    'admin' => 'admin',
    'assessor' => 'assessor',
    'assessment' => 'assessment_clerk',
    'treasurer' => 'treasurer',
    'cashier' => 'cashier',
    'viewer' => 'viewer',
    'hr' => 'hr',
    'hr_staff' => 'hr_staff',
    'employee' => 'employee'
];

// 4️⃣ Restrict access if role mismatch
if ($currentRoleFolder) {
    $expectedRole = $roleMap[$currentRoleFolder] ?? null;
    if ($expectedRole && $_SESSION['role'] !== $expectedRole) {
        session_destroy();
        header("Location: ../auth/login.php?error=unauthorized");
        exit;
    }
}

// 5️⃣ Fetch emp_idno from users/employees if not in session
if (!isset($_SESSION['emp_idno'])) {
    $user_id = $_SESSION['user_id'];

    // Get emp_idno from users table
    $stmt = $mysqli->prepare("SELECT emp_idno, name, email, contact_no FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $emp_idno = $user['emp_idno'] ?? null;

    // If emp_idno empty, try to match in employees table
    if (empty($emp_idno) && !empty($user['name'])) {
        $stmt = $mysqli->prepare("SELECT emp_idno FROM employees WHERE name = ? LIMIT 1");
        $stmt->bind_param("s", $user['name']);
        $stmt->execute();
        $employee = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($employee && $employee['emp_idno']) {
            $emp_idno = $employee['emp_idno'];

            // Update users table
            $update = $mysqli->prepare("UPDATE users SET emp_idno = ? WHERE id = ?");
            $update->bind_param("si", $emp_idno, $user_id);
            $update->execute();
            $update->close();
        }
    }

    $_SESSION['emp_idno'] = $emp_idno;
}

// ✅ User is logged in and authorized
?>
