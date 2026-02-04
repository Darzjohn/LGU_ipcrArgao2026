<?php
require 'db.php';
include 'header.php';

// --- Pagination Setup ---
$limit = 10; 
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// --- Filters ---
$globalFilter   = $_GET['search']    ?? '';
$barangayFilter = $_GET['barangay']  ?? '';
$locationFilter = $_GET['location']  ?? '';
$ownerFilter    = $_GET['owner']     ?? '';
$tdFilter       = $_GET['td_no']     ?? '';

// Build filter query string for pagination links
$queryParams = $_GET;
unset($queryParams['page']); 
$filterQuery = http_build_query($queryParams);

$whereParts = [];

// Global search
if ($globalFilter !== '') {
    $esc = $mysqli->real_escape_string($globalFilter);
    $whereParts[] = "(p.barangay LIKE '%$esc%' OR p.location LIKE '%$esc%' OR o.name LIKE '%$esc%' OR p.td_no LIKE '%$esc%')";
}

// Advanced filters
if ($barangayFilter !== '') {
    $esc = $mysqli->real_escape_string($barangayFilter);
    $whereParts[] = "p.barangay LIKE '%$esc%'";
}
if ($locationFilter !== '') {
    $esc = $mysqli->real_escape_string($locationFilter);
    $whereParts[] = "p.location LIKE '%$esc%'";
}
if ($ownerFilter !== '') {
    $esc = $mysqli->real_escape_string($ownerFilter);
    $whereParts[] = "o.name LIKE '%$esc%'";
}
if ($tdFilter !== '') {
    $esc = $mysqli->real_escape_string($tdFilter);
    $whereParts[] = "p.td_no LIKE '%$esc%'";
}

$whereSql = $whereParts ? "WHERE " . implode(" AND ", $whereParts) : "";

// --- Count total ---
$totalRes = $mysqli->query("
    SELECT COUNT(tb.id) AS cnt
    FROM tax_bills tb
    JOIN assessments a ON a.id = tb.assessment_id
    JOIN properties p ON p.id = a.property_id
    LEFT JOIN owners o ON o.id = p.owner_id
    $whereSql
");
$total = $totalRes->fetch_assoc()['cnt'] ?? 0;
$total_pages = max(1, ceil($total / $limit));

// --- Fetch bills ---
$res = $mysqli->query("
  SELECT tb.id AS bill_id, tb.tax_year,
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
?>

<h2 class="mb-4">Tax Bills</h2>

<div class="card mb-3">
  <div class="card-header bg-dark text-white">Search & Filter</div>
  <div class="card-body">
    <form method="get" class="row g-2">
      <input type="hidden" name="page" value="1">
      <div class="col-md-3"><input type="text" name="search" class="form-control" placeholder="🔍 Global Search" value="<?=htmlspecialchars($globalFilter)?>"></div>
      <div class="col-md-2"><input type="text" name="barangay" class="form-control" placeholder="Barangay" value="<?=htmlspecialchars($barangayFilter)?>"></div>
      <div class="col-md-2"><input type="text" name="location" class="form-control" placeholder="Location" value="<?=htmlspecialchars($locationFilter)?>"></div>
      <div class="col-md-2"><input type="text" name="owner" class="form-control" placeholder="Owner" value="<?=htmlspecialchars($ownerFilter)?>"></div>
      <div class="col-md-2"><input type="text" name="td_no" class="form-control" placeholder="TD No" value="<?=htmlspecialchars($tdFilter)?>"></div>
      <div class="col-md-1 d-grid"><button type="submit" class="btn btn-primary">Filter</button></div>
      <div class="col-md-1 d-grid"><a href="tax_bills.php?page=1" class="btn btn-secondary">Clear</a></div>
    </form>
  </div>
</div>

<form method="post" action="payments.php">
<div class="card">
  <div class="card-header bg-secondary text-white d-flex justify-content-between">
    <span>Property Tax Bills</span>
    <button type="submit" name="pay_selected" class="btn btn-warning btn-sm">💰 Pay Selected Year</button>
  </div>
  <div class="card-body table-responsive">
    <table class="table table-bordered table-hover align-middle">
      <thead class="table-dark">
        <tr>
          <th><input type="checkbox" id="select_all"></th>
          <th>Bill ID</th>
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
          <th>Adjustments</th>
          <th>Discount</th>
          <th>Penalty</th>
          <th>Total Due</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php
      $today = new DateTime();
      $curYear = (int)$today->format('Y');
      $curMonth = (int)$today->format('n');

      if ($res && $res->num_rows > 0):
        while($row=$res->fetch_assoc()):
          $basic = (float)$row['basic_tax'];
          $sef = (float)$row['sef_tax'];
          $adj = (float)$row['adjustments'];
          $assessed = (float)$row['assessed_value'];

          $discount = 0;
          $penalty = 0;

          $year = (int)$row['tax_year'];
          if($year == $curYear && $curMonth <= 3) {
              $discount = 0.10 * ($basic + $sef);
          } elseif($year == $curYear + 1 && $curMonth >= 10) {
              $discount = 0.20 * ($basic + $sef);
          }

          if($year == $curYear) {
              $months_due = max(0, $curMonth);
          } elseif($year < $curYear) {
              $months_due = ($curYear - $year) * 12 + $curMonth;
          } else {
              $months_due = 0;
          }
          $penalty = min(0.02 * $months_due * ($basic + $sef), 0.72 * ($basic + $sef));

          $total_due = $basic + $sef + $adj - $discount + $penalty;
      ?>
        <tr>
          <td><input type="checkbox" name="selected_bills[]" value="<?=$row['bill_id']?>"></td>
          <td><?=$row['bill_id']?></td>
          <td><?=htmlspecialchars($row['owner_name'] ?? 'N/A')?></td>
          <td><?=htmlspecialchars($row['td_no'])?></td>
          <td><?=htmlspecialchars($row['lot_no'])?></td>
          <td><?=htmlspecialchars($row['classification'])?></td>
          <td><?=htmlspecialchars($row['barangay'])?></td>
          <td><?=htmlspecialchars($row['location'])?></td>
          <td><?=$row['tax_year']?></td>
          <td>₱<?=number_format($assessed,2)?></td>
          <td>₱<?=number_format($basic,2)?></td>
          <td>₱<?=number_format($sef,2)?></td>
          <td>₱<?=number_format($adj,2)?></td>
          <td>₱<?=number_format($discount,2)?></td>
          <td>₱<?=number_format($penalty,2)?> (<?=round(($penalty/($basic+$sef))*100,2)?>%)</td>
          <td>₱<?=number_format($total_due,2)?></td>
          <td>
            <a href="report_taxbill.php?property_id=<?=$row['property_id']?>" target="_blank" class="btn btn-info btn-sm">📄 Print NATB</a>
          </td>
        </tr>
      <?php endwhile; else: ?>
        <tr><td colspan="17" class="text-center">No tax bills found.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>

    <nav>
      <ul class="pagination justify-content-center">
        <li class="page-item <?=($page<=1)?'disabled':''?>"><a class="page-link" href="?page=<?=($page-1)?>&<?=$filterQuery?>">Previous</a></li>
        <?php for($i=1;$i<=$total_pages;$i++): ?>
          <li class="page-item <?=($i==$page)?'active':''?>"><a class="page-link" href="?page=<?=$i?>&<?=$filterQuery?>"><?=$i?></a></li>
        <?php endfor; ?>
        <li class="page-item <?=($page>=$total_pages)?'disabled':''?>"><a class="page-link" href="?page=<?=($page+1)?>&<?=$filterQuery?>">Next</a></li>
      </ul>
    </nav>
  </div>
</div>
</form>

<script>
document.getElementById('select_all').onclick = function(){
  let boxes=document.querySelectorAll('input[name="selected_bills[]"]');
  boxes.forEach(cb=>cb.checked=this.checked);
};
</script>

<?php include 'footer.php'; ?>
