<?php
ob_start();
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';

$current_user = $_SESSION['name'] ?? '';
$date = $_GET['date'] ?? date('Y-m-d');
$logo_path = '../uploads/municipal_logo.png';

// Fetch Treasurer & Accountant from signatories table
$treasurer_name = $treasurer_title = '';
$signatories_query = $mysqli->query("SELECT position, name, title FROM signatories");
if ($signatories_query && $signatories_query->num_rows > 0) {
    while ($sig = $signatories_query->fetch_assoc()) {
        if (strtolower($sig['position']) === 'treasurer') {
            $treasurer_name = $sig['name'];
            $treasurer_title = $sig['title'];
        }
    }
}

// Fetch CTC records for the given date & collector
$stmt = $mysqli->prepare("
    SELECT id, ctc_no, date_issued, surname, firstname, middlename, total_due
    FROM ctc_individual
    WHERE DATE(date_issued) = ? AND created_by = ?
    ORDER BY id ASC
");
$stmt->bind_param('ss', $date, $current_user);
$stmt->execute();
$result = $stmt->get_result();

$total = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Daily CTC Abstract</title>
  <link rel="stylesheet" href="../assets/bootstrap.min.css">
  <style>
    body {
      font-family: "Times New Roman", serif;
      font-size: 13.5px;
      color: #222;
      background: #f9f9f9;
    }
    .report-container {
      background: #fff;
      border-radius: 6px;
      padding: 25px 30px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    .header-text {
      text-align: center;
      font-weight: bold;
      line-height: 1.2;
      margin-bottom: 25px;
    }
    .header-text img {
      width: 85px;
      height: 85px;
      object-fit: contain;
      margin-bottom: 8px;
    }
    .header-text h6 {
      font-size: 15px;
      margin: 2px 0;
      text-transform: uppercase;
    }
    .header-text h5 {
      font-size: 18px;
      text-decoration: underline;
      margin-top: 10px;
      color: #2b2b2b;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
      font-size: 10px;
      table-layout: fixed;
      background: #fff;
    }
    th, td {
      border: 1px solid #000;
      padding: 7px 9px;
      vertical-align: middle;
      word-wrap: break-word;
    }
    th {
      background: #e9ecef;
      text-align: center;
      font-weight: bold;
      letter-spacing: 0.3px;
    }
    tr:nth-child(even) td {
      background-color: #fafafa;
    }
    th:nth-child(1), td:nth-child(1) { width: 10%; text-align: center; }
    th:nth-child(2), td:nth-child(2) { width: 15%; text-align: center; }
    th:nth-child(3), td:nth-child(3) { width: 13%; text-align: center; }
    th:nth-child(4), td:nth-child(4) { width: 32%; padding-left: 8px; }
    th:nth-child(5), td:nth-child(5) { width: 10%; text-align: center; }
    th:nth-child(6), td:nth-child(6) { width: 20%; text-align: right; padding-right: 10px; }

    td.text-end, th.text-end { text-align: right; padding-right: 10px; }

    tfoot td.total-label { text-align: right; font-weight: bold; padding-right: 10px; }
    tfoot td.total-value { text-align: right; font-weight: bold; font-size: 16px; color: #000; padding-right: 10px; }

    .footer-date { text-align: center; margin-top: 25px; font-size: 12.5px; color: #333; }

    .signature-section { margin-top: 70px; text-align: center; font-weight: bold; page-break-inside: avoid; }
    .signature-group { display: flex; justify-content: center; gap: 120px; flex-wrap: nowrap; margin: 0 auto; width: 100%; align-items: flex-start; }
    .signature-block { text-align: center; min-width: 200px; white-space: nowrap !important; }
    .signature-line { border-bottom: 1px solid #000; height: 1px; margin: 0 auto 6px auto; display: inline-block; width: 220px; transition: width 0.2s ease; }
    .signature-name { font-weight: bold; display: block; margin-top: 3px; }
    .signature-title { font-size: 12px; font-style: italic; color: #333; }

    @media print {
      .no-print { display: none !important; }
      body { background: #fff; }
      .report-container { box-shadow: none; border: none; padding: 0; }
      .signature-group { justify-content: center; gap: 120px; page-break-inside: avoid; }
      .signature-block { white-space: nowrap !important; }
      .signature-line { width: 220px !important; }
    }
  </style>
</head>
<body>
<div class="container mt-4 mb-5 report-container">

  <div class="header-text">
    <?php if (file_exists($logo_path)): ?>
      <img src="<?= htmlspecialchars($logo_path) ?>" alt="Municipal Logo">
    <?php endif; ?>
    <h6>Republic of the Philippines</h6>
    <h6>Province of Cebu</h6>
    <h6><strong>Municipality of Argao</strong></h6>
    <h5>DAILY ABSTRACT OF CTC COLLECTION PER COLLECTOR</h5>
  </div>

  <p><strong>Collector Name:</strong> <?= htmlspecialchars($current_user) ?><br>
     <strong>Date:</strong> <?= date('F j, Y', strtotime($date)) ?></p>

  <table>
    <thead>
      <tr>
        <th>FORM NO.</th>
        <th>CTC NUMBER</th>
        <th>DATE</th>
        <th>NAME</th>
        <th>CODE</th>
        <th class="text-end">AMOUNT</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td>CTC-IND</td>
            <td><?= htmlspecialchars($row['ctc_no']) ?></td>
            <td><?= date('n/j/Y', strtotime($row['date_issued'])) ?></td>
            <td><?= htmlspecialchars($row['surname'] . ', ' . $row['firstname'] . ' ' . $row['middlename']) ?></td>
            <td></td>
            <td class="text-end">₱ <?= number_format($row['total_due'], 2) ?></td>
          </tr>
          <?php $total += $row['total_due']; ?>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="6" style="text-align:center; color:#666;">No records found for this date.</td></tr>
      <?php endif; ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="5" class="total-label">TOTAL:</td>
        <td class="total-value">₱ <?= number_format($total, 2) ?></td>
      </tr>
    </tfoot>
  </table>

  <div class="signature-section">
    <div class="signature-group">
      <div class="signature-block">
        <div class="signature-line"></div>
        <div class="signature-name"><?= htmlspecialchars($current_user) ?></div>
        <div class="signature-title">Collector</div>
      </div>
      <div class="signature-block">
        <div class="signature-line"></div>
        <div class="signature-name"><?= htmlspecialchars($treasurer_name) ?></div>
        <div class="signature-title"><?= htmlspecialchars($treasurer_title) ?></div>
      </div>
    </div>
  </div>

  <div class="footer-date">
    <p><small><?= date('F j, Y') ?> &nbsp;&nbsp;&nbsp; Page 1 of 1</small></p>
  </div>

</div>

<div class="no-print">
  <button class="btn btn-primary" onclick="window.print()">🖨️ Print Report</button>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".signature-block").forEach(block => {
    const name = block.querySelector(".signature-name");
    const line = block.querySelector(".signature-line");
    if (name && line) {
      const nameWidth = name.offsetWidth;
      line.style.width = (nameWidth + 60) + "px";
    }
  });
});
</script>
</body>
</html>
<?php ob_end_flush(); ?>
