<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';

// Only admin, assessor, or treasurer can view
if(!in_array($_SESSION['role'], ['admin','assessor','treasurer'])) {
    header("Location: ../index.php");
    exit;
}

// Pagination setup
$limit = 50;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Filters
$search = $_GET['search'] ?? '';
$where = '';
if ($search !== '') {
    $esc = $mysqli->real_escape_string($search);
    $where = "WHERE (owner_name LIKE '%$esc%' OR td_no LIKE '%$esc%' OR lot_no LIKE '%$esc%' OR rptsp_no LIKE '%$esc%' OR or_no LIKE '%$esc%')";
}

// Count total
$countRes = $mysqli->query("SELECT COUNT(*) AS cnt FROM collections $where");
$total = $countRes ? $countRes->fetch_assoc()['cnt'] : 0;
$total_pages = ($total > 0) ? ceil($total / $limit) : 1;

// Fetch records
$res = $mysqli->query("
    SELECT * FROM collections
    $where
    ORDER BY id DESC
    LIMIT $limit OFFSET $offset
");
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="bi bi-journal-text"></i> Collections</h2>
        <button id="btnReprint" class="btn btn-success" disabled>
            <i class="bi bi-printer"></i> Reprint OR
        </button>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-dark text-white">Search</div>
        <div class="card-body">
            <form method="get" class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search by Owner, TD No, Lot No, OR No" value="<?=htmlspecialchars($search)?>">
                </div>
                <div class="col-md-1 d-grid">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
                <div class="col-md-1 d-grid">
                    <a href="collection.php?page=1" class="btn btn-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
            <span>Collection Records</span>
            <div>
                <input type="checkbox" id="checkAll" class="form-check-input me-1"> 
                <label for="checkAll">Check All (Visible)</label>
            </div>
        </div>
        <div class="card-body table-responsive">
            <form id="reprintForm" method="post" action="official_receipt.php" target="_blank">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-dark text-center">
                        <tr>
                            <th></th>
                            <th>ID</th>
                            <th>OR No</th>
                            <th>Owner</th>
                            <th>TD No</th>
                            <th>Lot No</th>
                            <th>Tax Year</th>
                            <th>Assessed Value</th>
                            <th>Basic Tax</th>
                            <th>SEF Tax</th>
                            <th>Discount</th>
                            <th>Penalty</th>
                            <th>Total Due</th>
                            <th>Payment Date</th>
                            <th>Payor</th>
                            <th>Processed By</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($res && $res->num_rows > 0): ?>
                            <?php while($row = $res->fetch_assoc()): ?>
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" name="selected[]" value="<?= htmlspecialchars($row['id']) ?>" class="form-check-input record-check">
                                    </td>
                                    <td><?= $row['id'] ?></td>
                                    <td><?= htmlspecialchars($row['or_no']) ?></td>
                                    <td><?= htmlspecialchars($row['owner_name']) ?></td>
                                    <td><?= htmlspecialchars($row['td_no']) ?></td>
                                    <td><?= htmlspecialchars($row['lot_no']) ?></td>
                                    <td><?= htmlspecialchars($row['tax_year']) ?></td>
                                    <td>₱<?= number_format((float)$row['assessed_value'], 2) ?></td>
                                    <td>₱<?= number_format((float)$row['basic_tax'], 2) ?></td>
                                    <td>₱<?= number_format((float)$row['sef_tax'], 2) ?></td>
                                    <td>₱<?= number_format((float)$row['discount'], 2) ?></td>
                                    <td>₱<?= number_format((float)$row['penalty'], 2) ?></td>
                                    <td>₱<?= number_format((float)$row['total_due'], 2) ?></td>
                                    <td><?= htmlspecialchars($row['payment_date'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['payor_name'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['processed_by'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['status'] ?? '-') ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="17" class="text-center text-muted">No records found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </form>

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
</div>

<script>
document.getElementById("checkAll").addEventListener("change", function() {
    const checks = document.querySelectorAll(".record-check");
    checks.forEach(ch => ch.checked = this.checked);
    toggleButton();
});

document.querySelectorAll(".record-check").forEach(ch => {
    ch.addEventListener("change", toggleButton);
});

function toggleButton() {
    const anyChecked = document.querySelectorAll(".record-check:checked").length > 0;
    document.getElementById("btnReprint").disabled = !anyChecked;
}

// ✅ Reprint selected ORs
document.getElementById("btnReprint").addEventListener("click", function() {
    const form = document.getElementById("reprintForm");
    const selected = document.querySelectorAll(".record-check:checked");
    if (selected.length === 0) {
        alert("Please select at least one record to reprint.");
        return;
    }

    // Create FormData manually to ensure only checked items are sent
    const formData = new FormData();
    selected.forEach(ch => formData.append('selected[]', ch.value));

    // Open OR report in a new tab
    const tempForm = document.createElement('form');
    tempForm.method = 'POST';
    tempForm.target = '_blank';
    tempForm.action = 'official_receipt.php';
    selected.forEach(ch => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'selected[]';
        input.value = ch.value;
        tempForm.appendChild(input);
    });
    document.body.appendChild(tempForm);
    tempForm.submit();
    document.body.removeChild(tempForm);
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
