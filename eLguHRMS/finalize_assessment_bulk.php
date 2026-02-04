<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assessment_ids']) && is_array($_POST['assessment_ids'])) {
    $ids = $_POST['assessment_ids'];

    $mysqli->begin_transaction();

    try {
        $stmtUpdate = $mysqli->prepare("UPDATE assessments SET status='finalized' WHERE id=? AND status='draft'");
        $stmtInsert = $mysqli->prepare("INSERT INTO tax_bills (assessment_id, property_id, tax_year, assessed_value, basic_tax, sef_tax, adjustments) VALUES (?,?,?,?,?,?,?)");

        foreach ($ids as $id) {
            $id = (int)$id;

            // Get assessment details
            $res = $mysqli->query("SELECT * FROM assessments WHERE id=$id AND status='draft'");
            if ($res->num_rows === 0) continue;
            $row = $res->fetch_assoc();

            // Update assessment to finalized
            $stmtUpdate->bind_param("i", $id);
            $stmtUpdate->execute();

            // Insert into tax_bills
            $stmtInsert->bind_param(
                "iiidddd",
                $row['id'],
                $row['property_id'],
                $row['tax_year'],
                $row['assessed_value'],
                $row['basic_tax'],
                $row['sef_tax'],
                $row['adjustments']
            );
            $stmtInsert->execute();
        }

        $mysqli->commit();

        // Redirect to NATB report
        header("Location: report_taxbill.php?finalized=1");
        exit;

    } catch (Exception $e) {
        $mysqli->rollback();
        die("Error finalizing assessments: " . $e->getMessage());
    }
} else {
    header("Location: assessments.php");
    exit;
}
