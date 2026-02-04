<?php
require 'db.php';
include 'header.php';

// --- Pagination ---
$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// --- Filters ---
$search    = $_GET['search']    ?? '';
$payor     = $_GET['payor']     ?? '';
$or_no     = $_GET['or_no']     ?? '';
$tax_year  = $_GET['tax_year']  ?? '';

$where = [];
if ($search !== '') {
    $s = $mysqli->real_escape_string($search);
    $where[] = "(pa.or_no LIKE '%$s%' OR pa.payor_name LIKE '%$s%' OR pa.tax_year LIKE '%$s%' OR pa.property_id LIKE '%$s%')";
}
if ($payor !== '') {
    $p = $mysqli->real_escape_string($payor);
    $where[] = "pa.payor_name LIKE '%$p%'";
}
if ($or_no !== '') {
    $o = $mysqli->real_escape_string($or_no);
    $where[] = "pa.or_no LIKE '%$o%'";
}
if ($tax_year !== '') {
    $t = $mysqli->real_escape_string($tax_year);
    $where[] = "pa.tax_year = '$t'";
}

$whereSql = $where ? "WHERE " . implode(" AND ", $where) : "";

// Count
$totalRes = $mysqli->query("SELECT COUNT(*) as cnt FROM payment_audit pa $whereSql");
$total = $totalRes->fetch_assoc()['cnt'] ?? 0;
$total_pages = ($total > 0) ? ceil($total / $limit) : 1;

// Fetch
$res = $mysqli->query("
    SELECT pa.*
    FROM payment_audit pa
    $whereSql
    ORDER BY pa.created_at DESC
    LIMIT $limit OFFSET $offset
");
?>

<h2 class="mb-4">Payment Audit Trail</h2>

<div class="card mb-3">
  <div class="card-header bg-dark text-white">Search & Filter</div>
  <div class="card-body">
    <form method="get" class="row g-2">
      <input type="hidden" name="page" value="1">

      <div class="col-md-3">
        <input type="text" name="search" class="form-control" placeholder="🔍 Global Search"
               value="<?=htmlspecialchars($search)?>">
      </div>
      <div class="col-md-2">
        <input type="text" name="payor" class="form-control" placeholder="Payor Name"
               value="<?=htmlspecialchars($payor)?>">
      </div>
      <div class="col-md-2">
        <input type="text" name="or_no" class="form-control" placeholder="OR Number"
               value="<?=htmlspecialchars($or_no)?>">
      </div>
      <div class="col-md-2">
        <input type="text" name="tax_year" class="form-control" placeholder="Tax Year"
               value="<?=htmlspecialchars($tax_year)?>">
      </div>
      <div class="col-md-1 d-grid">
        <button type="submit" class="btn btn-primary">Filter</button>
      </div>
      <div class="col-md-1 d-grid">
        <a href="audit.php?page=1" class="btn btn-secondary">Clear</a>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header bg-secondary text-white">Audit Records</div>
  <div class="card-body table-responsive">
    <table class="table table-bordered table-hover align-middle">
      <thead class="table-dark">
        <tr>
          <th>Payment ID</th>
          <th>Property ID</th>
          <th>Tax Year</th>
          <th>OR No</th>
          <th>Payor</th>
          <th>Basic Tax</th>
          <th>SEF Tax</th>
          <th>Discount</th>
          <th>Penalty</th>
          <th>Total Paid</th>
          <th>Date Logged</th>
        </tr>
      </thead>
      <tbody>
      <?php if ($res && $res->num_rows > 0): ?>
        <?php while($row = $res->fetch_assoc()): ?>
          <tr>
            <td><?=$row['payment_id']?></td>
            <td><?=$row['property_id']?></td>
            <td><?=$row['tax_year']?></td>
            <td><?=htmlspecialchars($row['or_no'])?></td>
            <td><?=htmlspecialchars($row['payor_name'])?></td>
            <td>₱<?=number_format($row['basic_tax'],2)?></td>
            <td>₱<?=number_format($row['sef_tax'],2)?></td>
            <td>-₱<?=number_format($row['discount'],2)?></td>
            <td>₱<?=number_format($row['penalty'],2)?></td>
            <td><b>₱<?=number_format($row['total_due'],2)?></b></td>
            <td><?=$row['created_at']?></td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="11" class="text-center">No records found.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>

    <!-- Pagination -->
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

<?php include 'footer.php'; ?>
