<?php
// natb_print.php
require 'db.php';
include 'header.php';

$bill_id = isset($_GET['bill_id']) ? (int)$_GET['bill_id'] : 0;
$stmt = $mysqli->prepare("
  SELECT tb.*, a.*, p.td_no, p.location, o.name as owner_name
  FROM tax_bills tb
  JOIN assessments a ON a.id=tb.assessment_id
  JOIN properties p ON p.id=a.property_id
  LEFT JOIN owners o ON o.id=p.owner_id
  WHERE tb.id=?");
$stmt->bind_param("i",$bill_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
if (!$data) die("Bill not found");
$total = $data['total_amount'];
// compute delinquency penalty if after due_date
$penalty = 0.00;
$today = new DateTime();
$due = new DateTime($data['due_date']);
if ($today > $due && $data['paid_amount'] < $total) {
    $diff = $due->diff($today);
    $months = $diff->m + ($diff->y * 12);
    // fraction counts as month per doc: if days>0, add 1
    if ($diff->d > 0) $months++;
    if ($months < 1) $months = 1;
    $penalty = round($total * 0.02 * $months, 2);
}
?>
<!doctype html><html><head><meta charset="utf-8"><title>NATB - <?=htmlspecialchars($data['bill_no'])?></title>
<style>
  body{font-family: Arial, sans-serif; max-width:800px; margin:20px;}
  .header{display:flex; justify-content:space-between; align-items:center;}
  table{width:100%; border-collapse:collapse;}
  td, th{padding:6px; border:1px solid #ccc;}
</style>
</head>
<body>
  <div class="header">
    <div>
      <h2>Municipality of Argao</h2>
      <p>OFFICE OF THE MUNICIPAL ASSESSOR</p>
    </div>
    <div>
      <strong>NOTICE OF ASSESSMENT / TAX BILL</strong><br>
      Bill No: <?=htmlspecialchars($data['bill_no'])?><br>
      Issued: <?=htmlspecialchars($data['issuance_date'])?>
    </div>
  </div>

  <h3>Taxpayer: <?=htmlspecialchars($data['owner_name'])?></h3>
  <p>TD No: <?=htmlspecialchars($data['td_no'])?> | Location: <?=htmlspecialchars($data['location'])?></p>

  <table>
    <tr><th>Year</th><th>Assessed Value</th><th>Basic Tax</th><th>SEF (10%)</th><th>Adjustments</th><th>Total</th></tr>
    <tr>
      <td><?=htmlspecialchars($data['year'])?></td>
      <td><?=number_format($data['assessed_value'],2)?></td>
      <td><?=number_format($data['basic_tax'],2)?></td>
      <td><?=number_format($data['sef_tax'],2)?></td>
      <td><?=number_format($data['adjustments'],2)?></td>
      <td><?=number_format($data['total_amount'],2)?></td>
    </tr>
  </table>

  <p>Due Date: <?=htmlspecialchars($data['due_date'])?></p>
  <?php if ($penalty>0): ?>
    <p style="color:red">Penalty due to late payment (2%/month): <?=number_format($penalty,2)?></p>
  <?php endif; ?>

  <p>Paid: <?=number_format($data['paid_amount'],2)?></p>
  <p><strong>Amount Due (incl. penalty): <?=number_format($data['total_amount'] + $penalty - $data['paid_amount'],2)?></strong></p>

  <hr>
  <p>Receive by: ________________________</p>
  <p>Prepared by: ________________________</p>
</body></html>
<?php include 'footer.php'; ?>
