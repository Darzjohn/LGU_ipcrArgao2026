<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedIds = $_POST['selected_ids'] ?? [];
    if (empty($selectedIds)) {
        echo json_encode(['success'=>false,'message'=>'No bills selected.']);
        exit;
    }

    $curYear = (int)date('Y');
    $curMonth = (int)date('n');
    $processed_by = $_SESSION['name'] ?? 'system';

    // Prepare select statement
    $stmtSelect = $mysqli->prepare("
        SELECT tb.id, tb.tax_year, tb.rptsp_no, tb.status,
               p.id AS property_id, p.td_no, p.lot_no, p.barangay, p.location, p.classification, p.assessed_value,
               o.name AS owner_name,
               a.basic_tax, a.sef_tax, a.adjustments
        FROM tax_bills tb
        JOIN assessments a ON a.id = tb.assessment_id
        JOIN properties p ON p.id = a.property_id
        LEFT JOIN owners o ON o.id = p.owner_id
        WHERE tb.id IN (" . implode(',', array_fill(0, count($selectedIds), '?')) . ")
    ");

    $types = str_repeat('i', count($selectedIds));
    $stmtSelect->bind_param($types, ...$selectedIds);
    $stmtSelect->execute();
    $result = $stmtSelect->get_result();

    // Prepare insert statement
    $insertStmt = $mysqli->prepare("
        INSERT INTO payments_list 
        (assessment_id, tax_year, rptsp_no, td_no, lot_no, owner_name, barangay, location, classification, assessed_value, basic_tax, sef_tax, adjustments, discount, penalty, total_due, status, processed_by, assessed_date) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    while ($row = $result->fetch_assoc()) {
        $basic_tax   = (float)$row['basic_tax'];
        $sef_tax     = (float)$row['sef_tax'];
        $adjustments = (float)$row['adjustments'];
        $assessed_value = (float)$row['assessed_value'];
        $tax_due = $basic_tax + $sef_tax;
        $tax_year = (int)$row['tax_year'];

        // --- Discount ---
        $discount = 0; 
        $discountPercent = 0;
        if ($tax_year == $curYear && $curMonth >= 1 && $curMonth <= 3) {
            $discount = 0.10 * $tax_due; 
            $discountPercent = 10;
        }
        if ($tax_year == $curYear + 1 && $curMonth >= 10 && $curMonth <= 12) {
            $discount = 0.20 * $tax_due; 
            $discountPercent = 20;
        }

        // --- Penalty ---
        $penalty = 0; 
        $penaltyPercent = 0;
        if ($discountPercent == 0) {
            $months_due = ($tax_year < $curYear) ? ($curYear - $tax_year) * 12 + $curMonth : max(0, $curMonth);
            $penalty = min(0.02 * $months_due * $tax_due, 0.72 * $tax_due);
            $penaltyPercent = min($months_due * 2, 72);
        }

        $total_due = $tax_due + $adjustments - $discount + $penalty;

        $insertStmt->bind_param(
            "iissssssssddddddss",
            $row['id'],          // assessment_id
            $row['tax_year'],    // tax_year
            $row['rptsp_no'],    // rptsp_no
            $row['td_no'],       // td_no
            $row['lot_no'],      // lot_no
            $row['owner_name'],  // owner_name
            $row['barangay'],    // barangay
            $row['location'],    // location
            $row['classification'], // classification
            $assessed_value,     // assessed_value
            $basic_tax,          // basic_tax
            $sef_tax,            // sef_tax
            $adjustments,        // adjustments
            $discount,           // discount
            $penalty,            // penalty
            $total_due,          // total_due
            $row['status'],      // status
            $processed_by        // processed_by
        );

        $insertStmt->execute();
    }

    $insertStmt->close();
    $stmtSelect->close();

    echo json_encode(['success'=>true,'message'=>'Selected bills transferred to Payments List.']);
}
