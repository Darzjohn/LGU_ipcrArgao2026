<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/session_check.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $payer_name = $conn->real_escape_string($_POST['payer_name']);
    $date_issued = $conn->real_escape_string($_POST['date_issued']);
    $ngas_codes = $_POST['ngas_code'] ?? [];
    $natures = $_POST['nature_of_collection'] ?? [];
    $amounts = $_POST['amount'] ?? [];

    if(empty($payer_name) || empty($date_issued) || empty($ngas_codes)){
        die("All fields are required.");
    }

    // Start transaction
    $conn->begin_transaction();
    try {
        // Insert into form51
        $conn->query("INSERT INTO form51 (payer_name, date_issued) VALUES ('$payer_name', '$date_issued')");
        $form51_id = $conn->insert_id;

        // Insert multiple payments
        $stmt = $conn->prepare("INSERT INTO form51_payments (form51_id, ngas_code, nature_of_collection, amount) VALUES (?, ?, ?, ?)");
        for($i=0; $i<count($ngas_codes); $i++){
            $code = $ngas_codes[$i];
            $nature = $natures[$i];
            $amount = floatval($amounts[$i]);
            $stmt->bind_param("issd", $form51_id, $code, $nature, $amount);
            $stmt->execute();
        }

        $conn->commit();
        header("Location: form51_list.php");
        exit;
    } catch(Exception $e){
        $conn->rollback();
        die("Error saving Form51: ".$e->getMessage());
    }
} else {
    die("Invalid request.");
}
