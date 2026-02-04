<?php
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';

// Role restriction
if (!in_array($_SESSION['role'], ['admin','treasurer','cashier'])) {
    header("Location: ../index.php");
    exit;
}

// Helper function for prepared statements
function q($sql, $params = []) {
    global $mysqli;
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) die("SQL Prepare Error: " . $mysqli->error);

    if (!empty($params)) {
        $types = '';
        foreach ($params as $p) {
            if (is_int($p)) $types .= 'i';
            elseif (is_float($p) || is_double($p)) $types .= 'd';
            else $types .= 's';
        }
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) die("SQL Exec Error: " . $stmt->error);
    return $stmt;
}

// Handle Delete
if (isset($_GET['delete'])) {
    q("DELETE FROM remittance WHERE id=?", [intval($_GET['delete'])]);
    echo "<script>alert('Remittance record deleted successfully!'); location.href='remittance_list.php';</script>";
    exit;
}

// Filters
$where = "WHERE 1";
$params = [];

// Filters
$where = "WHERE 1";
$params = [];

if (!empty($_GET['search'])) {
    $where .= " AND (or_no LIKE ? OR created_by LIKE ?)";
    $search = "%" . $_GET['search'] . "%";
    $params[] = $search;
    $params[] = $search;
}

if (!empty($_GET['remittance_date'])) {
    $where .= " AND remittance_date = ?";
    $params[] = $_GET['remittance_date'];
}

$sql = "SELECT * FROM remittance $where ORDER BY remittance_date DESC, id DESC";

$stmt = q($sql, $params);
$records = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
       <h4><i class="bi bi-file-text"></i> Remittance Records</h4>

<?php
// Determine if button should be enabled
$selectedDate = $_GET['remittance_date'] ?? '';
$currentUser = $_SESSION['name'] ?? '';

$hasUserRecords = false;
if (!empty($records)) {
    foreach ($records as $rec) {
        if ($rec['created_by'] === $currentUser) {
            $hasUserRecords = true;
            break;
        }
    }
}
?>
<div>
    <!-- Print RCD Per Collector (unchanged logic) -->
    <?php if ($selectedDate && $hasUserRecords): ?>
        <a href="print_rcd_percollector.php?date=<?= urlencode($selectedDate) ?>&collector=<?= urlencode($currentUser) ?>"
           target="_blank"
           class="btn btn-success ms-2">
           🖨️ Print RCD Per Collector
        </a>
    <?php else: ?>
        <button class="btn btn-success ms-2" disabled>🖨️ Print RCD Per Collector</button>
    <?php endif; ?>

    <!-- 🔵 NEW BUTTON: Print ALL RCD Per Day -->
    <?php if ($selectedDate): ?>
        <a href="print_rcd_perday.php?date=<?= urlencode($selectedDate) ?>"
           target="_blank"
           class="btn btn-primary ms-2">
           📅 Print ALL RCD Per Day
        </a>
    <?php else: ?>
        <button class="btn btn-primary ms-2" disabled>📅 Print ALL RCD Per Day</button>
    <?php endif; ?>
</div>


        
    </div>

    <!-- Search / Filter -->
    <form class="row mb-3" method="get">
    <div class="col-md-4">
        <input type="text" name="search" class="form-control" placeholder="Search OR No, Created By"
               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
    </div>
    <div class="col-md-2">
        <input type="date" name="remittance_date" class="form-control"
               value="<?= htmlspecialchars($_GET['remittance_date'] ?? '') ?>">
    </div>
    <div class="col-md-2">
        <button class="btn btn-primary w-100">Filter</button>
    </div>
    <div class="col-md-2">
        <a href="remittance_list.php" class="btn btn-secondary w-100">Reset</a>
    </div>
</form>

    <!-- Table -->
    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-dark text-center">
                    <tr>
                        <th>ID</th>
                        <th>Form No</th>
                        <th>OR No</th>
                        <th>Total Paid</th>
                        <th>Date Paid</th>
                        <th>Remittance Date</th>
                        <th>Created By</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($records)): foreach ($records as $r): ?>
                        <tr class="text-center">
                            <td><?= $r['id'] ?></td>
                            <td><?= htmlspecialchars($r['form_no']) ?></td>
                            <td><?= htmlspecialchars($r['or_no']) ?></td>
                            <td>₱<?= number_format($r['total_paid'], 2) ?></td>
                            <td><?= htmlspecialchars($r['date_paid']) ?></td>
                            <td><?= htmlspecialchars($r['remittance_date']) ?></td>
                            <td><?= htmlspecialchars($r['created_by']) ?></td>
                            <td><?= htmlspecialchars($r['created_at']) ?></td>
                            <td>
                                <a href="?delete=<?= $r['id'] ?>" onclick="return confirm('Delete this record?')" class="btn btn-sm btn-danger">🗑️</a>
                            </td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="9" class="text-center text-muted">No remittance records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
<?php ob_end_flush(); ?>
