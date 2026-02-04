<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: ctc_list.php");
    exit;
}

// Optional: verify record exists
$res = $mysqli->query("SELECT ctc_no FROM ctc_individual WHERE id = $id");
if ($res->num_rows == 0) {
    header("Location: ctc_list.php?error=notfound");
    exit;
}

$mysqli->query("DELETE FROM ctc_individual WHERE id = $id");
header("Location: ctc_list.php?deleted=1");
exit;
