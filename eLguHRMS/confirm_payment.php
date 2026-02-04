<?php
require 'db.php';

// ---------------- INPUT VALIDATION ----------------
$selected_bills = $_POST['selected_bills'] ?? '';
if(empty($selected_bills)){
    die("❌ No bills selected.");
}
$bill_ids = array_map('intval', explode(',', $selected_bills));

// Manual payment inputs
$or_number       = $_POST['or_number'] ?? '';
$payor_name      = $_POST['payor_name'] ?? '';
$prev_receipt_no = $_POST['prev_receipt_no'] ?? '';
$prev_paid       = (float)($_POST['prev_paid'] ?? 0);
$date_paid       = $_POST['date_paid'] ?? date('Y-m-d');
$ownership_no    = $_POST['ownership_no'] ?? '';

if(!$or_number || !$payor_name || !$date_paid){
    die("❌ OR Number, Payor Name, and Date Paid are required.");
}

// ---------------- FETCH BILLS ----------------
$ids_str = implode(',', $bill_ids);
$sql = "
    SELECT tb.id AS bill_id, tb.tax_year, tb.rptsp_no,
           a.basic_tax, a.sef_tax, a.adjustments,
           (a.basic_tax + a.sef_tax + a.adjustments) AS total_due,
           p.id AS property_id
    FROM tax_bills tb
    JOIN assessments a ON a.id = tb.assessment_id
    JOIN properties p ON p.id = a.property_id
    WHERE tb.id IN ($ids_str)
";
$result = $mysqli->query($sql);
if(!$result){
    die("SQL Error: " . $mysqli->error);
}

$total_payment = 0;
$payments_data = [];
while($row = $result->fetch_assoc()){
    $total_payment += $row['total_due'];
    $payments_data[] = $row;
}

// ---------------- INSERT PAYMENTS ----------------
$insert_stmt = $mysqli->prepare("
    INSERT INTO payments 
    (bill_id, property_id, or_number, payor_name, prev_receipt_no, prev_paid, amount_paid, date_paid, ownership_no)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");

if(!$insert_stmt){
    die("Prepare Error: " . $mysqli->error);
}

foreach($payments_data as $p){
    $bill_id = $p['bill_id'];
    $property_id = $p['property_id'];
    $amount_paid = $p['total_due'];

    $insert_stmt->bind_param(
        "iisssddss",
        $bill_id,
        $property_id,
        $or_number,
        $payor_name,
        $prev_receipt_no,
        $prev_paid,
        $amount_paid,
        $date_paid,
        $ownership_no
    );
    $insert_stmt->execute();

    // ---------------- AUDIT LOG ----------------
    $audit_stmt = $mysqli->prepare("
        INSERT INTO payment_audit
        (bill_id, action, user_name, timestamp)
        VALUES (?, ?, ?, NOW())
    ");
    $action = "Payment confirmed: ₱" . number_format($amount_paid,2);
    $user_name = $payor_name;
    $audit_stmt->bind_param("iss", $bill_id, $action, $user_name);
    $audit_stmt->execute();
    $audit_stmt->close();

    // ---------------- UPDATE BILL STATUS ----------------
    $mysqli->query("UPDATE tax_bills SET status='Paid' WHERE id=$bill_id");
}

$insert_stmt->close();

echo "<div class='alert alert-success'>✅ Payment confirmed for " . count($payments_data) . " bill(s). Total: ₱" . number_format($total_payment,2) . "</div>";
echo "<a href='tax_billsall.php' class='btn btn-primary'>Back to Tax Bills</a>";
?>
