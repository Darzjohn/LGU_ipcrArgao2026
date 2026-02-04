<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';

$id = intval($_GET['id'] ?? 0);
$res = $mysqli->query("SELECT * FROM ctc_individual WHERE id=$id");
$ctc = $res->fetch_assoc();
if(!$ctc) die("Record not found.");
?>
<!DOCTYPE html>
<html>
<head>
<title>Print CTC - <?=$ctc['ctc_no']?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<style>
body{font-family:'Arial';margin:30px;}
.ctc-border{border:2px solid #000;padding:20px;}
h3{text-align:center;text-decoration:underline;}
.label{font-weight:bold;}
@media print {.no-print{display:none;}}
</style>
</head>
<body>
<div class="no-print mb-3 text-end">
  <button class="btn btn-primary" onclick="window.print()">🖨️ Print</button>
</div>

<div class="ctc-border">
  <h3>COMMUNITY TAX CERTIFICATE (INDIVIDUAL)</h3>
  <p><b>CTC No:</b> <?=$ctc['ctc_no']?> &nbsp;&nbsp;&nbsp; <b>Year:</b> <?=$ctc['year']?></p>
  <p><b>Place of Issue:</b> <?=$ctc['place_of_issue']?> &nbsp;&nbsp;&nbsp; <b>Date Issued:</b> <?=$ctc['date_issued']?></p>
  <hr>
  <p><b>Name:</b> <?=$ctc['surname']?>, <?=$ctc['firstname']?> <?=$ctc['middlename']?></p>
  <p><b>Address:</b> <?=$ctc['address']?></p>
  <p><b>Citizenship:</b> <?=$ctc['citizenship']?> &nbsp;&nbsp; <b>Civil Status:</b> <?=$ctc['civil_status']?></p>
  <p><b>ICR No:</b> <?=$ctc['icr_no']?> &nbsp;&nbsp; <b>Place of Birth:</b> <?=$ctc['place_of_birth']?></p>
  <p><b>Profession:</b> <?=$ctc['profession']?></p>
  <hr>
  <p><b>Gross Receipts:</b> ₱<?=number_format($ctc['gross_receipts'],2)?></p>
  <p><b>Salaries:</b> ₱<?=number_format($ctc['salaries'],2)?></p>
  <p><b>Income from Real Property:</b> ₱<?=number_format($ctc['real_property_income'],2)?></p>
  <p><b>Total Due:</b> ₱<?=number_format($ctc['total_due'],2)?></p>
  <p><b>Interest:</b> ₱<?=number_format($ctc['interest'],2)?></p>
  <p><b>Total Amount Paid:</b> ₱<?=number_format($ctc['total_paid'],2)?></p>
  <p><b>Amount in Words:</b> <?=$ctc['amount_words']?></p>
  <hr>
  <p><b>Date of Payment:</b> <?=$ctc['dop']?> &nbsp;&nbsp;&nbsp; <b>Municipal Treasurer:</b> <?=$ctc['treasurer']?></p>
  <br><br>
  <p class="text-center">------------------------------<br>Right Thumb Mark</p>
</div>
</body>
</html>
