<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';

if ($_SESSION['role'] !== 'assessor') {
    header("Location: ../index.php");
    exit;
}

// --- Pagination ---
$limit = 50;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// --- Filters ---
$globalFilter   = $_GET['search']    ?? '';
$barangayFilter = $_GET['barangay']  ?? '';
$locationFilter = $_GET['location']  ?? '';
$ownerFilter    = $_GET['owner']     ?? '';
$tdFilter       = $_GET['td_no']     ?? '';

$whereParts = [];

if ($globalFilter !== '') {
    $esc = $mysqli->real_escape_string($globalFilter);
    $whereParts[] = "(p.barangay LIKE '%$esc%' OR p.location LIKE '%$esc%' OR o.name LIKE '%$esc%' OR p.td_no LIKE '%$esc%')";
}
if ($barangayFilter !== '') $whereParts[] = "p.barangay LIKE '%".$mysqli->real_escape_string($barangayFilter)."%'";
if ($locationFilter !== '') $whereParts[] = "p.location LIKE '%".$mysqli->real_escape_string($locationFilter)."%'";
if ($ownerFilter !== '') $whereParts[] = "o.name LIKE '%".$mysqli->real_escape_string($ownerFilter)."%'";
if ($tdFilter !== '') $whereParts[] = "p.td_no LIKE '%".$mysqli->real_escape_string($tdFilter)."%'";

$whereSql = $whereParts ? "WHERE " . implode(" AND ", $whereParts) : "";

// --- Total rows ---
$totalRes = $mysqli->query("
    SELECT COUNT(*) AS cnt
    FROM tax_bills tb
    JOIN assessments a ON a.id = tb.assessment_id
    JOIN properties p ON p.id = a.property_id
    LEFT JOIN owners o ON o.id = p.owner_id
    $whereSql
");
$total = $totalRes->fetch_assoc()['cnt'] ?? 0;
$total_pages = ($total > 0) ? ceil($total / $limit) : 1;

// --- Fetch bills ---
$res = $mysqli->query("
  SELECT tb.id AS bill_id, tb.tax_year, tb.rptsp_no, tb.status,
         p.id AS property_id, p.td_no, p.lot_no, p.location, p.barangay, p.classification, p.assessed_value,
         o.name AS owner_name,
         a.basic_tax, a.sef_tax, a.adjustments
  FROM tax_bills tb
  JOIN assessments a ON a.id = tb.assessment_id
  JOIN properties p ON p.id = a.property_id
  LEFT JOIN owners o ON o.id = p.owner_id
  $whereSql
  ORDER BY tb.id DESC
  LIMIT $limit OFFSET $offset
");

$today = new DateTime();
$curYear = (int)$today->format('Y');
$curMonth = (int)$today->format('n');
?>

<style>
/* 🟧 Blinking animation for high penalties */
@keyframes blinkOrange {
  0%, 100% { color: orange; }
  50% { color: #ff6600; }
}
.blink-orange {
  animation: blinkOrange 1.2s infinite;
  font-weight: bold;
}
</style>

<h2 class="mb-4">All Tax Bills</h2>

<div class="card mb-3">
  <div class="card-header bg-dark text-white">Search & Filter</div>
  <div class="card-body">
    <form method="get" class="row g-2">
      <input type="hidden" name="page" value="1">
      <div class="col-md-3">
        <input type="text" name="search" class="form-control" placeholder="🔍 Global Search"
               value="<?=htmlspecialchars($globalFilter)?>">
      </div>
      <div class="col-md-2"><input type="text" name="barangay" class="form-control" placeholder="Barangay" value="<?=htmlspecialchars($barangayFilter)?>"></div>
      <div class="col-md-2"><input type="text" name="location" class="form-control" placeholder="Location" value="<?=htmlspecialchars($locationFilter)?>"></div>
      <div class="col-md-2"><input type="text" name="owner" class="form-control" placeholder="Owner" value="<?=htmlspecialchars($ownerFilter)?>"></div>
      <div class="col-md-2"><input type="text" name="td_no" class="form-control" placeholder="TD No" value="<?=htmlspecialchars($tdFilter)?>"></div>
      <div class="col-md-1 d-grid"><button type="submit" class="btn btn-primary">Filter</button></div>
      <div class="col-md-1 d-grid"><a href="tax_billsall.php?page=1" class="btn btn-secondary">Clear</a></div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
    <span>Tax Bills</span>
    <div>
        <button type="button" id="paySelected" class="btn btn-warning btn-sm" disabled>💰 Pay Selected Year</button>
        <button type="button" class="btn btn-info btn-sm" id="printNATB">📄 Print NATB</button>
        <button type="button" class="btn btn-info btn-sm" id="printConsolidated">📄 Print Consolidated NATB</button>
    </div>
  </div>
  <div class="card-body table-responsive">
    <table class="table table-bordered table-hover align-middle">
      <thead class="table-dark">
        <tr>
          <th><input type="checkbox" id="select_all"></th>
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
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
      <?php if ($res && $res->num_rows > 0): ?>
        <?php while($row=$res->fetch_assoc()): ?>
          <?php
          $basic_tax = (float)$row['basic_tax'];
          $sef_tax = (float)$row['sef_tax'];
          $adjustments = (float)$row['adjustments'];
          $tax_year = (int)$row['tax_year'];
          $totalTax = $basic_tax + $sef_tax;

          // --- Discount ---
          $discount = 0; 
          $discountPercent = 0;

          if ($tax_year == $curYear && $curMonth >= 1 && $curMonth <= 3) {
          // Current year, paid early Q1 → 10% discount
          $discount = 0.10 * $totalTax; 
          $discountPercent = 10;
          } elseif ($tax_year == $curYear + 1 && $curMonth >= 10 && $curMonth <= 12) {
          // Next year, advance payment → 20% discount
          $discount = 0.20 * $totalTax; 
          $discountPercent = 20;
          }

          // --- Penalty ---
          $penalty = 0;
          $penaltyPercent = 0;

          if ($discountPercent == 0) { 
          // Only apply penalty if no discount
          if ($tax_year < $curYear) {
        $months_due = ($curYear - $tax_year) * 12 + $curMonth;
          } else {
        $months_due = max(0, $curMonth);
        }

        $penalty = min(0.02 * $months_due * $totalTax, 0.72 * $totalTax);
        $penaltyPercent = min($months_due * 2, 72);
        }


          // --- Color logic ---
          $penaltyClass = ($penaltyPercent > 50) ? "blink-orange" : "";
          $penaltyColor = ($penaltyPercent > 50) ? "orange" : "red";

          $total_due = $totalTax + $adjustments - $discount + $penalty;
          ?>
          <tr>
            <td><input type="checkbox" name="selected_years[]" value="<?=$row['bill_id']?>"></td>
            <td><?=$row['property_id']?></td>
            <td><?=htmlspecialchars($row['owner_name'] ?? 'N/A')?></td>
            <td><?=htmlspecialchars($row['td_no'])?></td>
            <td><?=htmlspecialchars($row['lot_no'])?></td>
            <td><?=htmlspecialchars($row['classification'])?></td>
            <td><?=htmlspecialchars($row['barangay'])?></td>
            <td><?=htmlspecialchars($row['location'])?></td>
            <td><?=$tax_year?></td>
            <td>₱<?=number_format($basic_tax,2)?></td>
            <td>₱<?=number_format($sef_tax,2)?></td>
            <td>₱<?=number_format($adjustments,2)?></td>

            <td>
              ₱<?=number_format($discount,2)?>
              <?php if($discountPercent>0): ?>
                <span style="color:green;">(<?=$discountPercent?>%)</span>
              <?php endif; ?>
            </td>

            <td>
              ₱<?=number_format($penalty,2)?>
              <?php if($penalty>0): ?>
                <span class="<?=$penaltyClass?>" style="color:<?=$penaltyColor?>;">
                  (<?=$penaltyPercent?>%)
                </span>
              <?php endif; ?>
            </td>

            <td><b style="color:#003366;">₱<?=number_format($total_due,2)?></b></td>

            <td>
              <?php if(strtolower($row['status'])=='paid'): ?>
                <span class="badge bg-success">Paid</span>
              <?php else: ?>
                <span class="badge bg-danger">Unpaid</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="16" class="text-center">No tax bills found.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>

    <nav>
      <ul class="pagination justify-content-center">
        <li class="page-item <?=($page <= 1)?'disabled':''?>">
          <a class="page-link" href="?page=<?=($page-1)?>">Previous</a>
        </li>
        <?php for($i=1; $i<=$total_pages; $i++): ?>
          <li class="page-item <?=($i==$page)?'active':''?>">
            <a class="page-link" href="?page=<?=$i?>"><?=$i?></a>
          </li>
        <?php endfor; ?>
        <li class="page-item <?=($page >= $total_pages)?'disabled':''?>">
          <a class="page-link" href="?page=<?=($page+1)?>">Next</a>
        </li>
      </ul>
    </nav>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
  $('#select_all').on('change', function() {
    $('input[name="selected_years[]"]').prop('checked', $(this).is(':checked'));
    togglePayButton();
  });

  $(document).on('change', 'input[name="selected_years[]"]', function() {
    if (!$(this).is(':checked')) $('#select_all').prop('checked', false);
    else if ($('input[name="selected_years[]"]:checked').length === $('input[name="selected_years[]"]').length)
      $('#select_all').prop('checked', true);
    togglePayButton();
  });

  function togglePayButton() {
    $('#paySelected').prop('disabled', $('input[name="selected_years[]"]:checked').length === 0);
  }

  $('#paySelected').on('click', function() {
    const selected = $('input[name="selected_years[]"]:checked')
      .map(function() { return $(this).val(); }).get();

    if (selected.length === 0) return alert('Select at least one tax bill.');

    if (!confirm('Proceed with payment for selected year(s)?')) return;

    $.ajax({
      url: 'pay_selected.php',
      type: 'POST',
      dataType: 'json',
      data: { selected_ids: selected },
      success: function(response) {
        if (response.success) {
          alert(response.message);
          location.reload();
        } else {
          alert('Error: ' + response.message);
        }
      },
      error: function (xhr, status, err) {
        console.error('AJAX Error:', err);
        alert('Server error:\n' + xhr.responseText);
      }
    });
  });

  $('#printNATB').on('click', function() {
    const selected = $('input[name="selected_years[]"]:checked')
      .map(function() { return $(this).val(); }).get();
    if (selected.length === 0) return alert('Please select at least one tax bill to print.');
    window.open('report_taxbill.php?bills=' + selected.join(','), '_blank');
  });

  $('#printConsolidated').on('click', function() {
    const selected = $('input[name="selected_years[]"]:checked')
      .map(function() { return $(this).val(); }).get();
    if (selected.length === 0) return alert('Please select at least one tax bill to print.');
    window.open('report_taxbillall.php?bills=' + selected.join(','), '_blank');
  });
});
</script>
