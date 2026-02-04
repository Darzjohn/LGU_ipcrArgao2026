<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';


// Access All Accounts
if(!in_array($_SESSION['role'], ['admin','assessor','assessment_clerk','treasurer','cashier'])) {
    header("Location: ../index.php");
    exit;
}

$messages = [];

// --- Add Owner ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_owner'])) {
    $owner_name = trim($_POST['owner_name'] ?? '');
    $address    = trim($_POST['address'] ?? '');

    if ($owner_name) {
        q("INSERT INTO owners (name, address) VALUES (?, ?)", "ss", [$owner_name, $address]);
        $messages[] = ['type' => 'success', 'text' => 'Owner added successfully!'];
    } else {
        $messages[] = ['type' => 'danger', 'text' => 'Please enter owner name!'];
    }
}

// --- Edit Owner ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_owner'])) {
    $id         = (int)$_POST['owner_id'];
    $owner_name = trim($_POST['owner_name'] ?? '');
    $address    = trim($_POST['address'] ?? '');

    if ($id > 0 && $owner_name) {
        q("UPDATE owners SET name=?, address=? WHERE id=?", "ssi", [$owner_name, $address, $id]);
        $messages[] = ['type' => 'success', 'text' => 'Owner updated successfully!'];
    } else {
        $messages[] = ['type' => 'danger', 'text' => 'Invalid owner update!'];
    }
}

// --- Delete Owner ---
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 0) {
        q("DELETE FROM owners WHERE id=?", "i", [$id]);
        $messages[] = ['type' => 'success', 'text' => 'Owner deleted successfully!'];
    }
}

// --- Pagination & Search ---
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

// Count total
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
$owners = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<style>
.modal-dialog { max-width: 500px; }
.modal-body .form-control { font-size: 0.9rem; }
.table thead th { text-align: center; vertical-align: middle; }
.table tbody td { vertical-align: middle; }
</style>

<h2 class="mb-4">Owners Management</h2>

<?php foreach ($messages as $m): ?>
<div class="alert alert-<?= $m['type'] ?>"><?= $m['text'] ?></div>
<?php endforeach; ?>

<!-- Search -->
<div class="mb-3">
  <form method="get" class="row g-2">
    <div class="col-md-3">
      <input type="text" name="search" class="form-control" placeholder="Search Owner Name" value="<?= htmlspecialchars($search) ?>">
    </div>
    <div class="col-md-3">
      <button class="btn btn-primary"><i class="bi bi-search"></i> Search</button>
      <a href="owners.php" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Reset</a>
    </div>
  </form>
</div>

<!-- Add Owner Button -->
<div class="mb-3 text-end">
  <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addOwnerModal">
    <i class="bi bi-person-plus"></i> Add Owner
  </button>
</div>

<!-- Owners Table -->
<div class="card">
  <div class="card-header bg-secondary text-white">Owner List</div>
  <div class="card-body table-responsive">
    <table class="table table-bordered table-hover align-middle">
      <thead class="table-dark">
        <tr class="text-center">
          <th width="60">ID</th>
          <th>Name</th>
          <th>Address</th>
          <th width="180">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($owners as $row): ?>
        <tr>
          <td class="text-center"><?= $row['id'] ?></td>
          <td><?= htmlspecialchars($row['name']) ?></td>
          <td><?= htmlspecialchars($row['address']) ?></td>
          <td class="text-center">
            <button class="btn btn-sm btn-primary" 
                    data-bs-toggle="modal" 
                    data-bs-target="#editModal<?= $row['id'] ?>">
              <i class="bi bi-pencil-square"></i> Edit
            </button>
            <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger"
               onclick="return confirm('Delete this owner?')">
              <i class="bi bi-trash"></i> Delete
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
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

<!-- Add Owner Modal -->
<div class="modal fade" id="addOwnerModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <form method="post" class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">Add New Owner</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Owner Name</label>
          <input name="owner_name" type="text" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Address</label>
          <input name="address" type="text" class="form-control">
        </div>
      </div>

      <div class="modal-footer">
        <button type="submit" name="add_owner" class="btn btn-success">Add Owner</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Modals (Placed outside table) -->
<?php foreach ($owners as $row): ?>
<div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <form method="post" class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Edit Owner</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="owner_id" value="<?= $row['id'] ?>">
        <div class="mb-3">
          <label class="form-label">Owner Name</label>
          <input type="text" name="owner_name" class="form-control" 
                 value="<?= htmlspecialchars($row['name']) ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Address</label>
          <input type="text" name="address" class="form-control" 
                 value="<?= htmlspecialchars($row['address']) ?>">
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="edit_owner" class="btn btn-primary">Save Changes</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>
<?php endforeach; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
