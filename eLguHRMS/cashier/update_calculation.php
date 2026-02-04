<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

// ✅ Only Admin, Treasurer, and Cashier can perform updates
if (!in_array($_SESSION['role'], ['admin','treasurer','cashier'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

// ✅ Ensure valid request
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['ids'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid or missing parameters.']);
    exit;
}

// Parse IDs
$ids = array_filter(array_map('intval', explode(',', $_POST['ids'])));
if (empty($ids)) {
    echo json_encode(['success' => false, 'message' => 'No valid IDs provided.']);
    exit;
}

$updated = 0;

// ✅ Loop through selected records
foreach ($ids as $id) {
    $res = $mysqli->query("SELECT basic_tax, sef_tax, adjustments, discount, penalty FROM payments_list WHERE id = $id");
    if (!$res || $res->num_rows === 0) continue;

    $r = $res->fetch_assoc();

    $basic_tax   = floatval($r['basic_tax']);
    $sef_tax     = floatval($r['sef_tax']);
    $adjustments = floatval($r['adjustments']);
    $discount    = floatval($r['discount']);
    $penalty     = floatval($r['penalty']);

    // ✅ Compute new total_due formula
    $total_due = ($basic_tax + $sef_tax + $adjustments + $penalty) - $discount;

    // Update row
    $stmt = $mysqli->prepare("UPDATE payments_list SET total_due = ? WHERE id = ?");
    $stmt->bind_param('di', $total_due, $id);
    if ($stmt->execute()) $updated++;
    $stmt->close();
}

if ($updated > 0) {
    echo json_encode(['success' => true, 'message' => "Updated $updated record(s) successfully."]);
} else {
    echo json_encode(['success' => false, 'message' => 'No records updated.']);
}
?>
