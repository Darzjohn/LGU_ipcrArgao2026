<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $form51_id = $_POST['form51_id'];
    $grand_total = isset($_POST['grand_total']) ? floatval($_POST['grand_total']) : 0;
    $ngas_codes = $_POST['ngas_code'];
    $natures = $_POST['nature_of_collection'];
    $amounts = $_POST['amount'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Update grand total in form51 table
        $stmt = $conn->prepare("UPDATE form51 SET grand_total = ? WHERE id = ?");
        $stmt->bind_param("di", $grand_total, $form51_id);
        $stmt->execute();

        // Clear existing payments
        $conn->query("DELETE FROM form51_payments WHERE form51_id = $form51_id");

        // Insert updated payments
        $stmt = $conn->prepare("INSERT INTO form51_payments (form51_id, ngas_code, nature_of_collection, amount) VALUES (?, ?, ?, ?)");
        for ($i=0; $i < count($ngas_codes); $i++) {
            $stmt->bind_param("issd", $form51_id, $ngas_codes[$i], $natures[$i], $amounts[$i]);
            $stmt->execute();
        }

        $conn->commit();
        header("Location: edit_form51.php?id=$form51_id&success=1");
    } catch (Exception $e) {
        $conn->rollback();
        die("Error updating Form51: ".$e->getMessage());
    }
}
