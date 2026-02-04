<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';

$ids = $_GET['ids'] ?? '';
if (!$ids) die("No checks selected");

$ids_array = array_map('intval', explode(',', $ids));
$placeholders = implode(',', array_fill(0, count($ids_array), '?'));

$stmt = $mysqli->prepare("SELECT c.*, a.account_name, a.account_number, f.name AS fund_name
    FROM checks_issued c 
    LEFT JOIN bank_accounts a ON a.id = c.account_id 
    LEFT JOIN fund_source f ON f.id = c.fund_source_id
    WHERE c.id IN ($placeholders) 
    ORDER BY c.issue_date ASC");

$stmt->bind_param(str_repeat('i', count($ids_array)), ...$ids_array);
$stmt->execute();
$result = $stmt->get_result();

$checks = [];
while ($row = $result->fetch_assoc()) $checks[] = $row;
$stmt->close();

/* ------------------------------
   FETCH MUNICIPALITY FROM system_settings
------------------------------ */
$municipality = "UNKNOWN LGU";
$res = $mysqli->query("SELECT municipality FROM system_settings LIMIT 1");
if ($res && $r = $res->fetch_assoc()) {
    $municipality = $r['municipality'];
}

/* ------------------------------
   FETCH FUND FROM fund_source (uses first check's fund)
------------------------------ */
$fund = "UNKNOWN FUND";
if (!empty($checks) && !empty($checks[0]['fund_name'])) {
    $fund = $checks[0]['fund_name'];
}

/* ------------------------------
   AUTO-GENERATE REPORT NUMBER YYYY-MM-#### (sequence)
------------------------------ */
$year = date("Y");
$month = date("m");
$prefix = "$year-$month";

$seq_query = $mysqli->prepare("
    SELECT report_no 
    FROM report_sequence 
    WHERE report_no LIKE CONCAT(?, '%') 
    ORDER BY id DESC 
    LIMIT 1
");
$seq_query->bind_param("s", $prefix);
$seq_query->execute();
$seq_res = $seq_query->get_result();

if ($seq_res->num_rows > 0) {
    $last = $seq_res->fetch_assoc()['report_no'];
    $last_seq = intval(substr($last, -4));
    $new_seq = str_pad($last_seq + 1, 4, "0", STR_PAD_LEFT);
} else {
    $new_seq = "0001";
}
$report_no = "$prefix-$new_seq";

/* Save sequence */
$save_seq = $mysqli->prepare("INSERT INTO report_sequence (report_no) VALUES (?)");
$save_seq->bind_param("s", $report_no);
$save_seq->execute();

/* ------------------------------
   SHEET PAGINATION (15 rows per sheet)
------------------------------ */
$rows_per_sheet = 15;
$item_count = count($checks);
$total_sheets = max(1, ceil($item_count / $rows_per_sheet));
$sheet_no = 1;

/* Totals */
$total_amount = array_sum(array_column($checks, 'amount'));
$total_gross = array_sum(array_column($checks, 'gross_amount'));
?>

<!DOCTYPE html>
<html>
<head>
    <title>Report of Checks Issued</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #000; padding: 4px; }
        th { text-align: center; }
        td.text-left { text-align: left; }
        td.text-right { text-align: right; }
        .header-table td { border: none; padding: 2px; }
        .cert { margin-top: 20px; }
        .cert td { border: none; padding: 4px; }
        .center { text-align: center; }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>

<?php
/* MULTI-SHEET PRINTING LOOP */
$chunks = array_chunk($checks, $rows_per_sheet);
foreach ($chunks as $index => $chunk):
$sheet_no = $index + 1;
?>

<?php if ($sheet_no > 1): ?>
<div class="page-break"></div>
<?php endif; ?>

<h3 class="center">REPORT OF CHECKS ISSUED</h3>

<table class="header-table">
<tr>
    <td>LGU: <?= htmlspecialchars($municipality) ?></td>
    <td>Report No.: <?= $report_no ?></td>
</tr>
<tr>
    <td>FUND: <?= htmlspecialchars($fund) ?></td>
    <td>Sheet No: <?= $sheet_no ?> of <?= $total_sheets ?></td>
</tr>
<tr>
    <td>BANK Name/Account No: DBP/0736-021559-030/00005041-738-7</td>
    <td>Period Covered: <?= date('F, Y') ?></td>
</tr>
</table>

<table>
<thead>
<tr>
    <th>Date</th>
    <th>Check Serial No.</th>
    <th>DV/Payroll No.</th>
    <th>CAFOA No.</th>
    <th>Payee</th>
    <th>Nature of Payments</th>
    <th>Gross Amount</th>
    <th>Amount</th>
</tr>
</thead>
<tbody>

<?php foreach ($chunk as $c): ?>
<tr>
    <td class="center"><?= date('m/d/y', strtotime($c['issue_date'])) ?></td>
    <td class="center"><?= htmlspecialchars($c['check_no']) ?></td>
    <td class="center"><?= htmlspecialchars($c['dv_payroll_no']) ?></td>
    <td class="center"><?= htmlspecialchars($c['cafoa_no']) ?></td>
    <td class="text-left"><?= htmlspecialchars($c['payee']) ?></td>
    <td class="text-left"><?= nl2br(htmlspecialchars($c['nature_of_payment'])) ?></td>
    <td class="text-right"><?= number_format($c['gross_amount'], 2) ?></td>
    <td class="text-right"><?= number_format($c['amount'], 2) ?></td>
</tr>
<?php endforeach; ?>

<?php if ($sheet_no == $total_sheets): ?>
<tr>
    <td colspan="6" class="center"><strong>TOTAL</strong></td>
    <td class="text-right"><strong><?= number_format($total_gross, 2) ?></strong></td>
    <td class="text-right"><strong><?= number_format($total_amount, 2) ?></strong></td>
</tr>
<?php endif; ?>

</tbody>
</table>

<?php endforeach; ?>

<script>
window.onload = function() { window.print(); }
</script>

</body>
</html>
