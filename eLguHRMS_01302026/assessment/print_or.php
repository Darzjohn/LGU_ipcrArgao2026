<?php
// print_or.php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';

$or_no = $_GET['or_no'] ?? '';
if (!$or_no) die('OR required.');

$stmt = $mysqli->prepare("SELECT * FROM collections WHERE or_no = ? ORDER BY id");
$stmt->bind_param("s", $or_no);
$stmt->execute();
$res = $stmt->get_result();
$rows = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (!$rows) die('OR not found.');

// gather totals
$totalPaid = 0;
$first = $rows[0];

// fetch treasurer signatory
$treasurerName = '';
$s = $mysqli->prepare("SELECT name FROM signatories WHERE LOWER(position) = 'treasurer' LIMIT 1");
if ($s) { $s->execute(); $sr = $s->get_result()->fetch_assoc(); if ($sr) $treasurerName = $sr['name']; $s->close(); }

$collectingOfficer = $_SESSION['name'] ?? $_SESSION['username'] ?? 'Collector';

foreach ($rows as $r) $totalPaid += floatval($r['total_amount_paid'] ?? 0);

function amount_in_words($number) {
    // simplistic english words for amounts — if you need full feature, use a library.
    return ucwords(number_format($number,2));
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Official Receipt <?=htmlspecialchars($or_no)?></title>
  <style>
    body { font-family: Arial, sans-serif; font-size:12px; margin:20px; color:#000;}
    .center { text-align:center; }
    .bold { font-weight:bold; }
    .kv { width:150px; display:inline-block; }
    .field { border-bottom:1px dotted #000; padding:2px 4px; min-width:200px; display:inline-block;}
    table { width:100%; border-collapse:collapse; margin-top:10px; }
    td,th { padding:6px; vertical-align:top; }
    .right { text-align:right; }
    .underline { border-bottom:1px solid #000; display:inline-block; min-width:120px; padding:2px;}
    .green-percent { color:green; font-weight:bold;}
    .red-percent { color:red; font-weight:bold;}
    @media print { .no-print { display:none; } }
  </style>
</head>
<body>
  <div class="center">
    <div>Republic of the Philippines</div>
    <div>Province of Cebu</div>
    <div>Municipality of Argao</div>
    <h2>OFFICIAL RECEIPT</h2>
    <div class="bold">OR No: <?=htmlspecialchars($or_no)?> &nbsp; &nbsp; Date: <?=htmlspecialchars(date('Y-m-d H:i', strtotime($first['assessed_date'] ?? 'now'))) ?></div>
  </div>

  <div style="margin-top:8px;">
    <div><span class="kv bold">Owner:</span> <span class="field"><?=htmlspecialchars($first['owner_name'])?></span></div>
    <div><span class="kv bold">TD No:</span> <span class="field"><?=htmlspecialchars($first['td_no'])?></span> <span class="kv bold">Lot No:</span> <span class="field"><?=htmlspecialchars($first['lot_no'])?></span></div>
    <div><span class="kv bold">Assessed Value:</span> <span class="field">₱<?=number_format($first['assessed_value'],2)?></span></div>
    <div><span class="kv bold">Tax Year:</span> <span class="field"><?=htmlspecialchars($first['tax_year'])?></span></div>
  </div>

  <table border="1">
    <thead>
      <tr style="background:#f0f0f0">
        <th>RPTSP</th>
        <th>Tax Year</th>
        <th class="right">Basic Tax</th>
        <th class="right">SEF Tax</th>
        <th class="right">Discount</th>
        <th class="right">Penalty</th>
        <th class="right">Total Due</th>
        <th class="right">Amount Paid</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach($rows as $r): ?>
      <tr>
        <td><?=htmlspecialchars($r['rptsp_no'])?></td>
        <td class="right"><?=htmlspecialchars($r['tax_year'])?></td>
        <td class="right">₱<?=number_format($r['basic_tax'],2)?></td>
        <td class="right">₱<?=number_format($r['sef_tax'],2)?></td>
        <td class="right">₱<?=number_format($r['discount'],2)?> <?php if($r['discount']>0): ?><span class="green-percent">(<?=round(($r['discount'] / max(1,$r['tax_due']))*100)?>%)</span><?php endif;?></td>
        <td class="right">₱<?=number_format($r['penalty'],2)?> <?php if($r['penalty']>0): ?><span class="red-percent">(<?=round(($r['penalty'] / max(1,$r['tax_due']))*100)?>%)</span><?php endif;?></td>
        <td class="right">₱<?=number_format($r['total_due'],2)?></td>
        <td class="right">₱<?=number_format($r['total_amount_paid'],2)?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="7" class="right bold">TOTAL AMOUNT PAID:</td>
        <td class="right bold">₱<?=number_format($totalPaid,2)?></td>
      </tr>
    </tfoot>
  </table>

  <div style="margin-top:10px;">
    <div><span class="kv bold">Total Amount in Words:</span> <span class="field"><?=htmlspecialchars(amount_in_words($totalPaid))?></span></div>
    <div style="margin-top:8px;">
      <div style="width:50%; float:left; text-align:center;">
        <div>Prepared by:</div>
        <div style="height:50px;"></div>
        <div class="bold"><?=htmlspecialchars($collectingOfficer)?></div>
        <div>Collecting Officer</div>
      </div>
      <div style="width:50%; float:right; text-align:center;">
        <div>Received by:</div>
        <div style="height:50px;"></div>
        <div class="bold"><?=htmlspecialchars($treasurerName ?: 'Treasurer')?></div>
        <div>Treasurer</div>
      </div>
      <div style="clear:both;"></div>
    </div>
  </div>

  <div style="margin-top:12px;">
    <div><small>Previous OR #: <?=htmlspecialchars($first['previous_or_no'] ?? '-')?> &nbsp; Previous Date Paid: <?=htmlspecialchars($first['previous_date_paid'] ?? '-')?> &nbsp; Previous Year: <?=htmlspecialchars($first['previous_year'] ?? '-')?></small></div>
  </div>

  <div class="no-print" style="margin-top:12px;">
    <button onclick="window.print()" class="btn btn-primary">Print</button>
  </div>
</body>
</html>
