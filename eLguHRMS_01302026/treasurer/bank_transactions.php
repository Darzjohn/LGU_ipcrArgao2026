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

// q() helper if not present
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

// current user full name
$current_user = $_SESSION['name'];

// fetch bank accounts
$bank_accounts = [];
$res = $mysqli->query("SELECT id, account_name, account_number FROM bank_accounts ORDER BY account_name ASC");
if ($res) while ($r = $res->fetch_assoc()) $bank_accounts[] = $r;

// handle add transaction
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bt_action']) && $_POST['bt_action'] === 'add_transaction') {
    $account_id = intval($_POST['account_id']);
    $transaction_type = $_POST['transaction_type'];
    $reference_no = trim($_POST['reference_no'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);
    $transfer_to_account_id = null;
    if ($transaction_type === 'transfer') {
        $transfer_to_account_id = intval($_POST['transfer_to_account_id']);
    }
    $transaction_date = $_POST['transaction_date'] ?: date('Y-m-d');

    q("INSERT INTO bank_transactions
        (account_id, transaction_type, reference_no, description, amount, transfer_to_account_id, transaction_date, created_by, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())",
        [$account_id, $transaction_type, $reference_no, $description, $amount, $transfer_to_account_id, $transaction_date, $current_user]
    );

    // if transfer: create corresponding opposite entry (optional) - we'll insert a mirrored transaction for the destination account
    if ($transaction_type === 'transfer' && $transfer_to_account_id) {
        // mirrored: withdrawal from source already recorded above as transfer type;
        // also insert a deposit record for destination for audit
        q("INSERT INTO bank_transactions
            (account_id, transaction_type, reference_no, description, amount, transfer_to_account_id, transaction_date, created_by, created_at)
            VALUES (?, 'deposit', ?, ?, ?, NULL, ?, ?, NOW())",
            [$transfer_to_account_id, 'TR from A#' . (int)$account_id . ' ref:' . $reference_no, $description, $amount, $transaction_date, $current_user]
        );
    }

    echo "<script>alert('Transaction saved'); location.href='bank_transactions.php';</script>";
    exit;
}

// handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    q("DELETE FROM bank_transactions WHERE id=?", [$id]);
    echo "<script>alert('Transaction deleted'); location.href='bank_transactions.php';</script>";
    exit;
}

// filters
$where = "WHERE 1";
$params = [];
if (!empty($_GET['filter_account'])) {
    $where .= " AND t.account_id = ?";
    $params[] = intval($_GET['filter_account']);
}
if (!empty($_GET['filter_type'])) {
    $where .= " AND t.transaction_type = ?";
    $params[] = $_GET['filter_type'];
}
if (!empty($_GET['filter_date'])) {
    $where .= " AND t.transaction_date = ?";
    $params[] = $_GET['filter_date'];
}
if (!empty($_GET['filter_month'])) {
    $where .= " AND MONTH(t.transaction_date) = ?";
    $params[] = intval($_GET['filter_month']);
}
if (!empty($_GET['filter_year'])) {
    $where .= " AND YEAR(t.transaction_date) = ?";
    $params[] = intval($_GET['filter_year']);
}

// fetch transactions
$sql = "SELECT t.*, a.account_name, a.account_number, a2.account_name AS to_account_name
        FROM bank_transactions t
        LEFT JOIN bank_accounts a ON a.id = t.account_id
        LEFT JOIN bank_accounts a2 ON a2.id = t.transfer_to_account_id
        $where
        ORDER BY t.transaction_date DESC, t.id DESC";
$stmt = q($sql, $params);
$res = $stmt->get_result();
$transactions = [];
while ($r = $res->fetch_assoc()) $transactions[] = $r;
$stmt->close();
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-bank"></i> Bank Transactions</h4>
        <div>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addTransactionModal">➕ Add Transaction</button>
            <a href="bank_reconciliation.php" class="btn btn-secondary">Bank Reconciliation</a>
        </div>
    </div>

    <!-- Filters -->
    <form method="get" class="row g-2 mb-3">
        <div class="col-md-2">
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
            <select name="filter_type" class="form-control">
                <option value="">-- All Types --</option>
                <option value="deposit" <?= (($_GET['filter_type']??'')=='deposit')?'selected':'' ?>>Deposit</option>
                <option value="withdrawal" <?= (($_GET['filter_type']??'')=='withdrawal')?'selected':'' ?>>Withdrawal</option>
                <option value="transfer" <?= (($_GET['filter_type']??'')=='transfer')?'selected':'' ?>>Transfer</option>
            </select>
        </div>
        <div class="col-md-2">
            <input type="date" name="filter_date" class="form-control" value="<?= htmlspecialchars($_GET['filter_date'] ?? '') ?>">
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
            <input type="number" name="filter_year" class="form-control" placeholder="Year" value="<?= htmlspecialchars($_GET['filter_year'] ?? '') ?>">
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
                        <th>Type</th>
                        <th>Reference</th>
                        <th>Description</th>
                        <th>Date</th>
                        <th class="text-end">Amount</th>
                        <th>Transfer To</th>
                        <th>Created By</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(!empty($transactions)): foreach($transactions as $t): ?>
                    <tr class="align-middle text-center">
                        <td><?= $t['id'] ?></td>
                        <td class="text-start"><?= htmlspecialchars($t['account_name'] . ' (' . $t['account_number'] . ')') ?></td>
                        <td><?= strtoupper($t['transaction_type']) ?></td>
                        <td><?= htmlspecialchars($t['reference_no']) ?></td>
                        <td class="text-start"><?= nl2br(htmlspecialchars($t['description'])) ?></td>
                        <td><?= htmlspecialchars($t['transaction_date']) ?></td>
                        <td class="text-end">₱<?= number_format($t['amount']+0,2) ?></td>
                        <td><?= htmlspecialchars($t['to_account_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($t['created_by']) ?></td>
                        <td><?= htmlspecialchars($t['created_at']) ?></td>
                        <td>
                            <a href="?delete=<?= $t['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this transaction?')">🗑️</a>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="11" class="text-center text-muted">No transactions found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Transaction Modal -->
<div class="modal fade" id="addTransactionModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form method="post" class="modal-content" id="addTransactionForm">
      <input type="hidden" name="bt_action" value="add_transaction">
      <div class="modal-header">
        <h5 class="modal-title">Add Bank Transaction</h5>
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
            <div class="col-md-6">
                <label>Transaction Type</label>
                <select name="transaction_type" id="transaction_type" class="form-control" required>
                    <option value="deposit">Deposit</option>
                    <option value="withdrawal">Withdrawal</option>
                    <option value="transfer">Transfer</option>
                </select>
            </div>
            <div class="col-md-6" id="transferToWrapper" style="display:none;">
                <label>Transfer To (destination account)</label>
                <select name="transfer_to_account_id" class="form-control" id="transfer_to_account_id">
                    <option value="">-- Select Destination --</option>
                    <?php foreach($bank_accounts as $b): ?>
                    <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['account_name'] . ' (' . $b['account_number'] . ')') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label>Reference No</label>
                <input type="text" name="reference_no" class="form-control">
            </div>
            <div class="col-md-12">
                <label>Description</label>
                <textarea name="description" class="form-control"></textarea>
            </div>
            <div class="col-md-4">
                <label>Amount</label>
                <input type="number" name="amount" step="0.01" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label>Transaction Date</label>
                <input type="date" name="transaction_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <div>Created by: <strong><?= htmlspecialchars($current_user) ?></strong></div>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancel</button>
        <button class="btn btn-primary" type="submit">Save Transaction</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<script>
document.getElementById('transaction_type').addEventListener('change', function(){
    const tr = this.value === 'transfer';
    document.getElementById('transferToWrapper').style.display = tr ? 'block' : 'none';
});

// optional: client-side validation when submitting
document.getElementById('addTransactionForm').addEventListener('submit', function(e){
    const type = document.querySelector('[name="transaction_type"]').value;
    if (type === 'transfer') {
        const dest = document.querySelector('[name="transfer_to_account_id"]').value;
        const src = document.querySelector('[name="account_id"]').value;
        if (!dest) { alert('Select destination account for transfer'); e.preventDefault(); return; }
        if (dest === src) { alert('Cannot transfer to same account'); e.preventDefault(); return; }
    }
});
</script>
