<?php
require 'db.php';
include 'header.php';

$or_no = $_GET['or_no'] ?? '';
if (!$or_no) {
    echo "<div class='alert alert-danger'>OR Number is required.</div>";
    exit;
}

// Fetch payments for this OR
$stmt = $mysqli->prepare("
    SELECT p.or_no, p.payor_name, p.date_paid, p.amount,
           tb.tax_year, a.basic_tax, a.sef_tax, a.adjustments,
           IFNULL(d.amount,0) AS discount,
           IFNULL(pe.amount,0) AS penalty,
           pr.td_no, pr.lot_no, pr.location, pr.barangay, pr.classification,
           o.name AS owner_name
    FROM payments p
    JOIN tax_bills tb ON tb.id = p.bill_id
    JOIN assessments a ON a.id = tb.assessment_id
    JOIN properties pr ON pr.id = a.property_id
    LEFT JOIN owners o ON o.id = pr.owner_id
    LEFT JOIN discounts d ON d.bill_id = tb.id
    LEFT JOIN penalties pe ON pe.bill_id = tb.id
    WHERE p.or_no = ?
");
$stmt->bind_param("s", $or_no);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    echo "<div class='alert alert-warning'>No payment found for OR No: $or_no</div>";
    exit;
}

// Fetch first row for OR header info
$firstRow = $result->fetch_assoc();
?>

<h2>Official Receipt</h2>
<div class="mb-3">
    <strong>OR No:</strong> <?=htmlspecialchars($firstRow['or_no'])?><br>
    <strong>Payor Name:</strong> <?=htmlspecialchars($firstRow['payor_name'])?><br>
    <strong>Date Paid:</strong> <?=htmlspecialchars($firstRow['date_paid'])?><br>
</div>

<table class="table table-bordered">
    <thead class="table-dark">
        <tr>
            <th>Property TD No</th>
            <th>Owner</th>
            <th>Year</th>
            <th>Basic Tax</th>
            <th>SEF</th>
            <th>Adjustments</th>
            <th>Discount</th>
            <th>Penalty</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $totalPaid = 0;
        $result->data_seek(0); // Reset pointer to start
        while($row = $result->fetch_assoc()):
            $total = $row['basic_tax'] + $row['sef_tax'] + $row['adjustments'] - $row['discount'] + $row['penalty'];
            $totalPaid += $total;
        ?>
        <tr>
            <td><?=htmlspecialchars($row['td_no'])?></td>
            <td><?=htmlspecialchars($row['owner_name'])?></td>
            <td><?=htmlspecialchars($row['tax_year'])?></td>
            <td>₱<?=number_format($row['basic_tax'],2)?></td>
            <td>₱<?=number_format($row['sef_tax'],2)?></td>
            <td>₱<?=number_format($row['adjustments'],2)?></td>
            <td>₱<?=number_format($row['discount'],2)?></td>
            <td>₱<?=number_format($row['penalty'],2)?></td>
            <td>₱<?=number_format($total,2)?></td>
        </tr>
        <?php endwhile; ?>
        <tr class="table-secondary">
            <td colspan="8" class="text-end"><strong>Total Paid:</strong></td>
            <td><strong>₱<?=number_format($totalPaid,2)?></strong></td>
        </tr>
    </tbody>
</table>

<div class="mt-3">
    <button onclick="window.print()" class="btn btn-primary">Print OR</button>
</div>

<?php include 'footer.php'; ?>
