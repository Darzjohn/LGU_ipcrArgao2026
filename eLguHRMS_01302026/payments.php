<?php
require 'db.php';
include 'header.php';

$selectedBills = $_POST['selected_years'] ?? [];

if(!$selectedBills) {
    echo "<div class='alert alert-warning'>No bills selected. Please select bills first.</div>";
    exit;
}

// Fetch selected bills
$ids = implode(',', array_map('intval', $selectedBills));
$res = $mysqli->query("
    SELECT tb.id AS bill_id, tb.tax_year, tb.rptsp_no,
           p.id AS property_id, p.td_no, p.lot_no, p.location, p.barangay, p.classification,
           o.name AS owner_name,
           a.basic_tax, a.sef_tax, a.adjustments
    FROM tax_bills tb
    JOIN assessments a ON a.id = tb.assessment_id
    JOIN properties p ON p.id = a.property_id
    LEFT JOIN owners o ON o.id = p.owner_id
    WHERE tb.id IN ($ids)
    ORDER BY tb.id ASC
");

// Current date info
$today = new DateTime();
$curYear = (int)$today->format('Y');
$curMonth = (int)$today->format('n');
$todayStr = $today->format('Y-m-d');
?>

<h2 class="mb-4">Confirm Payment</h2>

<form method="post" action="process_payment.php">
<input type="hidden" name="selected_bills" value="<?=htmlspecialchars($ids)?>">

<div class="card">
  <div class="card-header bg-secondary text-white">Payment Details</div>
  <div class="card-body table-responsive">
    <table class="table table-bordered table-hover align-middle">
      <thead class="table-dark">
        <tr>
          <th>Property ID</th>
          <th>Owner</th>
          <th>TD No</th>
          <th>Lot No</th>
          <th>Classification</th>
          <th>Barangay</th>
          <th>Location</th>
          <th>Tax Year</th>
          <th>Basic Tax</th>
          <th>SEF Tax</th>
          <th>Adjustments</th>
          <th>Discount</th>
          <th>Penalty</th>
          <th>Total Due</th>
          <th>OR Number</th>
          <th>Payor Name</th>
          <th>Previous OR No</th>
          <th>Previous Paid</th>
          <th>Date Paid Today</th>
        </tr>
      </thead>
      <tbody>
      <?php if($res && $res->num_rows > 0): ?>
        <?php while($row = $res->fetch_assoc()): ?>
          <?php
          $basic_tax = (float)$row['basic_tax'];
          $sef_tax = (float)$row['sef_tax'];
          $adjustments = (float)$row['adjustments'];

          $discount = 0;
          $penalty = 0;

          $tax_year = (int)$row['tax_year'];

          // Discount
          if($tax_year == $curYear && $curMonth <= 3) {
              $discount = 0.10 * ($basic_tax + $sef_tax);
          }

          // Penalty
          if($tax_year == $curYear) {
              $months_due = max(0, $curMonth);
          } elseif($tax_year < $curYear) {
              $months_due = ($curYear - $tax_year) * 12+ $curMonth;
          } else {
              $months_due = 0;
          }
          $penalty = min(0.02 * $months_due * ($basic_tax + $sef_tax), 0.72 * ($basic_tax + $sef_tax));

          $total_due = $basic_tax + $sef_tax + $adjustments - $discount + $penalty;
          ?>
          <tr>
            <td><?=$row['property_id']?></td>
            <td><?=htmlspecialchars($row['owner_name'])?></td>
            <td><?=htmlspecialchars($row['td_no'])?></td>
            <td><?=htmlspecialchars($row['lot_no'])?></td>
            <td><?=htmlspecialchars($row['classification'])?></td>
            <td><?=htmlspecialchars($row['barangay'])?></td>
            <td><?=htmlspecialchars($row['location'])?></td>
            <td><?=$tax_year?></td>
            <td>₱<?=number_format($basic_tax,2)?></td>
            <td>₱<?=number_format($sef_tax,2)?></td>
            <td>₱<?=number_format($adjustments,2)?></td>
            <td>₱<?=number_format($discount,2)?></td>
            <td>₱<?=number_format($penalty,2)?></td>
            <td>₱<?=number_format($total_due,2)?></td>
            <td><input type="text" name="or_number[<?=$row['bill_id']?>]" class="form-control" required></td>
            <td><input type="text" name="payor_name[<?=$row['bill_id']?>]" class="form-control" required></td>
            <td><input type="text" name="prev_or[<?=$row['bill_id']?>]" class="form-control"></td>
            <td><input type="text" name="prev_paid[<?=$row['bill_id']?>]" class="form-control"></td>
            <td><input type="date" name="date_paid[<?=$row['bill_id']?>]" value="<?=$todayStr?>" class="form-control" required></td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="19" class="text-center">No bills selected.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
    <div class="d-grid">
      <button type="submit" class="btn btn-success btn-lg">Confirm Payment</button>
    </div>
  </div>
</div>
</form>

<?php include 'footer.php'; ?>
