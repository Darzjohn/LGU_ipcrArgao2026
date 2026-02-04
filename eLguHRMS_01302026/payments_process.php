<?php
require_once __DIR__ . '/../db.php'; // connect to root db.php
header('Content-Type: application/json');

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Validate selected IDs
if (empty($_POST['selected_ids']) || !is_array($_POST['selected_ids'])) {
    echo json_encode(['success' => false, 'message' => 'No tax bills selected.']);
    exit;
}

$selected_ids = array_map('intval', $_POST['selected_ids']);
$paid_date = date('Y-m-d');
$insertedCount = 0;
$errorMessages = [];

// --- Get the last OR number ---
$orResult = $mysqli->query("SELECT official_receipt_no FROM payments_list ORDER BY id DESC LIMIT 1");
if ($orResult && $orResult->num_rows > 0) {
    $lastOR = $orResult->fetch_assoc()['official_receipt_no'];
    $lastParts = explode('-', $lastOR);
    $prefix = $lastParts[0] ?? date('Y');
    $number = isset($lastParts[1]) ? intval($lastParts[1]) + 1 : 1;
    $newOR = $prefix . '-' . str_pad($number, 6, '0', STR_PAD_LEFT);
} else {
    $newOR = date('Y') . '-000001';
}

// --- Process each bill ---
foreach ($selected_ids as $bill_id) {

    // Retrieve related tax bill, assessment, and owner info
    $sql = "
        SELECT tb.*, a.basic_tax, a.sef_tax, a.adjustments,
               p.td_no, p.location, p.barangay, p.classification, p.assessed_value,
               o.name AS owner_name, o.id AS owner_id
        FROM tax_bills tb
        JOIN assessments a ON a.id = tb.assessment_id
        JOIN properties p ON p.id = a.property_id
        LEFT JOIN owners o ON o.id = p.owner_id
        WHERE tb.id = $bill_id
        LIMIT 1
    ";

    $res = $mysqli->query($sql);
    if (!$res || $res->num_rows === 0) {
        $errorMessages[] = "Bill ID $bill_id not found.";
        continue;
    }

    $bill = $res->fetch_assoc();
    $basic = floatval($bill['basic_tax']);
    $sef = floatval($bill['sef_tax']);
    $adjust = floatval($bill['adjustments']);
    $total = $basic + $sef + $adjust;

    // Prepare statement for inserting payment record
    $stmt = $mysqli->prepare("
        INSERT INTO payments_list 
        (taxbill_id, payor_name, previous_or_no, previous_date_paid, previous_year, owners_id, 
         total_amount_paid, official_receipt_no, payment_date, remarks)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        $errorMessages[] = "Prepare failed for Bill ID $bill_id: " . $mysqli->error;
        continue;
    }

    $payor_name = $bill['owner_name'] ?? 'Unknown Owner';
    $prev_or = $bill['rptsp_no'] ?? null;
    $prev_date = null;
    $prev_year = $bill['tax_year'] ?? '';
    $owner_id = $bill['owner_id'] ?? null;
    $remarks = 'Payment recorded.';

    $stmt->bind_param(
        "isssssdsss",
        $bill_id,
        $payor_name,
        $prev_or,
        $prev_date,
        $prev_year,
        $owner_id,
        $total,
        $newOR,
        $paid_date,
        $remarks
    );

    if ($stmt->execute()) {
        // Update status to "Paid"
        $mysqli->query("UPDATE tax_bills SET status='Paid' WHERE id=$bill_id");
        $insertedCount++;

        // Increment OR number
        $parts = explode('-', $newOR);
        $prefix = $parts[0];
        $number = intval($parts[1]) + 1;
        $newOR = $prefix . '-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    } else {
        $errorMessages[] = "Execution failed for Bill ID $bill_id: " . $stmt->error;
    }

    $stmt->close();
}

// --- Final response ---
if ($insertedCount > 0) {
    echo json_encode([
        'success' => true,
        'message' => "Processed $insertedCount payment(s) successfully." .
                     (!empty($errorMessages) ? " Some issues: " . implode('; ', $errorMessages) : '')
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No payments processed. ' . implode('; ', $errorMessages)
    ]);
}
?>
