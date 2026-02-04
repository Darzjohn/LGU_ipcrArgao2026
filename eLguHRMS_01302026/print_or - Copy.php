<?php
require 'db.php';

// Get query parameters
$payor_name = $_GET['payor_name'] ?? '';
$date_paid = $_GET['date_paid'] ?? '';

if (!$payor_name || !$date_paid) {
    die("<div class='alert alert-danger'>Missing payor or date.</div>");
}

// Fetch all payments for this payor and date
$stmt = $mysqli->prepare("SELECT p.or_no, p.tax_year, p.basic_tax, p.sef_tax, p.discount, p.penalty, p.total_paid, 
                                 tb.td_no, o.name AS owner_name, pr.location, pr.barangay
                          FROM payments p
                          JOIN tax_bills tb ON tb.id = p.tax_bill_id
                          JOIN assessments a ON a.id = tb.assessment_id
                          JOIN properties pr ON pr.id = a.property_id
                          LEFT JOIN owners o ON o.id = pr.owner_id
                          WHERE p.payor_name = ? AND p.date_paid = ?");
$stmt->bind_param('ss', $payor_name, $date_paid);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    die("<div class='alert alert-danger'>No payments found for this payor and date.</div>");
}

// Assume all OR numbers are same for the batch
$or_no = $res->fetch_assoc()['or_no'];
$res->data_seek(0); // rewind

$grand_total = 0;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Official Receipt - <?= htmlspecialchars($payor_name) ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: center; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h2>Official Receipt (Real Property Tax)</h2>
    <p><strong>OR No:</strong> <?= htmlspecialchars($or_no) ?></p>
    <p><strong>Payor Name:</strong> <?= htmlspecialchars($payor_name) ?></p>
    <p><strong>Date Paid:</strong> <?= htmlspecialchars($date_paid) ?></p>

    <table>
        <thead>
            <tr>
                <th>TD No</th>
                <th>Owner</th>
                <th>Location</th>
                <th>Barangay</th>
                <th>Tax Year</th>
                <th>Basic Tax</th>
                <th>SEF</th>
                <th>Discount</th>
                <th>Penalty</th>
                <th>Total Paid</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $res->fetch_assoc()): 
                $grand_total += $row['total_paid'];
            ?>
            <tr>
                <td><?= htmlspecialchars($row['td_no']) ?></td>
                <td><?= htmlspecialchars($row['owner_name'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($row['location'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($row['barangay'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($row['tax_year']) ?></td>
                <td class="text-right"><?= number_format($row['basic_tax'],2) ?></td>
                <td class="text-right"><?= number_format($row['sef_tax'],2) ?></td>
                <td class="text-right"><?= number_format($row['discount'],2) ?></td>
                <td class="text-right"><?= number_format($row['penalty'],2) ?></td>
                <td class="text-right"><?= number_format($row['total_paid'],2) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="9" class="text-right">Grand Total:</th>
                <th class="text-right"><?= number_format($grand_total,2) ?></th>
            </tr>
        </tfoot>
    </table>

    <p style="margin-top:30px;">----------------------------<br>Authorized Signature</p>
    <script>
        window.onload = function() {
            window.print(); // auto print
        }
    </script>
</body>
</html>
