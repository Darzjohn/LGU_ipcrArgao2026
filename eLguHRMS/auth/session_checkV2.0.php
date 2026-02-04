<?php
session_start();

// If user not logged in, redirect to login
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location: auth/login.php");
    exit;
}

// Optional: Refresh session timeout (if you want auto-logout after inactivity)
$timeout_duration = 1800; // 30 minutes
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: auth/login.php?session=expired");
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();
