<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assessment_id = (int)($_POST['id'] ?? 0);
    $assessed_value = (float)($_POST['assessed_value'] ?? 0);
    $basic_tax_rate = (float)($_POST['basic_tax_rate'] ?? 0.01);
    $adjustments = (float)($_POST['adjustments'] ?? 0);

    // Recalculate taxes
    $basic_tax = $assessed_value * $basic_tax_rate;
    $sef_tax = $assessed_value * 0.01;
    $total_tax = $basic_tax + $sef_tax + $adjustments;

    // Update assessment
    q("UPDATE assessments SET assessed_value=?, basic_tax_rate=?, basic_tax=?, sef_tax=?, adjustments=? WHERE id=? AND status='draft'",
      "dddddi", [$assessed_value, $basic_tax_rate, $basic_tax, $sef_tax, $adjustments, $assessment_id]);

    echo json_encode([
        'status' => 'success',
        'basic_tax' => number_format($basic_tax, 2),
        'sef_tax' => number_format($sef_tax, 2),
        'total_tax' => number_format($total_tax, 2)
    ]);
}
?>
