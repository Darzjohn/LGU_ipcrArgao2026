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

// q() helper
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

$current_user = $_SESSION['name'];

// fetch accounts
$bank_accounts = [];
$res = $mysqli->query("SELECT id, account_name, account_number FROM bank_accounts ORDER BY account_name ASC");
if ($res) while ($r = $res->fetch_assoc()) $bank_accounts[] = $r;

// handle add reconciliation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['br_action']) && $_POST['br_action'] === 'add_recon') {
    $account_id = intval($_POST['account_id']);
    $month = intval($_POST['month']);
    $year = intval($_POST['year']);
    $book_balance = floatval($_POST['book_balance']);
    $bank_statement_balance = floatval($_POST['bank_statement_balance']);
    $outstanding_checks = floatval($_POST['outstanding_checks']);
    $deposits_in_transit = floatval($_POST['deposits_in_transit']);
    $service_charge = floatval($_POST['service_charge']);
    $bank_adjustments = floatval($_POST['bank_adjustments']);
    // reconciled_balance computed client-side but also compute server-side to be safe
    $reconciled_balance = $book_balance + $deposits_in_transit - $outstanding_checks - $service_charge + $bank_adjustments;
    $remarks = trim($_POST['remarks'] ?? '');

    q("INSERT INTO bank_reconciliation
        (account_id, month, year, book_balance, bank_statement_balance, outstanding_checks, deposits_in_transit, service_charge, bank_adjustments, reconciled_balance, remarks, prepared_by, prepared_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
        [$account_id, $month, $year, $book_balance, $bank_statement_balance, $outstanding_checks, $deposits_in_transit, $service_charge, $bank_adjustments, $reconciled_balance, $remarks, $current_user]
    );

    echo "<script>alert('Reconciliation saved'); location.href='bank_reconciliation.php';</script>";
    exit;
}

// filters and list
$where = "WHERE 1";
$params = [];
if (!empty($_GET['filter_account'])) { $where .= " AND r.account_id = ?"; $params[] = intval($_GET['filter_account']); }
if (!empty($_GET['filter_month'])) { $where .= " AND r.month = ?"; $params[] = intval($_GET['filter_month']); }
if (!empty($_GET['filter_year'])) { $where .= " AND r.year = ?"; $params[] = intval($_GET['filter_year']); }

$sql = "SELECT r.*, a.account_name, a.account_number
        FROM bank_reconciliation r
        LEFT JOIN bank_accounts a ON a.id = r.account_id
        $where
        ORDER BY r.year DESC, r.month DESC, r.id DESC";
$stmt = q($sql, $params);
$res = $stmt->get_result();
$recons = [];
while ($r = $res->fetch_assoc()) $recons[] = $r;
$stmt->close();
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-journal-check"></i> Bank Reconciliation</h4>
        <div>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addReconModal">➕ New Reconciliation</button>
            <a href="bank_transactions.php" class="btn btn-secondary">Back to Transactions</a>
        </div>
    </div>

    <form method="get" class="row g-2 mb-3">
        <div class="col-md-3">
            <select name="filter_account" class="form-control">
                <option value="">-- All Accounts --</option>
                <?php foreach($bank_accounts as $b): ?>
                <option value="<?= $b['id'] ?>" <?= (!empty($_GET['filter_account']) && $_GET['filter_account']==$b['id'])?'selected':'' ?>>
                    <?= htmlspecialchars($b['account_name'] . ' (' . $b['account_number'] . ')') ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="filter_month" class="form-control">
                <option value="">-- Month --</option>
                <?php for($m=1;$m<=12;$m++): ?>
                <option value="<?= $m ?>" <?= (!empty($_GET['filter_month']) && $_GET['filter_month']==$m)?'selected':'' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-md-2">
            <input type="number" name="filter_year" class="form-control" placeholder="Year" value="<?= htmlspecialchars($_GET['filter_year'] ?? date('Y')) ?>">
        </div>
        <div class="col-md-2 d-grid">
            <button class="btn btn-primary">Filter</button>
        </div>
    </form>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-sm table-bordered table-hover">
                <thead class="table-dark text-center">
                    <tr>
                        <th>ID</th>
                        <th>Account</th>
                        <th>Month</th>
                        <th>Year</th>
                        <th class="text-end">Book Balance</th>
                        <th class="text-end">Bank Statement</th>
                        <th class="text-end">Outstanding Checks</th>
                        <th class="text-end">Deposits in Transit</th>
                        <th class="text-end">Service Charge</th>
                        <th class="text-end">Adjustments</th>
                        <th class="text-end">Reconciled Balance</th>
                        <th>Prepared By</th>
                        <th>Prepared At</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(!empty($recons)): foreach($recons as $r): ?>
                    <tr class="align-middle text-center">
                        <td><?= $r['id'] ?></td>
                        <td class="text-start"><?= htmlspecialchars($r['account_name'] . ' (' . $r['account_number'] . ')') ?></td>
                        <td><?= date('F', mktime(0,0,0,$r['month'],1)) ?></td>
                        <td><?= $r['year'] ?></td>
                        <td class="text-end">₱<?= number_format($r['book_balance']+0,2) ?></td>
                        <td class="text-end">₱<?= number_format($r['bank_statement_balance']+0,2) ?></td>
                        <td class="text-end">₱<?= number_format($r['outstanding_checks']+0,2) ?></td>
                        <td class="text-end">₱<?= number_format($r['deposits_in_transit']+0,2) ?></td>
                        <td class="text-end">₱<?= number_format($r['service_charge']+0,2) ?></td>
                        <td class="text-end">₱<?= number_format($r['bank_adjustments']+0,2) ?></td>
                        <td class="text-end">₱<?= number_format($r['reconciled_balance']+0,2) ?></td>
                        <td><?= htmlspecialchars($r['prepared_by']) ?></td>
                        <td><?= htmlspecialchars($r['prepared_at']) ?></td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="13" class="text-center text-muted">No reconciliations found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Recon Modal -->
<div class="modal fade" id="addReconModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form method="post" class="modal-content" id="addReconForm">
      <input type="hidden" name="br_action" value="add_recon">
      <div class="modal-header">
        <h5 class="modal-title">Add Bank Reconciliation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-2">
            <div class="col-md-6">
                <label>Account</label>
                <select name="account_id" class="form-control" required>
                    <option value="">-- Select Account --</option>
                    <?php foreach($bank_accounts as $b): ?>
                    <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['account_name'] . ' (' . $b['account_number'] . ')') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label>Month</label>
                <select name="month" class="form-control" required>
                    <?php for($m=1;$m<=12;$m++): ?>
                    <option value="<?= $m ?>" <?= ($m==date('n'))?'selected':'' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label>Year</label>
                <input type="number" name="year" class="form-control" value="<?= date('Y') ?>" required>
            </div>

            <div class="col-md-4">
                <label>Book Balance</label>
                <input type="number" step="0.01" name="book_balance" id="book_balance" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label>Bank Statement Balance</label>
                <input type="number" step="0.01" name="bank_statement_balance" id="bank_statement_balance" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label>Outstanding Checks</label>
                <input type="number" step="0.01" name="outstanding_checks" id="outstanding_checks" class="form-control" value="0">
            </div>

            <div class="col-md-4">
                <label>Deposits in Transit</label>
                <input type="number" step="0.01" name="deposits_in_transit" id="deposits_in_transit" class="form-control" value="0">
            </div>

            <div class="col-md-4">
                <label>Service Charge</label>
                <input type="number" step="0.01" name="service_charge" id="service_charge" class="form-control" value="0">
            </div>

            <div class="col-md-4">
                <label>Bank Adjustments</label>
                <input type="number" step="0.01" name="bank_adjustments" id="bank_adjustments" class="form-control" value="0">
            </div>

            <div class="col-md-6">
                <label>Reconciled Balance</label>
                <input type="text" name="reconciled_balance_display" id="reconciled_balance_display" class="form-control" readonly>
            </div>

            <div class="col-md-12">
                <label>Remarks</label>
                <textarea name="remarks" class="form-control"></textarea>
            </div>

            <div class="col-md-12 mt-2">Prepared by: <strong><?= htmlspecialchars($current_user) ?></strong></div>

        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancel</button>
        <button class="btn btn-primary" type="submit">Save Reconciliation</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<script>
function computeReconciled() {
    const book = parseFloat(document.getElementById('book_balance').value || 0);
    const deposits = parseFloat(document.getElementById('deposits_in_transit').value || 0);
    const outchecks = parseFloat(document.getElementById('outstanding_checks').value || 0);
    const service = parseFloat(document.getElementById('service_charge').value || 0);
    const adj = parseFloat(document.getElementById('bank_adjustments').value || 0);
    const reconciled = (book + deposits - outchecks - service + adj);
    document.getElementById('reconciled_balance_display').value = reconciled.toFixed(2);
}

['book_balance','deposits_in_transit','outstanding_checks','service_charge','bank_adjustments'].forEach(id=>{
    const el = document.getElementById(id);
    if(el) el.addEventListener('input', computeReconciled);
});

document.getElementById('addReconForm').addEventListener('submit', function(e){
    // ensure reconciled field has a value (server will re-calc)
    computeReconciled();
});
</script>
