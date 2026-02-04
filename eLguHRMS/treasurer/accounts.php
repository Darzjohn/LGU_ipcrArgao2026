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
        if (!$stmt) {
            // helpful debug during development
            die("SQL Prepare Error: " . $mysqli->error . "<br>Query: " . htmlspecialchars($sql));
        }

        if (!empty($params)) {
            $types = '';
            foreach ($params as $p) {
                if (is_int($p)) $types .= 'i';
                elseif (is_float($p) || is_double($p)) $types .= 'd';
                else $types .= 's';
            }
            // bind only when there are params
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt->execute()) {
            die("SQL Exec Error: " . $stmt->error);
        }
        return $stmt;
    }
}

// Add account
if (isset($_POST['add_account'])) {
    $account_name = $_POST['account_name'] ?? '';
    $account_number = $_POST['account_number'] ?? '';
    $bank_name = $_POST['bank_name'] ?? '';
    $beginning_balance = $_POST['beginning_balance'] !== '' ? floatval($_POST['beginning_balance']) : 0;

    q(
        "INSERT INTO bank_accounts (account_name, account_number, bank_name, beginning_balance, created_by, created_at)
         VALUES (?, ?, ?, ?, ?, NOW())",
        [$account_name, $account_number, $bank_name, $beginning_balance, $_SESSION['name']]
    );

    echo "<script>alert('Account added successfully'); location.href='accounts.php';</script>";
    exit;
}

// Edit account
if (isset($_POST['edit_account'])) {
    $account_id = intval($_POST['account_id']);
    $account_name = $_POST['account_name'] ?? '';
    $account_number = $_POST['account_number'] ?? '';
    $bank_name = $_POST['bank_name'] ?? '';
    $beginning_balance = $_POST['beginning_balance'] !== '' ? floatval($_POST['beginning_balance']) : 0;

    q(
        "UPDATE bank_accounts SET account_name=?, account_number=?, bank_name=?, beginning_balance=?, created_by=?, created_at=created_at WHERE id=?",
        [$account_name, $account_number, $bank_name, $beginning_balance, $_SESSION['name'], $account_id]
    );

    echo "<script>alert('Account updated successfully'); location.href='accounts.php';</script>";
    exit;
}

// Delete account
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    q("DELETE FROM bank_accounts WHERE id=?", [$del_id]);
    echo "<script>alert('Account deleted'); location.href='accounts.php';</script>";
    exit;
}

// Fetch accounts
$accounts_res = $mysqli->query("SELECT * FROM bank_accounts ORDER BY id DESC");
$accounts = [];
if ($accounts_res) {
    while ($row = $accounts_res->fetch_assoc()) $accounts[] = $row;
}
?>
<div class="container-fluid mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-bank2"></i> Bank Accounts</h4>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addAccountModal">➕ Add Account</button>
  </div>

  <div class="card">
    <div class="card-body table-responsive">
      <table class="table table-bordered table-hover">
        <thead class="table-dark text-center">
          <tr>
            <th>ID</th>
            <th>Account Name</th>
            <th>Account Number</th>
            <th>Bank Name</th>
            <th>Beginning Balance</th>
            <th>Created By</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($accounts)): foreach ($accounts as $a): ?>
          <tr class="text-center">
            <td><?= $a['id'] ?></td>
            <td><?= htmlspecialchars($a['account_name']) ?></td>
            <td><?= htmlspecialchars($a['account_number']) ?></td>
            <td><?= htmlspecialchars($a['bank_name']) ?></td>
            <td>₱<?= number_format($a['beginning_balance'], 2) ?></td>
            <td><?= htmlspecialchars($a['created_by'] ?? '') ?></td>
            <td>
              <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editAccountModal<?= $a['id'] ?>">✏️</button>
              <a href="?delete=<?= $a['id'] ?>" onclick="return confirm('Delete this account?')" class="btn btn-sm btn-danger">🗑️</a>
            </td>
          </tr>
          <?php endforeach; else: ?>
          <tr>
            <td colspan="7" class="text-center text-muted">No bank accounts found.</td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Add Account Modal -->
<div class="modal fade" id="addAccountModal" tabindex="-1" aria-labelledby="addAccountLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="addAccountLabel">➕ Add Bank Account</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <label>Account Name</label>
        <input type="text" name="account_name" class="form-control mb-2" required>
        <label>Account Number</label>
        <input type="text" name="account_number" class="form-control mb-2">
        <label>Bank Name</label>
        <input type="text" name="bank_name" class="form-control mb-2">
        <label>Beginning Balance</label>
        <input type="number" step="0.01" name="beginning_balance" class="form-control mb-2" value="0.00">
      </div>
      <div class="modal-footer">
        <button type="submit" name="add_account" class="btn btn-success">Save</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Account Modals -->
<?php foreach ($accounts as $a): ?>
<div class="modal fade" id="editAccountModal<?= $a['id'] ?>" tabindex="-1" aria-labelledby="editAccountLabel<?= $a['id'] ?>" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title" id="editAccountLabel<?= $a['id'] ?>">✏️ Edit Account</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="account_id" value="<?= $a['id'] ?>">
        <label>Account Name</label>
        <input type="text" name="account_name" class="form-control mb-2" value="<?= htmlspecialchars($a['account_name']) ?>" required>
        <label>Account Number</label>
        <input type="text" name="account_number" class="form-control mb-2" value="<?= htmlspecialchars($a['account_number']) ?>">
        <label>Bank Name</label>
        <input type="text" name="bank_name" class="form-control mb-2" value="<?= htmlspecialchars($a['bank_name']) ?>">
        <label>Beginning Balance</label>
        <input type="number" step="0.01" name="beginning_balance" class="form-control mb-2" value="<?= number_format($a['beginning_balance'],2,'.','') ?>">
      </div>
      <div class="modal-footer">
        <button type="submit" name="edit_account" class="btn btn-warning">Update</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </form>
  </div>
</div>
<?php endforeach; ?>

<?php
require_once __DIR__ . '/../layouts/footer.php';
ob_end_flush();
?>
