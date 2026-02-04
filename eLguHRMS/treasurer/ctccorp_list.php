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
if (!in_array($_SESSION['role'], ['admin', 'treasurer', 'cashier'])) {
    header("Location: ../index.php");
    exit;
}

// Smart query helper
if (!function_exists('q')) {
    function q($sql, $params = []) {
        global $mysqli;
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) die("SQL Prepare Error: " . $mysqli->error . "<br>Query: $sql");
        if (!empty($params)) {
            $types = '';
            foreach ($params as $p) {
                if (is_int($p)) $types .= 'i';
                elseif (is_float($p) || is_double($p)) $types .= 'd';
                else $types .= 's';
            }
            $stmt->bind_param($types, ...$params);
        }
        if (!$stmt->execute()) die("SQL Exec Error: " . $stmt->error . "<br>Query: $sql");
        return $stmt;
    }
}


// Handle Daily CTC Corporation Remittance
if (isset($_POST['daily_ctc_corp_remittance']) && !empty($_POST['remittance_date'])) {
    $selected_date = $_POST['remittance_date'];
    $current_user = $_SESSION['name'] ?? '';
    $today = date('Y-m-d');
    $form_no = "907"; // CTC Corporation form_no

    // Check if remittance already exists for this date & collector
    $check_stmt = $mysqli->prepare("SELECT COUNT(*) AS count FROM remittance WHERE date_paid = ? AND form_no = ? AND created_by = ?");
    $check_stmt->bind_param('sss', $selected_date, $form_no, $current_user);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result()->fetch_assoc();

    if ($check_result['count'] > 0) {
        echo "<script>alert('This Date is Already Remitted'); location.href='ctccorp_list.php?date_issued=$selected_date';</script>";
        exit;
    }

    // Fetch all CTC Corporation records for the selected date by the current user
    $stmt = $mysqli->prepare("SELECT ctccorp_no, date_issued, total_due, created_by FROM ctc_corporation WHERE DATE(date_issued) = ? AND created_by = ?");
    $stmt->bind_param('ss', $selected_date, $current_user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $insert_stmt = $mysqli->prepare("INSERT INTO remittance (form_no, or_no, total_paid, date_paid, created_by, remittance_date) VALUES (?, ?, ?, ?, ?, ?)");
        while ($row = $result->fetch_assoc()) {
            $or_no = $row['ctccorp_no'];
            $total_paid = $row['total_due'];
            $date_paid = $row['date_issued'];
            $created_by = $row['created_by'];
            $insert_stmt->bind_param('ssdsss', $form_no, $or_no, $total_paid, $date_paid, $created_by, $today);
            $insert_stmt->execute();
        }
        $insert_stmt->close();
        echo "<script>alert('Daily CTC Corporation Remittance successfully created!'); location.href='ctccorp_list.php?date_issued=$selected_date';</script>";
        exit;
    } else {
        echo "<script>alert('No CTC Corporation records found for this date.');</script>";
    }
}


// Initialize
$ctccorp_records = [];

// ===== Handle Add CTC Corporation with duplicate check =====
if (isset($_POST['add_ctccorp'])) {
    $stmt = q("SELECT COUNT(*) as cnt FROM ctc_corporation WHERE ctccorp_no=? AND year=?", [$_POST['ctccorp_no'], $_POST['year']]);
    $res = $stmt->get_result()->fetch_assoc();
    if ($res['cnt'] > 0) {
        echo "<script>alert('❌ Corporation CTC number already exists for this year.'); location.href='ctccorp_list.php';</script>";
        exit;
    }

    q(
        "INSERT INTO ctc_corporation (
            ctccorp_no, year, place_of_issue, date_issued,
            company_fullname, business_address, kind_of_org, nature_of_business, incorporation_address, date_reg,
            rpt_assessedvalue, gross_receipts, basic_tax, additional_tax, rpt_tax_due, gr_tax_due, surcharge, total_due,
            position_authorizedsig, treasurer, created_by, created_at
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())",
        [
            $_POST['ctccorp_no'], $_POST['year'], $_POST['place_of_issue'], $_POST['date_issued'],
            $_POST['company_fullname'], $_POST['business_address'], $_POST['kind_of_org'], $_POST['nature_of_business'], $_POST['incorporation_address'], $_POST['date_reg'],
            $_POST['rpt_assessedvalue'], $_POST['gross_receipts'], $_POST['basic_tax'], $_POST['additional_tax'], $_POST['rpt_tax_due'], $_POST['gr_tax_due'], $_POST['surcharge'], $_POST['total_due'],
            $_POST['position_authorizedsig'], $_SESSION['name'], $_SESSION['name']
        ]
    );
    echo "<script>alert('✅ Corporation CTC added successfully!'); location.href='ctccorp_list.php';</script>";
    exit;
}

// ===== Handle Edit =====
if (isset($_POST['edit_ctccorp'])) {
    q(
        "UPDATE ctc_corporation SET
            ctccorp_no=?, year=?, place_of_issue=?, date_issued=?,
            company_fullname=?, business_address=?, kind_of_org=?, nature_of_business=?, incorporation_address=?, date_reg=?,
            rpt_assessedvalue=?, gross_receipts=?, basic_tax=?, additional_tax=?, rpt_tax_due=?, gr_tax_due=?, surcharge=?, total_due=?,
            position_authorizedsig=?, updated_by=?, updated_at=NOW()
         WHERE id=?",
        [
            $_POST['ctccorp_no'], $_POST['year'], $_POST['place_of_issue'], $_POST['date_issued'],
            $_POST['company_fullname'], $_POST['business_address'], $_POST['kind_of_org'], $_POST['nature_of_business'], $_POST['incorporation_address'], $_POST['date_reg'],
            $_POST['rpt_assessedvalue'], $_POST['gross_receipts'], $_POST['basic_tax'], $_POST['additional_tax'], $_POST['rpt_tax_due'], $_POST['gr_tax_due'], $_POST['surcharge'], $_POST['total_due'],
            $_POST['position_authorizedsig'], $_SESSION['name'], $_POST['ctccorp_id']
        ]
    );
    echo "<script>alert('✏️ Corporation CTC updated successfully!'); location.href='ctccorp_list.php';</script>";
    exit;
}

// ===== Handle Delete =====
if (isset($_GET['delete'])) {
    q("DELETE FROM ctc_corporation WHERE id=?", [intval($_GET['delete'])]);
    echo "<script>alert('🗑️ Corporation CTC deleted successfully!'); location.href='ctccorp_list.php';</script>";
    exit;
}

// ===== Search / Filter =====
$where = "WHERE 1";
$params = [];
if (!empty($_GET['search'])) {
    $where .= " AND (company_fullname LIKE ? OR ctccorp_no LIKE ? OR created_by LIKE ?)";
    $search = "%" . $_GET['search'] . "%";
    array_push($params, $search, $search, $search);
}
if (!empty($_GET['year'])) {
    $where .= " AND year = ?";
    array_push($params, $_GET['year']);
}
if (!empty($_GET['date_issued'])) {
    $where .= " AND date_issued = ?";
    array_push($params, $_GET['date_issued']);
}

// Fetch records
$sql = "SELECT * FROM ctc_corporation $where ORDER BY id DESC";
$stmt = q($sql, $params);
$res = $stmt->get_result();
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) $ctccorp_records[] = $row;
}

// Last CTC no suggestion
$last_ctccorp = $mysqli->query("SELECT ctccorp_no FROM ctc_corporation ORDER BY id DESC LIMIT 1")->fetch_assoc()['ctccorp_no'] ?? '';
$suggest_ctccorp_no = $last_ctccorp ? ((int)$last_ctccorp + 1) : 1;
?>

<div class="container-fluid mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-building"></i> Community Tax Certificates - Corporations</h4>
    <div class="d-flex gap-2 mb-3">
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCTCCorpModal">➕ New CTC Corporation</button>

    <?php if (!empty($_GET['date_issued'])): ?>
        <!-- Daily CTC Corporation Remittance Button -->
        <form method="post" style="display:inline;">
            <input type="hidden" name="remittance_date" value="<?= htmlspecialchars($_GET['date_issued']) ?>">
            <button type="submit" name="daily_ctc_corp_remittance" class="btn btn-primary">
                💰 Daily CTC Corporation Remittance
            </button>
        </form>

        <!-- Print CTC Corporation Daily Abstract -->
        <a href="print_ctccorp_daily_abstract.php?date=<?= urlencode($_GET['date_issued']) ?>" 
           target="_blank" 
           class="btn btn-outline-primary">
           🖨️ Print CTC Corporation Daily Abstract
        </a>
    <?php else: ?>
        <button class="btn btn-primary" disabled>💰 Daily CTC Corporation Remittance</button>
        <button class="btn btn-outline-primary" disabled>🖨️ Print CTC Corporation Daily Abstract</button>
    <?php endif; ?>
</div>


    <!-- <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCTCCorpModal">➕ New CTC</button> -->
  </div>

  <!-- Search & Filter -->
  <form class="row mb-3" method="get">
    <div class="col-md-4">
      <input type="text" name="search" class="form-control" placeholder="Search by CTC No. or Company Name or Cashier/Officer"
             value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
    </div>
    <div class="col-md-2">
      <input type="number" name="year" class="form-control" placeholder="Year"
             value="<?= htmlspecialchars($_GET['year'] ?? '') ?>">
    </div>
    <div class="col-md-2">
      <input type="date" name="date_issued" class="form-control"
             value="<?= htmlspecialchars($_GET['date_issued'] ?? '') ?>">
    </div>
    <div class="col-md-2">
      <button class="btn btn-primary w-100">Filter</button>
    </div>
    <div class="col-md-2">
      <a href="ctccorp_list.php" class="btn btn-secondary w-100">Reset</a>
    </div>
  </form>

  <!-- Data Table -->
  <div class="card">
    <div class="card-body table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-dark text-center">
          <tr>
            <th>ID</th><th>CTC No</th><th>Company</th><th>Kind</th><th>Year</th><th>Basic</th>
            <th>GR Tax</th><th>RPT Tax</th><th>Surcharge</th><th>Total Due</th><th>Date Issued</th><th>Collecting Officer</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($ctccorp_records)): foreach ($ctccorp_records as $r): ?>
          <tr class="text-center">
            <td><?= $r['id'] ?></td>
            <td><strong><?= htmlspecialchars($r['ctccorp_no']) ?></strong></td>
            <td><strong><?= htmlspecialchars($r['company_fullname']) ?></strong></td>
            <td><?= htmlspecialchars($r['kind_of_org']) ?></td>
            <td><?= htmlspecialchars($r['year']) ?></td>
            <td>₱<?= number_format($r['basic_tax'], 2) ?></td>
            <td>₱<?= number_format($r['gr_tax_due'], 2) ?></td>
            <td>₱<?= number_format($r['rpt_tax_due'], 2) ?></td>
            <td>₱<?= number_format($r['surcharge'], 2) ?></td>
            <td><strong>₱<?= number_format($r['total_due'], 2) ?></strong></td>
            <td><strong><?= htmlspecialchars($r['date_issued']) ?></strong></td>
            <td><strong><?= htmlspecialchars($r['created_by']) ?></strong></td>
            <td>
              <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editCTCCorpModal<?= $r['id'] ?>">✏️</button>
              <a href="?delete=<?= $r['id'] ?>" onclick="return confirm('Delete this record?')" class="btn btn-sm btn-danger">🗑️</a>
              <a href="print_ctc_corporation.php?id=<?= $r['id'] ?>" target="_blank" class="btn btn-sm btn-primary" title="Print CTC">🖨️</a>
            </td>
          </tr>
          <?php endforeach; else: ?>
          <tr><td colspan="13" class="text-center text-muted">No Corporation CTC records found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/ctccorp_modals.php'; ?>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<script>
document.addEventListener("DOMContentLoaded", function() {
  const addCtcNoInput = document.querySelector("#addCTCCorpModal input[name='ctccorp_no']");
  if (addCtcNoInput) addCtcNoInput.value = "<?= $suggest_ctccorp_no ?>";
});
</script>
<?php ob_end_flush(); ?>
