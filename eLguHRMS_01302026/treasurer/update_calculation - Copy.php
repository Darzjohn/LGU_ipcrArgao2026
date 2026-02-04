<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/session_check.php';

header('Content-Type: application/json');

if (!isset($_POST['ids']) || trim($_POST['ids']) === '') {
    echo json_encode(['success' => false, 'message' => 'No IDs received']);
    exit;
}

$idList = array_filter(array_map('intval', explode(',', $_POST['ids'])));
if (empty($idList)) {
    echo json_encode(['success' => false, 'message' => 'Invalid IDs']);
    exit;
}

$curYear = (int)date('Y');
$curMonth = (int)date('n');

foreach ($idList as $id) {
    $res = $mysqli->query("SELECT * FROM payments_list WHERE id=$id");
    if (!$res || $res->num_rows === 0) continue;

    $row = $res->fetch_assoc();
    $basic_tax = (float)$row['basic_tax'];
    $sef_tax   = (float)$row['sef_tax'];
    $adjustments = (float)$row['adjustments'];
    $assessed_value = (float)$row['assessed_value'];
    $tax_due = $basic_tax + $sef_tax;

    $tax_year = (int)$row['tax_year'];
    $discount = 0; $discountPercent = 0;
    if ($tax_year == $curYear && $curMonth >= 1 && $curMonth <= 3) { $discount = 0.10*$tax_due; $discountPercent=10; }
    if ($tax_year == $curYear + 1 && $curMonth >= 10 && $curMonth <= 12) { $discount = 0.20*$tax_due; $discountPercent=20; }

    $penalty=0; $penaltyPercent=0;
    if ($discountPercent==0) {
        $months_due = ($tax_year < $curYear)? ($curYear-$tax_year)*12+$curMonth : max(0,$curMonth);
        $penalty = min(0.02*$months_due*$tax_due,0.72*$tax_due);
        $penaltyPercent = min($months_due*2,72);
    }

    $total_due = $tax_due + $adjustments - $discount + $penalty;

    $stmt = $mysqli->prepare("UPDATE payments_list SET discount=?, penalty=?, total_due=? WHERE id=?");
    $stmt->bind_param("dddi", $discount, $penalty, $total_due, $id);
    $stmt->execute();
    $stmt->close();
}

echo json_encode(['success' => true]);
