<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';

// Only assessor or admin can view payments
if(!in_array($_SESSION['role'], ['admin','assessor'])) {
    header("Location: ../index.php");
    exit;
}

// --- Pagination ---
$limit = 50;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// --- Filters ---
$globalFilter = $_GET['search'] ?? '';
$whereParts = [];
if ($globalFilter !== '') {
    $esc = $mysqli->real_escape_string($globalFilter);
    $whereParts[] = "(owner_name LIKE '%$esc%' OR td_no LIKE '%$esc%' OR lot_no LIKE '%$esc%' OR rptsp_no LIKE '%$esc%')";
}
$whereSql = $whereParts ? "WHERE " . implode(" AND ", $whereParts) : "";

// --- Total rows ---
$totalRes = $mysqli->query("SELECT COUNT(*) AS cnt FROM payments_list $whereSql");
$total = $totalRes->fetch_assoc()['cnt'] ?? 0;
$total_pages = ($total > 0) ? ceil($total / $limit) : 1;

// --- Fetch payments ---
$res = $mysqli->query("
    SELECT *
    FROM payments_list
    $whereSql
    ORDER BY id DESC
    LIMIT $limit OFFSET $offset
");
?>

<div class="container-fluid mt-4">
    <h2 class="mb-4"><i class="bi bi-cash-stack"></i> Payments List</h2>

    <div class="card mb-3">
        <div class="card-header bg-dark text-white">Search</div>
        <div class="card-body">
            <form method="get" class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search by Owner, TD No, Lot No, or RPTSP" value="<?=htmlspecialchars($globalFilter)?>">
                </div>
                <div class="col-md-1 d-grid">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
                <div class="col-md-1 d-grid">
                    <a href="payments_list.php?page=1" class="btn btn-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-secondary text-white">Payments Records</div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>RPTSP No</th>
                        <th>Owner</th>
                        <th>TD No</th>
                        <th>Lot No</th>
                        <th>Tax Year</th>
                        <th>Barangay</th>
                        <th>Location</th>
                        <th>Classification</th>
                        <th>Assessed Value</th>
                        <th>Basic Tax</th>
                        <th>SEF Tax</th>
                        <th>Adjustments</th>
                        <th>Discount</th>
                        <th>Penalty</th>
                        <th>Total Due</th>
                        <th>Status</th>
                        <th>Processed By</th>
                        <th>Assessed Date</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($res && $res->num_rows > 0): ?>
                    <?php while($row = $res->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['rptsp_no']) ?></td>
                            <td><?= htmlspecialchars($row['owner_name']) ?></td>
                            <td><?= htmlspecialchars($row['td_no']) ?></td>
                            <td><?= htmlspecialchars($row['lot_no']) ?></td>
                            <td><?= $row['tax_year'] ?></td>
                            <td><?= htmlspecialchars($row['barangay']) ?></td>
                            <td><?= htmlspecialchars($row['location']) ?></td>
                            <td><?= htmlspecialchars($row['classification']) ?></td>
                            <td>₱<?= number_format((float)$row['assessed_value'],2) ?></td>
                            <td>₱<?= number_format((float)$row['basic_tax'],2) ?></td>
                            <td>₱<?= number_format((float)$row['sef_tax'],2) ?></td>
                            <td>₱<?= number_format((float)$row['adjustments'],2) ?></td>
                            <td>₱<?= number_format((float)$row['discount'],2) ?></td>
                            <td>₱<?= number_format((float)$row['penalty'],2) ?></td>
                            <td>₱<?= number_format((float)$row['total_due'],2) ?></td>
                            <td>
                                <?php if(strtolower($row['status'])=='paid'): ?>
                                    <span class="badge bg-success">Paid</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Unpaid</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($row['processed_by']) ?></td>
                            <td><?= $row['assessed_date'] ?? '-' ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="19" class="text-center text-muted">No payments found.</td></tr>
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
                            <a class="page-link" href="?page=<?=$i?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?=($page >= $total_pages)?'disabled':''?>">
                        <a class="page-link" href="?page=<?=($page+1)?>">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
