<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
$messages = [];

// Only Assessor can manage users
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

// Global filter
if ($globalFilter !== '') {
    $esc = $mysqli->real_escape_string($globalFilter);
    $whereParts[] = "(p.barangay LIKE '%$esc%' OR p.location LIKE '%$esc%' OR o.name LIKE '%$esc%' OR p.td_no LIKE '%$esc%')";
}

// Advanced filters
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
          $discount = 0; $discountPercent = 0;
          if ($tax_year == $curYear && $curMonth >= 1 && $curMonth <= 3) {
              $discount = 0.10 * $totalTax; $discountPercent = 10;
          } elseif ($tax_year == $curYear+1 && $curMonth >= 10 && $curMonth <= 12) {
              $discount = 0.20 * $totalTax; $discountPercent = 20;
          }
          if($tax_year < $curYear) {
              $months_due = ($curYear - $tax_year)*12 + $curMonth;
          } else $months_due = max(0,$curMonth);
          $penalty = min(0.02 * $months_due * $totalTax, 0.72 * $totalTax);
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
            <td>₱<?=number_format($discount,2)?> <?=($discountPercent>0)?'('.$discountPercent.'%)':''?></td>
            <td>₱<?=number_format($penalty,2)?></td>
            <td><b style="color:#003366;">₱<?=number_format($total_due,2)?></b></td>
            <td>
              <?php if($row['status']=='Paid'): ?>
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

  // ✅ Select All Function
  $('#select_all').on('change', function() {
    $('input[name="selected_years[]"]').prop('checked', $(this).is(':checked'));
    togglePayButton();
  });

  // ✅ When individual boxes change
  $(document).on('change', 'input[name="selected_years[]"]', function() {
    if (!$(this).is(':checked')) $('#select_all').prop('checked', false);
    else if ($('input[name="selected_years[]"]:checked').length === $('input[name="selected_years[]"]').length)
      $('#select_all').prop('checked', true);
    togglePayButton();
  });

  // ✅ Enable/disable Pay button
  function togglePayButton() {
    $('#paySelected').prop('disabled', $('input[name="selected_years[]"]:checked').length === 0);
  }

  // ✅ Pay Selected
  $('#paySelected').on('click', function() {
    const selected = $('input[name="selected_years[]"]:checked')
      .map(function() { return $(this).val(); }).get();

    if (selected.length === 0) return alert('Select at least one tax bill.');

    if (!confirm('Proceed with payment for selected year(s)?')) return;

    $.ajax({
      url: 'api/pay_selected.php',
      type: 'POST',
      dataType: 'json',
      data: { selected_ids: selected },
      success: function(response) {
        if (response.success) {
          alert(response.message);
          const lastORs = response.message.match(/\d{4}-\d{6}/g);
          if (lastORs && lastORs.length > 0) {
            const lastOrNo = lastORs[lastORs.length - 1];
            window.open('report_or.php?or_no=' + lastOrNo, '_blank');
          }
          location.reload();
        } else {
          alert('Error: ' + response.message);
        }
      },
      
      error: function (xhr, status, err) {
    console.error('AJAX Error:', err);
    console.log('Response:', xhr.responseText);
    alert('Server error:\n' + xhr.responseText);
}

    });
  });

  // ✅ Print NATB
  $('#printNATB').on('click', function() {
    const selected = $('input[name="selected_years[]"]:checked')
      .map(function() { return $(this).val(); }).get();
    if (selected.length === 0) return alert('Please select at least one tax bill to print.');
    window.open('report_taxbill.php?bills=' + selected.join(','), '_blank');
  });

  // ✅ Print Consolidated NATB
  $('#printConsolidated').on('click', function() {
    const selected = $('input[name="selected_years[]"]:checked')
      .map(function() { return $(this).val(); }).get();
    if (selected.length === 0) return alert('Please select at least one tax bill to print.');
    window.open('report_taxbillall.php?bills=' + selected.join(','), '_blank');
  });
});
</script>

