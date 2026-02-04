<?php
require 'db.php';

$payment_id = $_GET['payment_id'] ?? 0;
$payment_id = (int)$payment_id;

if (!$payment_id) {
    echo "<div class='alert alert-danger'>Invalid payment ID.</div>";
    exit;
}

// Fetch payment details
$res = $mysqli->query("
    SELECT pay.*, p.td_no, p.lot_no, p.location, p.barangay, p.classification,
           o.name AS owner_name
    FROM payments pay
    JOIN assessments a ON a.id = pay.assessment_id
    JOIN properties p ON p.id = pay.property_id
    LEFT JOIN owners o ON o.id = p.owner_id
    WHERE pay.id = $payment_id
");

if (!$res || $res->num_rows === 0) {
    echo "<div class='alert alert-danger'>Payment not found.</div>";
    exit;
}

$payment = $res->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Official Receipt</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid black; }
        th, td { padding: 8px; text-align: left; }
        .total { font-weight: bold; }
        .center { text-align: center; }
        @media print { button { display: none; } }
    </style>
</head>
<body>

<h2>Official Receipt - Real Property Tax</h2>

<table>
    <tr>
        <th>OR No</th>
        <td><?= htmlspecialchars($payment['or_no']) ?></td>
        <th>Date Paid</th>
        <td><?= htmlspecialchars($payment['date_paid']) ?></td>
    </tr>
    <tr>
        <th>Payor Name</th>
        <td colspan="3"><?= htmlspecialchars($payment['payor_name']) ?></td>
    </tr>
    <tr>
        <th>Property TD No</th>
        <td><?= htmlspecialchars($payment['td_no']) ?></td>
        <th>Lot No</th>
        <td><?= htmlspecialchars($payment['lot_no']) ?></td>
    </tr>
    <tr>
        <th>Location</th>
        <td><?= htmlspecialchars($payment['location']) ?></td>
        <th>Barangay</th>
        <td><?= htmlspecialchars($payment['barangay']) ?></td>
    </tr>
    <tr>
        <th>Classification</th>
        <td><?= htmlspecialchars($payment['classification']) ?></td>
        <th>Tax Year</th>
        <td><?= $payment['tax_year'] ?></td>
    </tr>
</table>

<table>
    <thead>
        <tr>
            <th>Basic Tax</th>
            <th>SEF</th>
            <th>Discount</th>
            <th>Penalty</th>
            <th>Total Paid</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>₱<?= number_format($payment['basic_tax'],2) ?></td>
            <td>₱<?= number_format($payment['sef_tax'],2) ?></td>
            <td>₱<?= number_format($payment['discount'],2) ?></td>
            <td>₱<?= number_format($payment['penalty'],2) ?></td>
            <td class="total">₱<?= number_format($payment['total_paid'],2) ?></td>
        </tr>
    </tbody>
</table>

<div class="center" style="margin-top:50px;">
    <p>Thank you for your payment!</p>
    <button onclick="window.print()">Print Receipt</button>
</div>

</body>
</html>
