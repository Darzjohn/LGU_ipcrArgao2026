<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';

if ($_SESSION['role'] !== 'assessor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$selected_ids = $_POST['selected_ids'] ?? [];

if (empty($selected_ids)) {
    echo json_encode(['success' => false, 'message' => 'No tax bills selected.']);
    exit;
}

// Prepare statement to fetch selected tax bills
$ids = implode(',', array_map('intval', $selected_ids));

$sql = "
SELECT tb.id AS bill_id, tb.tax_year, tb.rptsp_no, tb.status,
       p.id AS property_id, p.td_no, p.lot_no, p.location, p.barangay, p.classification, p.assessed_value,
       o.name AS owner_name,
       a.id AS assessment_id, a.basic_tax, a.sef_tax, a.adjustments
FROM tax_bills tb
JOIN assessments a ON a.id = tb.assessment_id
JOIN properties p ON p.id = a.property_id
LEFT JOIN owners o ON o.id = p.owner_id
WHERE tb.id IN ($ids)
";

$res = $mysqli->query($sql);

if (!$res || $res->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'No records found for selected bills.']);
    exit;
}

$insert = $mysqli->prepare("
INSERT INTO payments_list (
    assessment_id, tax_year, rptsp_no, td_no, owner_name, barangay, location,
    classification, assessed_value, basic_tax, sef_tax, adjustments,
    discount, penalty, total_due, processed_by, status
) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
");

if (!$insert) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $mysqli->error]);
    exit;
}

$today = new DateTime();
$curYear = (int)$today->format('Y');
$curMonth = (int)$today->format('n');
$processedBy = $_SESSION['name'] ?? $_SESSION['username'];
$status = 'unpaid';

while ($row = $res->fetch_assoc()) {
    $basic = (float)$row['basic_tax'];
    $sef = (float)$row['sef_tax'];
    $adjustments = (float)$row['adjustments'];
    $tax_year = (int)$row['tax_year'];

    // Discount
    $discount = 0;
    if ($tax_year == $curYear && $curMonth <= 3) {
        $discount = 0.10 * ($basic + $sef);
    }

    // Penalty (only if no discount)
    $penalty = 0;
    if ($discount == 0) {
        if ($tax_year < $curYear) {
            $months_due = ($curYear - $tax_year) * 12 + $curMonth;
        } else {
            $months_due = max(0, $curMonth);
        }
        $penalty = min(0.02 * $months_due * ($basic + $sef), 0.72 * ($basic + $sef));
    }

    $total_due = $basic + $sef + $adjustments - $discount + $penalty;

    $insert->bind_param(
        "iissssssddddddsss",
        $row['assessment_id'],
        $row['tax_year'],
        $row['rptsp_no'],
        $row['td_no'],
        $row['owner_name'],
        $row['barangay'],
        $row['location'],
        $row['classification'],
        $row['assessed_value'],
        $basic,
        $sef,
        $adjustments,
        $discount,
        $penalty,
        $total_due,
        $processedBy,
        $status
    );

    $insert->execute();
}

$insert->close();

echo json_encode(['success' => true, 'message' => 'Selected tax bills successfully added to payments list.']);
