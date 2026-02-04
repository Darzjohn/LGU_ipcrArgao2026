<?php
ob_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';

if (!in_array($_SESSION['role'], ['admin', 'treasurer', 'cashier'])) {
    header("Location: ../index.php");
    exit;
}

if (!function_exists('q')) {
    function q($sql, $params = []) {
        global $mysqli;
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) die("SQL Prepare Error: " . $mysqli->error);
        if ($params) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt;
    }
}

// ==================== AJAX: Check Duplicate CTC Number ====================
if (isset($_GET['check_ctccorp_no'])) {
    $ctc_no = $_GET['check_ctccorp_no'];

    $stmt = q("SELECT ctccorp_no FROM ctc_corporation WHERE ctccorp_no = ?", [$ctc_no]);
    $res = $stmt->get_result();
    $exists = $res->num_rows > 0;

    $suggested = $ctc_no;
    if ($exists) {
        if (preg_match('/(\d+)$/', $ctc_no, $matches)) {
            $num = intval($matches[1]) + 1;
            $suggested = preg_replace('/\d+$/', $num, $ctc_no);
        } else {
            $suggested = $ctc_no . '1';
        }
    }

    header('Content-Type: application/json');
    echo json_encode([
        'duplicate' => $exists,
        'suggested' => $suggested
    ]);
    exit;
}

// ✅ CRUD Operations

// ADD
if (isset($_POST['add_ctccorp'])) {
    q("INSERT INTO ctc_corporation (
        ctccorp_no, year, place_of_issue, date_issued, company_fullname,
        business_address, kind_of_org, nature_of_business, incorporation_address, date_reg,
        rpt_assessedvalue, gross_receipts, basic_tax, additional_tax,
        rpt_tax_due, gr_tax_due, surcharge, total_due,
        position_authorizedsig, treasurer, created_by, created_at
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())", [
        $_POST['ctccorp_no'], $_POST['year'], $_POST['place_of_issue'], $_POST['date_issued'],
        $_POST['company_fullname'], $_POST['business_address'], $_POST['kind_of_org'], $_POST['nature_of_business'],
        $_POST['incorporation_address'], $_POST['date_reg'], $_POST['rpt_assessedvalue'],
        $_POST['gross_receipts'], $_POST['basic_tax'], $_POST['additional_tax'],
        $_POST['rpt_tax_due'], $_POST['gr_tax_due'], $_POST['surcharge'],
        $_POST['total_due'], $_POST['position_authorizedsig'], $_SESSION['name'], $_SESSION['name']
    ]);
    echo "<script>alert('✅ Corporation CTC Added!'); location.href='ctccorp_list.php';</script>";
    exit;
}

// EDIT
if (isset($_POST['edit_ctccorp'])) {
    q("UPDATE ctc_corporation SET
        ctccorp_no=?, year=?, place_of_issue=?, date_issued=?, company_fullname=?,
        business_address=?, kind_of_org=?, nature_of_business=?, incorporation_address=?, date_reg=?,
        rpt_assessedvalue=?, gross_receipts=?, basic_tax=?, additional_tax=?,
        rpt_tax_due=?, gr_tax_due=?, surcharge=?, total_due=?,
        position_authorizedsig=?, treasurer=?, updated_by=?, updated_at=NOW()
        WHERE id=?", [
        $_POST['ctccorp_no'], $_POST['year'], $_POST['place_of_issue'], $_POST['date_issued'],
        $_POST['company_fullname'], $_POST['business_address'], $_POST['kind_of_org'], $_POST['nature_of_business'],
        $_POST['incorporation_address'], $_POST['date_reg'], $_POST['rpt_assessedvalue'],
        $_POST['gross_receipts'], $_POST['basic_tax'], $_POST['additional_tax'],
        $_POST['rpt_tax_due'], $_POST['gr_tax_due'], $_POST['surcharge'], $_POST['total_due'],
        $_POST['position_authorizedsig'], $_SESSION['name'], $_SESSION['name'], $_POST['ctccorp_id']
    ]);
    echo "<script>alert('✏️ Corporation CTC Updated!'); location.href='ctccorp_list.php';</script>";
    exit;
}

// DELETE
if (isset($_GET['delete'])) {
    q("DELETE FROM ctc_corporation WHERE id=?", [$_GET['delete']]);
    echo "<script>alert('🗑️ Deleted successfully'); location.href='ctccorp_list.php';</script>";
    exit;
}

// FILTER
$where = "WHERE 1";
$params = [];

if (!empty($_GET['search'])) {
    $where .= " AND (company_fullname LIKE ? OR ctccorp_no LIKE ? OR created_by LIKE ?)";
    $search = "%" . $_GET['search'] . "%";
    array_push($params, $search, $search);
}
if (!empty($_GET['year'])) {
    $where .= " AND year = ?";
    array_push($params, $_GET['year']);
}

$stmt = q("SELECT * FROM ctc_corporation $where ORDER BY id DESC", $params);
$res = $stmt->get_result();
$records = $res->fetch_all(MYSQLI_ASSOC);
?>

<div class="container-fluid mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-building"></i> Community Tax Certificates - For Corporations</h4>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCTCCorpModal">➕ New Corp CTC</button>
  </div>

  <form class="row mb-3" method="get">
    <div class="col-md-4">
      <input type="text" name="search" class="form-control" placeholder="Search by CTC No. or Company or Cashier/Collecting Officer"
             value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
    </div>
    <div class="col-md-3">
      <input type="number" name="year" class="form-control" placeholder="Year"
             value="<?= htmlspecialchars($_GET['year'] ?? '') ?>">
    </div>
    <div class="col-md-2">
      <button class="btn btn-primary w-100">Filter</button>
    </div>
    <div class="col-md-2">
      <a href="ctccorp_list.php" class="btn btn-secondary w-100">Reset</a>
    </div>
  </form>

  <div class="card">
    <div class="card-body table-responsive">
      <table class="table table-bordered table-hover align-middle text-center">
        <thead class="table-dark">
          <tr>
            <th>ID</th><th>CTC No</th><th>Company</th><th>Kind of Org</th><th>Year</th>
            <th>RPT Tax</th><th>GR Tax</th><th>Basic Tax</th><th>Total Due</th><th>Date Issued</th><th>Collecting Officer</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($records): foreach ($records as $r): ?>
          <tr>
            <td><?= $r['id'] ?></td>
            <td><b><?= htmlspecialchars($r['ctccorp_no']) ?></b></td>
            <td><?= htmlspecialchars($r['company_fullname']) ?></td>
            <td><?= htmlspecialchars($r['kind_of_org']) ?></td>
            <td><?= $r['year'] ?></td>
            <td>₱<?= number_format($r['rpt_tax_due'],2) ?></td>
            <td>₱<?= number_format($r['gr_tax_due'],2) ?></td>
            <td>₱<?= number_format($r['basic_tax'],2) ?></td>
            <td><b>₱<?= number_format($r['total_due'],2) ?></b></td>
            <td><?= htmlspecialchars($r['date_issued']) ?></td>
            <td><?= htmlspecialchars($r['created_by']) ?></td>
            <!-- <td>
              <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $r['id'] ?>">✏️</button>
              <a href="?delete=<?= $r['id'] ?>" onclick="return confirm('Delete this record?')" class="btn btn-sm btn-danger">🗑️</a>
            </td> -->
            
            <td>
  <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $r['id'] ?>">✏️</button>
  <a href="?delete=<?= $r['id'] ?>" onclick="return confirm('Delete this record?')" class="btn btn-sm btn-danger">🗑️</a>

  <!-- ✅ New Print Button -->
  <a href="print_ctc_corporation.php?id=<?= $r['id'] ?>" target="_blank" class="btn btn-sm btn-success">
    🖨️
  </a>
</td>
            
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="12" class="text-muted">No records found</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/ctccorp_modals.php'; ?>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<script>
// ==================== Existing JS (compute, surcharge, number-to-words) ====================

// ✅ Duplicate CTC Check before submission
function checkDuplicateCTC(input) {
    const val = input.value.trim();
    if (!val) return;

    fetch('ctccorp_list.php?check_ctccorp_no=' + encodeURIComponent(val))
        .then(res => res.json())
        .then(data => {
            if (data.duplicate) {
                alert(`❌ Duplicate CTC Number!\nSuggested: ${data.suggested}`);
                input.value = data.suggested;
                input.focus();
            }
        });
}

// Add event listeners to CTC No inputs
document.querySelectorAll('[name="ctccorp_no"]').forEach(input => {
    input.addEventListener('blur', e => checkDuplicateCTC(e.target));
});
</script>

<script>
// ✅ Surcharge Computation Rules (Effective March)
// January–February → 0%
// March → 6%
// April → 8%
// May → 10%
// June → 12%
// July → 14%
// August → 16%
// September → 18%
// October → 20%
// November → 22%
// December → 24% (max cap)

function computeSurchargeAndTotal(modal) {
  const rpt = parseFloat(modal.querySelector('[name="rpt_tax_due"]')?.value) || 0;
  const gr = parseFloat(modal.querySelector('[name="gr_tax_due"]')?.value) || 0;
  const basic = parseFloat(modal.querySelector('[name="basic_tax"]')?.value) || 0;
  const addl = parseFloat(modal.querySelector('[name="additional_tax"]')?.value) || 0;

  let surchargeRate = 0;
  const dateField = modal.querySelector('[name="date_issued"]');
  if (dateField && dateField.value) {
    const d = new Date(dateField.value);
    const month = d.getMonth() + 1; // JS months: 0–11, so +1

    if (month <= 2) {
      surchargeRate = 0; // January–February = 0%
    } else {
      // March = 6%, April = 8%, ... December = 24% (max)
      surchargeRate = Math.min((month - 2) * 2 + 4, 24);
    }
  }

  const subtotal = rpt + gr + basic + addl;
  const surcharge = subtotal * (surchargeRate / 100);
  const total_due = subtotal + surcharge;

  if (modal.querySelector('[name="surcharge"]'))
      modal.querySelector('[name="surcharge"]').value = surcharge.toFixed(2);
  if (modal.querySelector('[name="total_due"]'))
      modal.querySelector('[name="total_due"]').value = total_due.toFixed(2);
}

// ✅ Auto-trigger computation when any input changes
document.addEventListener('input', function(e) {
  const modal = e.target.closest('.modal');
  if (modal) computeSurchargeAndTotal(modal);
});

// ✅ Also recompute when date_issued is changed (on change event)
document.addEventListener('change', function(e) {
  if (e.target.matches('[name="date_issued"]')) {
    const modal = e.target.closest('.modal');
    if (modal) computeSurchargeAndTotal(modal);
  }
});
</script>

<?php ob_end_flush(); ?>
