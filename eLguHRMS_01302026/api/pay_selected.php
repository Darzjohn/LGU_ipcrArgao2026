<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (empty($_POST['selected_ids']) || !is_array($_POST['selected_ids'])) {
    echo json_encode(['success' => false, 'message' => 'No tax bills selected.']);
    exit;
}

$selected_ids = array_map('intval', $_POST['selected_ids']);

// --- Get the last OR number ---
$orResult = $mysqli->query("SELECT or_no FROM payments_list ORDER BY id DESC LIMIT 1");
if ($orResult && $orResult->num_rows > 0) {
    $lastOR = $orResult->fetch_assoc()['or_no'];
    $lastParts = explode('-', $lastOR);
    $prefix = $lastParts[0];
    $number = isset($lastParts[1]) ? intval($lastParts[1]) + 1 : 1;
    $newOR = $prefix . '-' . str_pad($number, 6, '0', STR_PAD_LEFT);
} else {
    $newOR = date('Y') . '-000001';
}

$paid_date = date('Y-m-d H:i:s');
$insertedCount = 0;
$errorCount = 0;
$errorMessages = [];

foreach ($selected_ids as $bill_id) {

    $sql = "
        SELECT tb.*, a.id AS assessment_id,
               a.basic_tax, a.sef_tax, a.adjustments,
               p.id AS property_id, p.td_no, p.location, p.barangay, p.classification, p.assessed_value,
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
        $errorCount++;
        continue;
    }

    $bill = $res->fetch_assoc();
    $basic = floatval($bill['basic_tax']);
    $sef = floatval($bill['sef_tax']);
    $adjust = floatval($bill['adjustments']);
    $total = $basic + $sef + $adjust;

    $insertSQL = "INSERT INTO payments_list (
        or_no, previous_or_no, previous_date_paid, previous_year,
        assessment_id, tax_year, rptsp_no, td_no, owner_name, barangay,
        location, classification, basic_tax, sef_tax, adjustments, discount,
        penalty, total_due, payment_date, status, processed_by, owner,
        payor_name, year, owner_id, amount, total_amount_paid
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $mysqli->prepare($insertSQL);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $mysqli->error]);
        exit;
    }

    $prev_or = $bill['rptsp_no'] ?? null;
    $prev_date = null;
    $prev_year = $bill['tax_year'];
    $discount = 0.00;
    $penalty = 0.00;
    $total_due = $total;
    $status = 'Paid';
    $processed_by = 'System';
    $owner = $bill['owner_name'];
    $payor_name = $bill['owner_name'];
    $year = $bill['tax_year'];
    $amount = $total;
    $total_amount_paid = $total;

    // Prepare parameters
    $params = [
        $newOR,
        $prev_or,
        $prev_date,
        $prev_year,
        $bill['assessment_id'],
        $bill['tax_year'],
        $bill['rptsp_no'],
        $bill['td_no'],
        $bill['owner_name'],
        $bill['barangay'],
        $bill['location'],
        $bill['classification'],
        $bill['basic_tax'],
        $bill['sef_tax'],
        $bill['adjustments'],
        $discount,
        $penalty,
        $total_due,
        $paid_date,
        $status,
        $processed_by,
        $owner,
        $payor_name,
        $year,
        $bill['owner_id'],
        $amount,
        $total_amount_paid
    ];

    // Auto-detect parameter types
    $types = '';
    foreach ($params as $p) {
        if (is_int($p)) $types .= 'i';
        elseif (is_float($p) || is_double($p)) $types .= 'd';
        else $types .= 's';
    }

    // Bind all dynamically
    $stmt->bind_param($types, ...$params);

    if (!$stmt->execute()) {
        $errorMessages[] = "Bill ID $bill_id error: " . $stmt->error;
        $errorCount++;
    } else {
        $mysqli->query("UPDATE tax_bills SET status='Paid' WHERE id=$bill_id");
        $insertedCount++;
    }

    $stmt->close();

    // Increment OR number
    $parts = explode('-', $newOR);
    $prefix = $parts[0];
    $number = intval($parts[1]) + 1;
    $newOR = $prefix . '-' . str_pad($number, 6, '0', STR_PAD_LEFT);
}

// Return JSON response
if ($insertedCount > 0) {
    echo json_encode([
        'success' => true,
        'message' => "Processed $insertedCount payment(s). Next OR: $newOR" .
                     ($errorCount ? " ($errorCount error(s): " . implode('; ', $errorMessages) . ")" : "")
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No payments processed. ' . implode('; ', $errorMessages)
    ]);
}
?>
