<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
$messages = [];

// Only Assessor can manage owners
if ($_SESSION['role'] !== 'assessor') {
    header("Location: ../index.php");
    exit;
}

// ✅ Safe query helper (defines q() function)
if (!function_exists('q')) {
    function q($sql, $types = "", $params = [])
    {
        global $mysqli;
        $stmt = $mysqli->prepare($sql);
        if ($types && $params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt;
    }
}

// --- Handle Add Owner ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_owner'])) {
    $owner_name = trim($_POST['owner_name'] ?? '');
    $address    = trim($_POST['address'] ?? '');

    if ($owner_name) {
        q("INSERT INTO owners (name, address) VALUES (?, ?)", "ss", [$owner_name, $address]);
        $messages[] = ['type' => 'success', 'text' => '✅ Owner added successfully!'];
    } else {
        $messages[] = ['type' => 'danger', 'text' => '⚠️ Please enter owner name!'];
    }
}

// --- Handle Edit Owner ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_owner'])) {
    $id         = (int)$_POST['owner_id'];
    $owner_name = trim($_POST['owner_name'] ?? '');
    $address    = trim($_POST['address'] ?? '');

    if ($id > 0 && $owner_name) {
        q("UPDATE owners SET name=?, address=? WHERE id=?", "ssi", [$owner_name, $address, $id]);
        $messages[] = ['type' => 'success', 'text' => '✅ Owner updated successfully!'];
    } else {
        $messages[] = ['type' => 'danger', 'text' => '⚠️ Invalid owner update!'];
    }
}

// --- Handle Delete Owner ---
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 0) {
        q("DELETE FROM owners WHERE id=?", "i", [$id]);
        $messages[] = ['type' => 'success', 'text' => '🗑️ Owner deleted successfully!'];
    }
}

// --- Pagination and Search ---
$limit  = 10;
$page   = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;
$search = trim($_GET['search'] ?? '');
$where  = [];
$params = [];
$types  = '';

if ($search !== '') {
    $where[]  = "(name LIKE ?)";
    $like     = "%$search%";
    $params[] = $like;
    $types   .= "s";
}
$where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";

// Total count
$sql_count = "SELECT COUNT(*) FROM owners $where_sql";
$stmt = $mysqli->prepare($sql_count);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$stmt->bind_result($total);
$stmt->fetch();
$stmt->close();
$total_pages = ceil($total / $limit);

// Fetch owners
$sql = "SELECT * FROM owners $where_sql ORDER BY id DESC LIMIT ? OFFSET ?";
$stmt = $mysqli->prepare($sql);
if ($params) {
    $stmt->bind_param($types . 'ii', ...array_merge($params, [$limit, $offset]));
} else {
    $stmt->bind_param("ii", $limit, $offset);
}
$stmt->execute();
$res = $stmt->get_result();
?>

<h2 class="mb-4"><i class="bi bi-people-fill"></i> Owners</h2>

<?php foreach ($messages as $m): ?>
<div class="alert alert-<?= $m['type'] ?>"><?= $m['text'] ?></div>
<?php endforeach; ?>

<!-- Search -->
<div class="mb-3">
  <form method="get" class="row g-2">
    <div class="col-md-3">
      <input type="text" name="search" class="form-control" placeholder="Search Owner Name" value="<?= htmlspecialchars($search) ?>">
    </div>
    <div class="col-md-2">
      <button class="btn btn-primary"><i class="bi bi-search"></i> Search</button>
      <a href="owners.php" class="btn btn-secondary"><i class="bi bi-arrow-repeat"></i> Reset</a>
    </div>
  </form>
</div>

<!-- Add Owner -->
<div class="card mb-4 shadow-sm">
  <div class="card-header bg-primary text-white fw-semibold">
    <i class="bi bi-person-plus-fill"></i> Add New Owner
  </div>
  <div class="card-body">
    <form method="post" class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Owner Name</label>
        <input name="owner_name" type="text" class="form-control" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Address</label>
        <input name="address" type="text" class="form-control">
      </div>
      <div class="col-md-2 align-self-end">
        <button class="btn btn-success w-100" name="add_owner"><i class="bi bi-plus-circle"></i> Add</button>
      </div>
    </form>
  </div>
</div>

<!-- Owner List -->
<div class="card shadow-sm">
  <div class="card-header bg-secondary text-white fw-semibold">
    <i class="bi bi-card-list"></i> Owner List
  </div>
  <div class="card-body table-responsive">
    <table class="table table-bordered table-hover align-middle">
      <thead class="table-dark">
        <tr>
          <th width="5%">ID</th>
          <th width="35%">Name</th>
          <th width="40%">Address</th>
          <th width="20%">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php $modals = []; ?>
        <?php while ($row = $res->fetch_assoc()): ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= htmlspecialchars($row['name']) ?></td>
          <td><?= htmlspecialchars($row['address']) ?></td>
          <td>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>">
              <i class="bi bi-pencil-square"></i> Edit
            </button>
            <a href="owners.php?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this owner?')">
              <i class="bi bi-trash"></i> Delete
            </a>
          </td>
        </tr>
        <?php $modals[] = $row; ?>
        <?php endwhile; ?>
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

<!-- Edit Modals -->
<?php foreach ($modals as $row): ?>
<div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <form method="post" class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Edit Owner</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body py-4">
        <input type="hidden" name="owner_id" value="<?= $row['id'] ?>">
        <div class="mb-3">
          <label class="form-label fw-semibold">Owner Name</label>
          <input type="text" name="owner_name" class="form-control" value="<?= htmlspecialchars($row['name'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Address</label>
          <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($row['address'] ?? '') ?>">
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="edit_owner" class="btn btn-success"><i class="bi bi-check-circle"></i> Save Changes</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>
<?php endforeach; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
