<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';

// Only Assessor can access
if(!in_array($_SESSION['role'], ['admin','assessor','assessment_clerk','cashier'])) {
    header("Location: ../index.php");
    exit;
}

// Pagination
$limit = 50;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Filters
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

// Total rows
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

// Fetch bills
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
          <th>Assessed Value</th>
          <th>Basic Tax</th>
          <th>SEF Tax</th>
          <th>Tax Due</th>
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
          $sef_tax   = (float)$row['sef_tax'];
          $adjustments = (float)$row['adjustments'];
          $assessed_value = (float)$row['assessed_value'];
          $tax_due = $basic_tax + $sef_tax;

          $tax_year = (int)$row['tax_year'];
          $discount = 0; $discountPercent = 0;
          if ($tax_year == $curYear && $curMonth >= 1 && $curMonth <= 3) { $discount = 0.10*$tax_due; $discountPercent=10;}
          if ($tax_year == $curYear + 1 && $curMonth >= 10 && $curMonth <= 12) { $discount = 0.20*$tax_due; $discountPercent=20;}
          $penalty=0; $penaltyPercent=0;
          if ($discountPercent==0) {
            $months_due = ($tax_year < $curYear)? ($curYear-$tax_year)*12+$curMonth : max(0,$curMonth);
            $penalty = min(0.02*$months_due*$tax_due,0.72*$tax_due);
            $penaltyPercent = min($months_due*2,72);
          }
          $total_due = $tax_due + $adjustments - $discount + $penalty;
          $penaltyClass = ($penaltyPercent>50)?"blink-orange":"";
          $penaltyColor = ($penaltyPercent>50)?"orange":"red";

          $isPaid = strtolower($row['status']) === 'paid';
          ?>
          <tr class="<?= $isPaid ? 'paid-row' : '' ?>" <?= $isPaid ? 'title="Already Paid"' : '' ?>>
            <td>
              <input type="checkbox" name="selected_years[]" value="<?=$row['bill_id']?>"
                     <?= $isPaid ? 'disabled' : '' ?> class="bill-checkbox">
            </td>
            <td><?=$row['property_id']?></td>
            <td><?=htmlspecialchars($row['owner_name'])?></td>
            <td><?=htmlspecialchars($row['td_no'])?></td>
            <td><?=htmlspecialchars($row['lot_no'])?></td>
            <td><?=htmlspecialchars($row['classification'])?></td>
            <td><?=htmlspecialchars($row['barangay'])?></td>
            <td><?=htmlspecialchars($row['location'])?></td>
            <td><?=$tax_year?></td>
            <td>₱<?=number_format($assessed_value,2)?></td>
            <td>₱<?=number_format($basic_tax,2)?></td>
            <td>₱<?=number_format($sef_tax,2)?></td>
            <td>₱<?=number_format($tax_due,2)?></td>
            <td>₱<?=number_format($adjustments,2)?></td>
            <td>₱<?=number_format($discount,2)?> <?php if($discountPercent>0):?><span style="color:green;">(<?=$discountPercent?>%)</span><?php endif;?></td>
            <td>₱<?=number_format($penalty,2)?> <?php if($penalty>0):?><span class="<?=$penaltyClass?>" style="color:<?=$penaltyColor?>">(<?=$penaltyPercent?>%)</span><?php endif;?></td>
            <td><b style="color:#003366;">₱<?=number_format($total_due,2)?></b></td>
            <td><?= $isPaid ? '<span class="badge bg-success">Paid</span>' : '<span class="badge bg-danger">Unpaid</span>' ?></td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="18" class="text-center">No tax bills found.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ✅ Styling for paid rows with tooltip -->
<style>
  .paid-row {
    background-color: #f0f0f0 !important;
    color: #777 !important;
    cursor: not-allowed;
    opacity: 0.7;
  }
  .paid-row:hover {
    background-color: #e2e2e2 !important;
  }
  .paid-row input[type="checkbox"] {
    cursor: not-allowed;
  }
</style>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(function(){
  // ✅ Only select unpaid rows
  $('#select_all').change(function(){
    const checked = this.checked;
    $('input[name="selected_years[]"]:not(:disabled)').prop('checked', checked);
    togglePayButton();
  });

  $('input[name="selected_years[]"]').change(function(){
    const all = $('input[name="selected_years[]"]:not(:disabled)');
    const checked = all.filter(':checked').length;
    $('#select_all').prop('checked', checked === all.length);
    togglePayButton();
  });

  function togglePayButton(){
    $('#paySelected').prop('disabled',$('input[name="selected_years[]"]:checked').length==0);
  }

  $('#paySelected').click(function(){
    const selected = $('input[name="selected_years[]"]:checked').map(function(){return this.value;}).get();
    if(selected.length==0) return alert('Select at least one tax bill.');
    if(!confirm('Proceed with payment for selected year(s)?')) return;
    $.post('pay_selected.php',{selected_ids:selected},function(resp){
      if(resp.success){ alert(resp.message); location.reload();}
      else alert('Error: '+resp.message);
    },'json').fail(function(xhr){alert('Server error:\n'+xhr.responseText);});
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
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
