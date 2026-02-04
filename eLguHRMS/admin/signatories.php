<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';

// Only admin can manage signatories
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Handle Add / Edit / Delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $position = trim($_POST['position']);
    $name = trim($_POST['name']);
    $title = trim($_POST['title']);

    if ($action === 'add') {
        $stmt = $mysqli->prepare("INSERT INTO signatories (position, name, title) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $position, $name, $title);
        $stmt->execute();
        $stmt->close();
    }

    if ($action === 'edit') {
        $id = (int)$_POST['id'];
        $stmt = $mysqli->prepare("UPDATE signatories SET position=?, name=?, title=? WHERE id=?");
        $stmt->bind_param("sssi", $position, $name, $title, $id);
        $stmt->execute();
        $stmt->close();
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $mysqli->prepare("DELETE FROM signatories WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// Search & Pagination setup
$search = trim($_GET['search'] ?? '');
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

$where = '';
if ($search) {
    $where = "WHERE position LIKE ? OR name LIKE ? OR title LIKE ?";
}

// Count total signatories
if ($where) {
    $stmt = $mysqli->prepare("SELECT COUNT(*) AS total FROM signatories $where");
    $like = "%$search%";
    $stmt->bind_param("sss", $like, $like, $like);
    $stmt->execute();
    $count_result = $stmt->get_result()->fetch_assoc();
    $total_signatories = $count_result['total'];
    $stmt->close();
} else {
    $total_signatories = $mysqli->query("SELECT COUNT(*) AS total FROM signatories")->fetch_assoc()['total'];
}

// Fetch signatories
if ($where) {
    $stmt = $mysqli->prepare("SELECT * FROM signatories $where ORDER BY id DESC LIMIT ?, ?");
    $stmt->bind_param("ssii", $like, $like, $offset, $limit);
} else {
    $stmt = $mysqli->prepare("SELECT * FROM signatories ORDER BY id DESC LIMIT ?, ?");
    $stmt->bind_param("ii", $offset, $limit);
}
$stmt->execute();
$signatories = $stmt->get_result();
$stmt->close();

$total_pages = ceil($total_signatories / $limit);
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-pencil-square"></i> Signatory Management</h4>
        <div class="d-flex gap-2">
            <form class="d-flex" method="get" role="search">
                <input class="form-control me-2" type="search" name="search" placeholder="Search signatory..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-outline-primary" type="submit"><i class="bi bi-search"></i></button>
            </form>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#signatoryModal" onclick="openAddModal()">+ Add Signatory</button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Position</th>
                    <th>Name</th>
                    <th>Title</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($signatories->num_rows > 0): ?>
                <?php while ($row = $signatories->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['position']) ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick='openEditModal(<?= json_encode($row) ?>)'>
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this signatory?');">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5" class="text-center text-muted">No signatories found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <nav>
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="signatoryModal" tabindex="-1" aria-labelledby="signatoryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" id="signatoryForm">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="signatoryModalLabel">Add Signatory</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body row g-3">
          <input type="hidden" name="id" id="signatory_id">
          <input type="hidden" name="action" id="form_action" value="add">

          <div class="col-md-6">
            <label class="form-label">Position</label>
            <input type="text" class="form-control" name="position" id="position" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Name</label>
            <input type="text" class="form-control" name="name" id="name" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Title</label>
            <input type="text" class="form-control" name="title" id="title" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Save</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openAddModal() {
    document.getElementById('signatoryModalLabel').textContent = 'Add Signatory';
    document.getElementById('form_action').value = 'add';
    document.getElementById('signatoryForm').reset();
}

function openEditModal(signatory) {
    document.getElementById('signatoryModalLabel').textContent = 'Edit Signatory';
    document.getElementById('form_action').value = 'edit';
    document.getElementById('signatory_id').value = signatory.id;
    document.getElementById('position').value = signatory.position;
    document.getElementById('name').value = signatory.name;
    document.getElementById('title').value = signatory.title;
    var modal = new bootstrap.Modal(document.getElementById('signatoryModal'));
    modal.show();
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
