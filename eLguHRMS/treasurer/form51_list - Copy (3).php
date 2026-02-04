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
}

// Initialize
$form51_records = [];

// Handle Add
if (isset($_POST['add_form51'])) {
    $particulars = $_POST['particulars'] ?? ''; // Prevent undefined index error

    q(
        "INSERT INTO form51 (or_no, date_issued, payor_name, address, payment_mode, total_cash_paid, check_number, bank_name, check_date, treasurer, grand_total, created_by, created_at)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,NOW())",
        [
            $_POST['or_no'], $_POST['date_issued'], $_POST['payor_name'], $_POST['address'] ?? '',
            $_POST['payment_mode'] ?? 'cash', $_POST['total_cash_paid'] ?? 0, $_POST['check_number'] ?? '',
            $_POST['bank_name'] ?? '', $_POST['check_date'] ?? null, $_SESSION['name'],
            $_POST['grand_total'] ?? 0, $_SESSION['name']
        ]
    );
    echo "<script>alert('Form 51 added successfully!'); location.href='form51_list.php';</script>";
    exit;
}

// Handle Edit
if (isset($_POST['edit_form51'])) {
    $particulars = $_POST['particulars'] ?? ''; // Prevent undefined index error

    q(
        "UPDATE form51 
         SET or_no=?, date_issued=?, payor_name=?, address=?, payment_mode=?, total_cash_paid=?, check_number=?, bank_name=?, check_date=?, treasurer=?, grand_total=?, updated_by=?, updated_at=NOW()
         WHERE id=?",
        [
            $_POST['or_no'], $_POST['date_issued'], $_POST['payor_name'], $_POST['address'] ?? '',
            $_POST['payment_mode'] ?? 'cash', $_POST['total_cash_paid'] ?? 0, $_POST['check_number'] ?? '',
            $_POST['bank_name'] ?? '', $_POST['check_date'] ?? null, $_SESSION['name'],
            $_POST['grand_total'] ?? 0, $_SESSION['name'], $_POST['form51_id']
        ]
    );
    echo "<script>alert('Form 51 updated successfully!'); location.href='form51_list.php';</script>";
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    q("DELETE FROM form51 WHERE id=?", [intval($_GET['delete'])]);
    echo "<script>alert('Form 51 deleted successfully!'); location.href='form51_list.php';</script>";
    exit;
}

// Search / Filter
$where = "WHERE 1";
$params = [];

if (!empty($_GET['search'])) {
    $where .= " AND (or_no LIKE ? OR payor_name LIKE ?)";
    $search = "%" . $_GET['search'] . "%";
    array_push($params, $search, $search);
}

if (!empty($_GET['date_issued'])) {
    $where .= " AND date_issued = ?";
    array_push($params, $_GET['date_issued']);
}

$sql = "SELECT * FROM form51 $where ORDER BY id DESC";
$stmt = q($sql, $params);
$res = $stmt->get_result();
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) $form51_records[] = $row;
}
?>

<div class="container-fluid mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-receipt"></i> Form 51 Records</h4>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addForm51Modal">➕ New Form 51</button>
  </div>

  <!-- Search & Filter -->
  <form class="row mb-3" method="get">
    <div class="col-md-4">
      <input type="text" name="search" class="form-control" placeholder="Search by OR No or Payor Name"
             value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
    </div>
    <div class="col-md-3">
      <input type="date" name="date_issued" class="form-control"
             value="<?= htmlspecialchars($_GET['date_issued'] ?? '') ?>">
    </div>
    <div class="col-md-2">
      <button class="btn btn-primary w-100">Filter</button>
    </div>
    <div class="col-md-2">
      <a href="form51_list.php" class="btn btn-secondary w-100">Reset</a>
    </div>
  </form>

  <!-- Data Table -->
  <div class="card">
    <div class="card-body table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-dark text-center">
          <tr>
            <th>ID</th><th>OR No</th><th>Payor Name</th><th>Address</th><th>Grand Total</th><th>Date Issued</th><th>Cashier/Treasurer</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($form51_records)): foreach ($form51_records as $r): ?>
          <tr class="text-center">
            <td><?= $r['id'] ?></td>
            <td><strong><?= htmlspecialchars($r['or_no']) ?></strong></td>
            <td><?= htmlspecialchars($r['payor_name']) ?></td>
            <td><?= htmlspecialchars($r['address']) ?></td>
            <td>₱<?= number_format($r['grand_total'],2) ?></td>
            <td><?= htmlspecialchars($r['date_issued']) ?></td>
            <td><?= htmlspecialchars($r['treasurer'] ?? $_SESSION['name']) ?></td>
            <td>
              <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $r['id'] ?>">✏️</button>
              <a href="?delete=<?= $r['id'] ?>" onclick="return confirm('Delete this record?')" class="btn btn-sm btn-danger">🗑️</a>
              <a href="print_form51.php?id=<?= $r['id'] ?>" target="_blank" class="btn btn-sm btn-primary">🖨️</a>
            </td>
          </tr>
          <?php endforeach; else: ?>
          <tr><td colspan="8" class="text-center text-muted">No Form 51 records found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Load Modals -->
<?php require_once __DIR__ . '/form51_modals.php'; ?>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<?php ob_end_flush(); ?>
