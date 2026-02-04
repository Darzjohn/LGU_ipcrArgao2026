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

// Smart query helper
if (!function_exists('q')) {
    function q($sql, $params = []) {
        global $mysqli;
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) die("SQL Prepare Error: " . $mysqli->error . "<br>Query: " . htmlspecialchars($sql));
        if (!empty($params)) {
            $types = '';
            foreach ($params as $p) {
                if (is_int($p)) $types .= 'i';
                elseif (is_float($p) || is_double($p)) $types .= 'd';
                else $types .= 's';
            }
            $stmt->bind_param($types, ...$params);
        }
        if (!$stmt->execute()) die("SQL Exec Error: " . $stmt->error . "<br>SQL: " . htmlspecialchars($sql));
        return $stmt;
    }
}

// Current logged user
$current_user = $_SESSION['name'];

// Fetch bank accounts
$bank_accounts = [];
$res = $mysqli->query("SELECT id, account_name, account_number FROM bank_accounts ORDER BY account_name ASC");
if ($res) while ($row = $res->fetch_assoc()) $bank_accounts[] = $row;

// Fetch fund sources
$fund_sources = [];
$resf = $mysqli->query("SELECT id, name FROM fund_source WHERE status=1 ORDER BY name ASC");
if ($resf) while ($row = $resf->fetch_assoc()) $fund_sources[] = $row;

// Save RCI
if (isset($_POST['add_rci'])) {
    $account_id = intval($_POST['account_id']);
    $fund_source_id = intval($_POST['fund_source_id'] ?? 0);
    $serial_no = trim($_POST['serial_no'] ?? '');
    $dv_payroll_no = trim($_POST['dv_payroll_no'] ?? '');
    $cafoa_no = trim($_POST['cafoa_no'] ?? '');
    $sub_allotment = trim($_POST['sub_allotment'] ?? '');
    $nature_of_payment = trim($_POST['nature_of_payment'] ?? '');
    $gross_amount = floatval($_POST['gross_amount'] ?? 0);
    $check_no = trim($_POST['check_no']);
    $payee = trim($_POST['payee']);
    $issue_date = $_POST['issue_date'];
    $amount = floatval($_POST['amount']);
    $remarks = trim($_POST['remarks'] ?? '');
    $cleared = $_POST['cleared'] ?? 'no';

    q("INSERT INTO checks_issued 
        (account_id, fund_source_id, serial_no, dv_payroll_no, cafoa_no, sub_allotment, nature_of_payment, gross_amount, check_no, payee, issue_date, amount, remarks, cleared, created_by, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
        [$account_id, $fund_source_id, $serial_no, $dv_payroll_no, $cafoa_no, $sub_allotment, $nature_of_payment, $gross_amount, $check_no, $payee, $issue_date, $amount, $remarks, $cleared, $current_user]);

    echo "<script>alert('RCI added successfully'); location.href='checks_issued.php';</script>";
    exit;
}

// Delete check
if (isset($_GET['delete'])) {
    $check_id = intval($_GET['delete']);
    q("DELETE FROM checks_issued WHERE id=?", [$check_id]);
    echo "<script>alert('Check deleted successfully'); location.href='checks_issued.php';</script>";
    exit;
}

// Filters: date and month
$where = "WHERE 1";
$params = [];
if (!empty($_GET['filter_date'])) {
    $where .= " AND issue_date = ?";
    $params[] = $_GET['filter_date'];
}
if (!empty($_GET['filter_month'])) {
    $where .= " AND MONTH(issue_date) = ?";
    $params[] = $_GET['filter_month'];
}

// Fetch checks with bank and fund info
$sql = "SELECT c.*, a.account_name, a.account_number, f.name AS fund_name 
        FROM checks_issued c 
        LEFT JOIN bank_accounts a ON a.id = c.account_id 
        LEFT JOIN fund_source f ON f.id = c.fund_source_id
        $where
        ORDER BY c.id DESC";
$stmt = q($sql, $params);
$checks = [];
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $checks[] = $row;
$stmt->close();
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-journal-check"></i> Checks Issued / RCI</h4>
        <div class="d-flex gap-2">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addRCIModal">➕ Add New RCI</button>
            <button id="btnPrintRCI" class="btn btn-primary" disabled>🖨️ Print RCI</button>
        </div>
    </div>

    <!-- Filters -->
    <form method="get" class="row g-2 mb-3">
        <div class="col-md-3">
            <input type="date" name="filter_date" class="form-control" value="<?= htmlspecialchars($_GET['filter_date'] ?? '') ?>">
        </div>
        <div class="col-md-3">
            <select name="filter_month" class="form-control">
                <option value="">-- Filter by Month --</option>
                <?php for($m=1;$m<=12;$m++): ?>
                <option value="<?= $m ?>" <?= (!empty($_GET['filter_month']) && $_GET['filter_month']==$m)?'selected':'' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-md-2 d-grid">
            <button class="btn btn-primary">Filter</button>
        </div>
        <div class="col-md-2 d-grid">
            <a href="checks_issued.php" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <div class="card">
        <div class="card-body table-responsive">
            <form id="printForm" method="post" target="_blank" action="print_rci.php">
            <table class="table table-bordered table-hover table-sm">
                <thead class="table-dark text-center">
                    <tr>
                        <th><input type="checkbox" id="checkAll"></th>
                        <th>ID</th>
                        <th>Serial No</th>
                        <th>DV/Payroll No</th>
                        <th>CAFOA No</th>
                        <th>Sub-Allotment</th>
                        <th>Nature of Payment</th>
                        <th>Gross Amount</th>
                        <th>Check No</th>
                        <th>Payee</th>
                        <th>Issue Date</th>
                        <th>Amount</th>
                        <th>Fund Source</th>
                        <th>Bank Account</th>
                        <th>Cleared</th>
                        <th>Remarks</th>
                        <th>Created By</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($checks)): foreach($checks as $c): ?>
                    <tr class="text-center align-middle">
                        <td><input type="checkbox" class="record-check" name="selected[]" value="<?= $c['id'] ?>"></td>
                        <td><?= $c['id'] ?></td>
                        <td><?= htmlspecialchars($c['serial_no']) ?></td>
                        <td><?= htmlspecialchars($c['dv_payroll_no']) ?></td>
                        <td><?= htmlspecialchars($c['cafoa_no']) ?></td>
                        <td><?= htmlspecialchars($c['sub_allotment']) ?></td>
                        <td class="text-start"><?= nl2br(htmlspecialchars($c['nature_of_payment'])) ?></td>
                        <td class="text-end">₱<?= number_format($c['gross_amount']+0,2) ?></td>
                        <td><?= htmlspecialchars($c['check_no']) ?></td>
                        <td><?= htmlspecialchars($c['payee']) ?></td>
                        <td><?= htmlspecialchars($c['issue_date']) ?></td>
                        <td class="text-end">₱<?= number_format($c['amount']+0,2) ?></td>
                        <td><?= htmlspecialchars($c['fund_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($c['account_name'] . " ({$c['account_number']})") ?></td>
                        <td><?= $c['cleared']=='yes'?'<span class="badge bg-success">YES</span>':'<span class="badge bg-danger">NO</span>' ?></td>
                        <td class="text-start"><?= nl2br(htmlspecialchars($c['remarks'])) ?></td>
                        <td><?= htmlspecialchars($c['created_by']) ?></td>
                        <td><?= htmlspecialchars($c['created_at']) ?></td>
                        <td>
    <!-- Edit Button -->
    <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editRCIModal<?= $c['id'] ?>">
        ✏️
    </button>
    <!-- Delete Button -->
    <a href="?delete=<?= $c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this check?')">
        🗑️
    </a>
</td>

                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="19" class="text-center text-muted">No checks found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/modals_check.php'; ?>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<script>
const checkAll = document.getElementById('checkAll');
const recordChecks = document.querySelectorAll('.record-check');
const btnPrintRCI = document.getElementById('btnPrintRCI');

checkAll.addEventListener('change', function() {
    recordChecks.forEach(ch => ch.checked = this.checked);
    togglePrintButton();
});

recordChecks.forEach(ch => ch.addEventListener('change', togglePrintButton));

function togglePrintButton() {
    btnPrintRCI.disabled = document.querySelectorAll('.record-check:checked').length === 0;
}

// New: submit selected checkboxes via POST to print_rci.php
btnPrintRCI.addEventListener('click', function() {
    const selected = Array.from(document.querySelectorAll('.record-check:checked')).map(ch => ch.value);
    if(selected.length === 0) { 
        alert("Select at least one RCI"); 
        return; 
    }

    // Create a temporary form
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'print_rci.php';
    form.target = '_blank'; // open in new tab

    selected.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'selected[]';
        input.value = id;
        form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
});
</script>


<?php ob_end_flush(); ?>
