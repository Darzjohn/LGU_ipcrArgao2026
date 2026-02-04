<?php
session_start();

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



// ✅ User is logged in and authorized
?>
