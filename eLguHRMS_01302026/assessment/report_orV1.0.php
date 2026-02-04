<?php
require_once __DIR__ . '/../config/db.php';

$or_no = $_GET['or_no'] ?? '';
if(!$or_no){
    die("OR Number required.");
}

$res = $mysqli->query("SELECT * FROM collections WHERE or_no='".$mysqli->real_escape_string($or_no)."'");
if(!$res || $res->num_rows==0) die("No records found for OR: ".$or_no);
$payments = [];
while($row=$res->fetch_assoc()) $payments[]=$row;
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Official Receipt - <?=htmlspecialchars($or_no)?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container">
  <h2 class="text-center mb-4">Official Receipt</h2>
  <h5>OR Number: <?=htmlspecialchars($or_no)?></h5>
  <h6>Payor: <?=htmlspecialchars($payments[0]['payor_name'])?></h6>
  <h6>Payment Date: <?=htmlspecialchars($payments[0]['payment_date'])?></h6>

  <table class="table table-bordered mt-3">
    <thead>
      <tr>
        <th>RPTSP</th><th>TD No</th><th>Lot No</th><th>Owner</th><th>Tax Year</th><th>Total Due</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($payments as $p): ?>
      <tr>
        <td><?=htmlspecialchars($p['rptsp_no'])?></td>
        <td><?=htmlspecialchars($p['td_no'])?></td>
        <td><?=htmlspecialchars($p['lot_no'])?></td>
        <td><?=htmlspecialchars($p['owner_name'])?></td>
        <td><?=htmlspecialchars($p['tax_year'])?></td>
        <td>₱<?=number_format($p['total_due'],2)?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <p>Total Amount Paid: ₱<?=number_format(array_sum(array_column($payments,'total_due')),2)?></p>
  <p>Collected By: <?=htmlspecialchars($payments[0]['processed_by'])?></p>
</div>
</body>
</html>
