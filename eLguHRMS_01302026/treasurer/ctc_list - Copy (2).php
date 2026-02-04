<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';

// Only Treasurer, Admin, or Clerk can access
if(!in_array($_SESSION['role'], ['admin','treasurer','cashier'])) {
    header("Location: ../index.php");
    exit;
}

$messages = [];

// ✅ ADD CTC
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_ctc'])) {
    $stmt = $mysqli->prepare("INSERT INTO ctc_individual (
        ctc_no, year, date_issued, surname, firstname, middlename, 
        address, citizenship, place_of_birth, civil_status,
        gross_receipts, salaries, real_property_income,
        basic_tax, additional_tax, total_due,
        place_of_issue, created_by
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param(
        "sisssssssssddddsss",
        $_POST['ctc_no'],
        $_POST['year'],
        $_POST['date_issued'],
        $_POST['surname'],
        $_POST['firstname'],
        $_POST['middlename'],
        $_POST['address'],
        $_POST['citizenship'],
        $_POST['place_of_birth'],
        $_POST['civil_status'],
        $_POST['gross_receipts'],
        $_POST['salaries'],
        $_POST['real_property_income'],
        $_POST['basic_tax'],
        $_POST['additional_tax'],
        $_POST['total_due'],
        $_POST['place_of_issue'],
        $_SESSION['username']
    );

    if ($stmt->execute()) {
        $messages[] = ['type' => 'success', 'text' => 'CTC added successfully!'];
    } else {
        $messages[] = ['type' => 'danger', 'text' => 'Error adding CTC: ' . $stmt->error];
    }
    $stmt->close();
}

// ✅ EDIT CTC
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_ctc'])) {
    $stmt = $mysqli->prepare("UPDATE ctc_individual SET
        ctc_no=?, year=?, date_issued=?, surname=?, firstname=?, middlename=?,
        address=?, citizenship=?, place_of_birth=?, civil_status=?,
        gross_receipts=?, salaries=?, real_property_income=?,
        basic_tax=?, additional_tax=?, total_due=?, place_of_issue=?
        WHERE id=?");

    $stmt->bind_param(
        "sisssssssssddddssi",
        $_POST['ctc_no'],
        $_POST['year'],
        $_POST['date_issued'],
        $_POST['surname'],
        $_POST['firstname'],
        $_POST['middlename'],
        $_POST['address'],
        $_POST['citizenship'],
        $_POST['place_of_birth'],
        $_POST['civil_status'],
        $_POST['gross_receipts'],
        $_POST['salaries'],
        $_POST['real_property_income'],
        $_POST['basic_tax'],
        $_POST['additional_tax'],
        $_POST['total_due'],
        $_POST['place_of_issue'],
        $_POST['ctc_id']
    );

    if ($stmt->execute()) {
        $messages[] = ['type' => 'success', 'text' => 'CTC updated successfully!'];
    } else {
        $messages[] = ['type' => 'danger', 'text' => 'Error updating CTC: ' . $stmt->error];
    }
    $stmt->close();
}

// ✅ DELETE CTC
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $mysqli->query("DELETE FROM ctc_individual WHERE id=$id");
    $messages[] = ['type' => 'success', 'text' => 'CTC deleted successfully!'];
}

// ✅ PAGINATION + SEARCH
$limit  = 10;
$page   = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;
$search = trim($_GET['search'] ?? '');

$where_sql = "";
$params = [];
$types = "";

if ($search !== '') {
    $where_sql = "WHERE ctc_no LIKE ? OR surname LIKE ? OR firstname LIKE ?";
    $like = "%$search%";
    $params = [$like, $like, $like];
    $types = "sss";
}

// Count total
$sql_count = "SELECT COUNT(*) FROM ctc_individual $where_sql";
$stmt = $mysqli->prepare($sql_count);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$stmt->bind_result($total);
$stmt->fetch();
$stmt->close();
$total_pages = ceil($total / $limit);

// Fetch records
$sql = "SELECT * FROM ctc_individual $where_sql ORDER BY id DESC LIMIT ? OFFSET ?";
$stmt = $mysqli->prepare($sql);
if ($params) {
    $stmt->bind_param($types . "ii", ...array_merge($params, [$limit, $offset]));
} else {
    $stmt->bind_param("ii", $limit, $offset);
}
$stmt->execute();
$res = $stmt->get_result();

// ✅ FIXED VARIABLE INITIALIZATION
$ctc_records = [];
while ($row = $res->fetch_assoc()) {
    $ctc_records[] = $row;
}
$stmt->close();
?>

<h2 class="mb-4">Community Tax Certificate (CTC) Management</h2>

<?php foreach ($messages as $m): ?>
<div class="alert alert-<?= $m['type'] ?>"><?= $m['text'] ?></div>
<?php endforeach; ?>

<!-- ✅ SEARCH -->
<div class="mb-3">
  <form method="get" class="row g-2">
    <div class="col-md-4">
      <input type="text" name="search" class="form-control" placeholder="Search CTC No or Name" value="<?= htmlspecialchars($search) ?>">
    </div>
    <div class="col-md-4">
      <button class="btn btn-primary"><i class="bi bi-search"></i> Search</button>
      <a href="ctc_list.php" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Reset</a>
    </div>
  </form>
</div>

<!-- ✅ ADD BUTTON -->
<div class="mb-3 text-end">
  <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCTCModal">
    <i class="bi bi-plus-circle"></i> Add New CTC
  </button>
</div>

<!-- ✅ TABLE -->
<div class="card">
  <div class="card-header bg-secondary text-white">CTC List</div>
  <div class="card-body table-responsive">
    <table class="table table-bordered table-hover align-middle">
      <thead class="table-dark text-center">
        <tr>
          <th>ID</th>
          <th>CTC No</th>
          <th>Name</th>
          <th>Year</th>
          <th>Date Issued</th>
          <th>Total Due</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($ctc_records as $r): ?>
        <tr>
          <td class="text-center"><?= $r['id'] ?></td>
          <td><?= htmlspecialchars($r['ctc_no']) ?></td>
          <td><?= htmlspecialchars($r['surname'] . ', ' . $r['firstname']) ?></td>
          <td><?= htmlspecialchars($r['year']) ?></td>
          <td><?= htmlspecialchars($r['date_issued']) ?></td>
          <td class="text-end">₱<?= number_format($r['total_due'], 2) ?></td>
          <td class="text-center">
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editModal<?= $r['id'] ?>"><i class="bi bi-pencil-square"></i> Edit</button>
            <a href="?delete=<?= $r['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this record?')"><i class="bi bi-trash"></i> Delete</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <!-- ✅ PAGINATION -->
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

<!-- ✅ INCLUDE MODALS -->
<?php require_once __DIR__ . '/ctc_modals.php'; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
