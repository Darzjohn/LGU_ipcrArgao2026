<?php 
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';

// ✅ Access restriction
if (!in_array($_SESSION['role'], ['admin', 'treasurer', 'cashier'])) {
    header("Location: ../index.php");
    exit;
}

$messages = [];

// Helper query
function q(string $sql, string $types = "", array $params = []) {
    global $mysqli;
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) die("SQL Error: " . $mysqli->error);
    if ($types && $params) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    return $stmt;
}

$current_user = $_SESSION['name'] ?? 'System';

/* --- ADD CTC --- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_ctc'])) {
    $data = [
        $_POST['year'] ?? date('Y'),
        $_POST['place_of_issue'] ?? '',
        $_POST['date_issued'] ?? date('Y-m-d'),
        $_POST['ctc_no'] ?? '',
        $_POST['surname'] ?? '',
        $_POST['firstname'] ?? '',
        $_POST['middlename'] ?? '',
        $_POST['address'] ?? '',
        $_POST['citizenship'] ?? '',
        $_POST['icr_no'] ?? '',
        $_POST['place_of_birth'] ?? '',
        $_POST['civil_status'] ?? 'Single',
        $_POST['profession'] ?? '',
        floatval($_POST['gross_receipts'] ?? 0),
        floatval($_POST['salaries'] ?? 0),
        floatval($_POST['real_property_income'] ?? 0),
        floatval($_POST['basic_tax'] ?? 5),
        floatval($_POST['additional_tax'] ?? 0),
        floatval($_POST['total_due'] ?? 0),
        $_POST['treasurer'] ?? '',
        $_POST['date_of_payment'] ?? null,
        $current_user
    ];

    if (!empty($data[3]) && !empty($data[4])) {
        q("INSERT INTO ctc_individual (
                year, place_of_issue, date_issued, ctc_no, surname, firstname, middlename, address, citizenship, 
                icr_no, place_of_birth, civil_status, profession, gross_receipts, salaries, real_property_income, 
                basic_tax, additional_tax, total_due, treasurer, date_of_payment, created_by
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
            "ssssssssssssdddddddsss", $data
        );
        $messages[] = ['type' => 'success', 'text' => 'CTC added successfully!'];
    } else {
        $messages[] = ['type' => 'danger', 'text' => 'Please fill in required fields.'];
    }
}

/* --- EDIT CTC --- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_ctc'])) {
    $id = (int)$_POST['ctc_id'];
    $data = [
        $_POST['year'] ?? date('Y'),
        $_POST['place_of_issue'] ?? '',
        $_POST['date_issued'] ?? date('Y-m-d'),
        $_POST['ctc_no'] ?? '',
        $_POST['surname'] ?? '',
        $_POST['firstname'] ?? '',
        $_POST['middlename'] ?? '',
        $_POST['address'] ?? '',
        $_POST['citizenship'] ?? '',
        $_POST['icr_no'] ?? '',
        $_POST['place_of_birth'] ?? '',
        $_POST['civil_status'] ?? 'Single',
        $_POST['profession'] ?? '',
        floatval($_POST['gross_receipts'] ?? 0),
        floatval($_POST['salaries'] ?? 0),
        floatval($_POST['real_property_income'] ?? 0),
        floatval($_POST['basic_tax'] ?? 5),
        floatval($_POST['additional_tax'] ?? 0),
        floatval($_POST['total_due'] ?? 0),
        $_POST['treasurer'] ?? '',
        $_POST['date_of_payment'] ?? null,
        $id
    ];

    q("UPDATE ctc_individual 
          SET year=?, place_of_issue=?, date_issued=?, ctc_no=?, surname=?, firstname=?, middlename=?, 
              address=?, citizenship=?, icr_no=?, place_of_birth=?, civil_status=?, profession=?, 
              gross_receipts=?, salaries=?, real_property_income=?, basic_tax=?, additional_tax=?, 
              total_due=?, treasurer=?, date_of_payment=? WHERE id=?", 
      "ssssssssssssdddddddssi", $data);
    $messages[] = ['type' => 'success', 'text' => 'CTC updated successfully!'];
}

/* --- DELETE CTC --- */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 0) {
        q("DELETE FROM ctc_individual WHERE id=?", "i", [$id]);
        $messages[] = ['type' => 'success', 'text' => 'CTC deleted successfully!'];
    }
}

/* --- Pagination & Search --- */
$limit  = 10;
$page   = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;
$search = trim($_GET['search'] ?? '');
$where  = "";
$params = [];
$types  = "";

if ($search !== '') {
    $where = "WHERE (ctc_no LIKE ? OR surname LIKE ? OR firstname LIKE ?)";
    $like = "%$search%";
    $params = [$like, $like, $like];
    $types = "sss";
}

$count_sql = "SELECT COUNT(*) FROM ctc_individual $where";
$stmt = q($count_sql, $types, $params);
$stmt->bind_result($total);
$stmt->fetch();
$stmt->close();
$total_pages = max(1, ceil($total / $limit));

$sql = "SELECT * FROM ctc_individual $where ORDER BY id DESC LIMIT ? OFFSET ?";
if ($params) {
    $stmt = q($sql, $types . "ii", array_merge($params, [$limit, $offset]));
} else {
    $stmt = q($sql, "ii", [$limit, $offset]);
}
$res = $stmt->get_result();
$ctcs = $res->fetch_all(MYSQLI_ASSOC);
?>

<h2 class="mb-4"><i class="bi bi-person-vcard"></i> Community Tax Certificates (Individual)</h2>

<?php foreach ($messages as $m): ?>
<div class="alert alert-<?= $m['type'] ?>"><?= $m['text'] ?></div>
<?php endforeach; ?>

<!-- Search -->
<form method="get" class="row g-2 mb-3">
  <div class="col-md-4">
    <input type="text" name="search" class="form-control" placeholder="Search by CTC No. or Name" value="<?= htmlspecialchars($search) ?>">
  </div>
  <div class="col-md-3">
    <button class="btn btn-primary"><i class="bi bi-search"></i> Search</button>
    <a href="ctc_list.php" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Reset</a>
  </div>
</form>

<!-- Add button -->
<div class="text-end mb-3">
  <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCTCModal">
    <i class="bi bi-plus-circle"></i> Add CTC
  </button>
</div>

<!-- Table -->
<div class="card">
  <div class="card-header bg-secondary text-white">CTC Records</div>
  <div class="card-body table-responsive">
    <table class="table table-bordered align-middle">
      <thead class="table-dark text-center">
        <tr>
          <th>ID</th>
          <th>CTC No</th>
          <th>Name</th>
          <th>Year</th>
          <th>Place of Issue</th>
          <th>Total Due</th>
          <th>Created By</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($ctcs as $r): ?>
        <tr>
          <td class="text-center"><?= $r['id'] ?></td>
          <td><?= htmlspecialchars($r['ctc_no']) ?></td>
          <td><?= htmlspecialchars("{$r['surname']}, {$r['firstname']} {$r['middlename']}") ?></td>
          <td><?= htmlspecialchars($r['year']) ?></td>
          <td><?= htmlspecialchars($r['place_of_issue']) ?></td>
          <td>₱<?= number_format((float)$r['total_due'], 2) ?></td>
          <td><?= htmlspecialchars($r['created_by']) ?></td>
          <td class="text-center">
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editModal<?= $r['id'] ?>">Edit</button>
            <a href="?delete=<?= $r['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this record?')">Delete</a>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$ctcs): ?>
        <tr><td colspan="8" class="text-center text-muted">No CTC records found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <!-- Pagination -->
    <nav>
      <ul class="pagination justify-content-center">
        <li class="page-item <?= ($page <= 1 ? 'disabled' : '') ?>">
          <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">Previous</a>
        </li>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <li class="page-item <?= ($i == $page ? 'active' : '') ?>">
          <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
        <li class="page-item <?= ($page >= $total_pages ? 'disabled' : '') ?>">
          <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">Next</a>
        </li>
      </ul>
    </nav>
  </div>
</div>

<!-- ✅ ADD & EDIT MODALS -->
<?php
include __DIR__ . '/ctc_modals.php';
?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
