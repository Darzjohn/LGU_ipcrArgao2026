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

// ✅ Role restriction
if (!in_array($_SESSION['role'], ['admin', 'treasurer', 'cashier'])) {
    header("Location: ../index.php");
    exit;
}

// ✅ Smart query helper
if (!function_exists('q')) {
    function q($sql, $params = []) {
        global $mysqli;

        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            die("❌ SQL Prepare Error: " . $mysqli->error . "<br>Query: $sql");
        }

        if (!empty($params)) {
            $types = '';
            foreach ($params as $p) {
                if (is_int($p)) $types .= 'i';
                elseif (is_float($p) || is_double($p)) $types .= 'd';
                else $types .= 's';
            }
            if (strlen($types) != count($params)) {
                die("❌ Bind mismatch: " . strlen($types) . " vs " . count($params));
            }
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt->execute()) {
            die("❌ SQL Exec Error: " . $stmt->error . "<br>Query: $sql");
        }

        return $stmt;
    }
}

// ✅ Initialize
$ctc_records = [];

// ✅ Handle Add
if (isset($_POST['add_ctc'])) {
    q(
        "INSERT INTO ctc_individual (
            ctc_no, year, date_issued, place_of_issue,
            surname, firstname, middlename, address,
            citizenship, place_of_birth, civil_status, sex,
            gross_receipts, salaries, real_property_income,
            gr_tax_due, sal_tax_due, rpt_tax_due, surcharge,
            basic_tax, additional_tax, total_due,
            treasurer, created_by, created_at
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, ?, NOW())",
        [
            $_POST['ctc_no'], $_POST['year'], $_POST['date_issued'], $_POST['place_of_issue'],
            $_POST['surname'], $_POST['firstname'], $_POST['middlename'], $_POST['address'],
            $_POST['citizenship'], $_POST['place_of_birth'], $_POST['civil_status'], $_POST['sex'],
            $_POST['gross_receipts'], $_POST['salaries'], $_POST['real_property_income'],
            $_POST['gr_tax_due'], $_POST['sal_tax_due'], $_POST['rpt_tax_due'], $_POST['surcharge'],
            $_POST['basic_tax'], $_POST['additional_tax'], $_POST['total_due'],
            $_SESSION['name'], $_SESSION['name']
        ]
    );
    echo "<script>alert('✅ CTC added successfully!'); location.href='ctc_list.php';</script>";
    exit;
}

// ✅ Handle Edit
if (isset($_POST['edit_ctc'])) {
    q(
        "UPDATE ctc_individual SET
            ctc_no=?, year=?, date_issued=?, place_of_issue=?,
            surname=?, firstname=?, middlename=?, address=?,
            citizenship=?, place_of_birth=?, civil_status=?, sex=?,
            gross_receipts=?, salaries=?, real_property_income=?,
            gr_tax_due=?, sal_tax_due=?, rpt_tax_due=?, surcharge=?,
            basic_tax=?, additional_tax=?, total_due=?,
            updated_by=?, updated_at=NOW()
         WHERE id=?",
        [
            $_POST['ctc_no'], $_POST['year'], $_POST['date_issued'], $_POST['place_of_issue'],
            $_POST['surname'], $_POST['firstname'], $_POST['middlename'], $_POST['address'],
            $_POST['citizenship'], $_POST['place_of_birth'], $_POST['civil_status'], $_POST['sex'],
            $_POST['gross_receipts'], $_POST['salaries'], $_POST['real_property_income'],
            $_POST['gr_tax_due'], $_POST['sal_tax_due'], $_POST['rpt_tax_due'], $_POST['surcharge'],
            $_POST['basic_tax'], $_POST['additional_tax'], $_POST['total_due'],
            $_SESSION['name'], $_POST['ctc_id']
        ]
    );
    echo "<script>alert('✏️ CTC updated successfully!'); location.href='ctc_list.php';</script>";
    exit;
}

// ✅ Handle Delete
if (isset($_GET['delete'])) {
    q("DELETE FROM ctc_individual WHERE id=?", [intval($_GET['delete'])]);
    echo "<script>alert('🗑️ CTC deleted successfully!'); location.href='ctc_list.php';</script>";
    exit;
}

// ✅ Search / Filter
$where = "WHERE 1";
$params = [];

if (!empty($_GET['search'])) {
    $where .= " AND (surname LIKE ? OR firstname LIKE ? OR ctc_no LIKE ?)";
    $search = "%" . $_GET['search'] . "%";
    array_push($params, $search, $search, $search);
}

if (!empty($_GET['year'])) {
    $where .= " AND year = ?";
    array_push($params, $_GET['year']);
}

$sql = "SELECT * FROM ctc_individual $where ORDER BY id DESC";
$stmt = q($sql, $params);
$res = $stmt->get_result();

if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $ctc_records[] = $row;
    }
}
?>

<div class="container-fluid mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-person-vcard"></i> Community Tax Certificates</h4>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCTCModal">➕ New CTC</button>
  </div>

  <!-- ✅ Search & Filter -->
  <form class="row mb-3" method="get">
    <div class="col-md-4">
      <input type="text" name="search" class="form-control" placeholder="Search by CTC No. or Name"
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
      <a href="ctc_list.php" class="btn btn-secondary w-100">Reset</a>
    </div>
  </form>

  <!-- ✅ Data Table -->
  <div class="card">
    <div class="card-body table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-dark text-center">
          <tr>
            <th>ID</th><th>CTC No</th><th>Name</th><th>Sex</th><th>Year</th>
            <th>GR Tax</th><th>SAL Tax</th><th>RPT Tax</th><th>Surcharge</th><th>Total Due</th><th>Date Issued</th><th>Collecting Officer</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($ctc_records)): foreach ($ctc_records as $r): ?>
          <tr class="text-center">
            <td><?= $r['id'] ?></td>
            <td><strong><?= htmlspecialchars($r['ctc_no']) ?></strong></td>
            <td><strong><?= htmlspecialchars($r['surname'] . ', ' . $r['firstname'] . ' ' . $r['middlename']) ?></strong></td>
            <td><?= htmlspecialchars($r['sex']) ?></td>
            <td><?= htmlspecialchars($r['year']) ?></td>
            <td>₱<?= number_format($r['gr_tax_due'], 2) ?></td>
            <td>₱<?= number_format($r['sal_tax_due'], 2) ?></td>
            <td>₱<?= number_format($r['rpt_tax_due'], 2) ?></td>
            <td>₱<?= number_format($r['surcharge'], 2) ?></td>
            <td><strong>₱<?= number_format($r['total_due'], 2) ?></strong></td>
            <td><strong><?= htmlspecialchars($r['date_issued']) ?></strong></td>
            <td><strong><?= htmlspecialchars($r['created_by']) ?></strong></td>
            <!-- <td>
              <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $r['id'] ?>">✏️</button>
              <a href="?delete=<?= $r['id'] ?>" onclick="return confirm('Delete this record?')" class="btn btn-sm btn-danger">🗑️</a>
            </td> -->
            <td>
  <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $r['id'] ?>">✏️</button>
  <a href="?delete=<?= $r['id'] ?>" onclick="return confirm('Delete this record?')" class="btn btn-sm btn-danger">🗑️</a>

  <!-- Print CTC individual (opens PDF in new tab). Preview: add &preview=1 -->
  <a href="print_ctc_individual.php?id=<?= $r['id'] ?>" target="_blank" class="btn btn-sm btn-primary" title="Print CTC">🖨️</a>
  <!-- optional preview button:
  <a href="print_ctc_individual.php?id=<?= $r['id'] ?>&preview=1" target="_blank" class="btn btn-sm btn-info" title="Preview">👁️</a>
  -->
</td>

          </tr>
          <?php endforeach; else: ?>
          <tr><td colspan="12" class="text-center text-muted">No CTC records found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ✅ Load Modals -->
<?php require_once __DIR__ . '/ctc_modals.php'; ?>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<script>
function numberToWords(num) {
  const ones = ["","One","Two","Three","Four","Five","Six","Seven","Eight","Nine"];
  const teens = ["Ten","Eleven","Twelve","Thirteen","Fourteen","Fifteen","Sixteen","Seventeen","Eighteen","Nineteen"];
  const tens = ["","","Twenty","Thirty","Forty","Fifty","Sixty","Seventy","Eighty","Ninety"];

  function convert(n){
    if(n < 10) return ones[n];
    if(n < 20) return teens[n-10];
    if(n < 100) return tens[Math.floor(n/10)] + (n%10 ? " " + ones[n%10] : "");
    if(n < 1000) return ones[Math.floor(n/100)] + " Hundred " + convert(n%100);
    if(n < 1000000) return convert(Math.floor(n/1000)) + " Thousand " + convert(n%1000);
    return convert(Math.floor(n/1000000)) + " Million " + convert(n%1000000);
  }

  const pesos = Math.floor(num);
  const centavos = Math.round((num - pesos) * 100);
  return convert(pesos) + (centavos > 0 ? " Pesos and " + convert(centavos) + " Centavos Only" : " Pesos Only");
}

function computeAddCTC() {
  const gross = parseFloat(document.getElementById("add_gross")?.value) || 0;
  const sal = parseFloat(document.getElementById("add_sal")?.value) || 0;
  const rpt = parseFloat(document.getElementById("add_rpt")?.value) || 0;
  const add = parseFloat(document.getElementById("add_additional")?.value) || 0;
  const basic = parseFloat(document.getElementById("add_basic")?.value) || 0;

  const gr_tax = gross * 0.001;
  const sal_tax = sal * 0.001;
  const rpt_tax = rpt * 0.001;

  const dateIssued = new Date(document.getElementById("add_date_issued")?.value);
  const month = dateIssued.getMonth() + 1;
  let surchargeRate = 0;
  if (month >= 3) surchargeRate = Math.min(((month - 2) * 2) + 4, 24);

  const subtotal = gr_tax + sal_tax + rpt_tax + basic + add;
  const surcharge = subtotal * (surchargeRate / 100);
  const total = subtotal + surcharge;

  document.getElementById("add_gr_tax").value = gr_tax.toFixed(2);
  document.getElementById("add_sal_tax").value = sal_tax.toFixed(2);
  document.getElementById("add_rpt_tax").value = rpt_tax.toFixed(2);
  document.getElementById("add_surcharge").value = surcharge.toFixed(2);
  document.getElementById("add_total_due").value = total.toFixed(2);
  document.getElementById("add_amount_words").value = numberToWords(total);
}
</script>

<?php ob_end_flush(); ?>
