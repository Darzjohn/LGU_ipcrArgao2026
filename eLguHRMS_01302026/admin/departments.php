<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';

// ✅ Role restriction
if (!in_array($_SESSION['role'], ['admin', 'hr', 'hr_staff'])) {
    header("Location: ../index.php");
    exit;
}

$messages = [];

/* ============================
   ✅ ADD DEPARTMENT
============================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_department'])) {
    $name = trim($_POST['name'] ?? '');

    if ($name !== '') {
        $stmt = $mysqli->prepare("INSERT INTO departments (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->close();

        $messages[] = ['type' => 'success', 'text' => 'Department added successfully!'];
    } else {
        $messages[] = ['type' => 'danger', 'text' => 'Department name is required!'];
    }
}

/* ============================
   ✅ EDIT DEPARTMENT
============================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_department'])) {
    $id   = (int)$_POST['department_id'];
    $name = trim($_POST['name'] ?? '');

    if ($id > 0 && $name !== '') {
        $stmt = $mysqli->prepare("UPDATE departments SET name=? WHERE id=?");
        $stmt->bind_param("si", $name, $id);
        $stmt->execute();
        $stmt->close();

        $messages[] = ['type' => 'success', 'text' => 'Department updated successfully!'];
    } else {
        $messages[] = ['type' => 'danger', 'text' => 'Invalid update request!'];
    }
}

/* ============================
   ✅ DELETE DEPARTMENT
============================ */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    if ($id > 0) {
        $stmt = $mysqli->prepare("DELETE FROM departments WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        $messages[] = ['type' => 'success', 'text' => 'Department deleted successfully!'];
    }
}

/* ============================
   ✅ SEARCH & PAGINATION
============================ */
$limit  = 10;
$page   = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;
$search = trim($_GET['search'] ?? '');

$where  = "";
$params = [];
$types  = "";

if ($search !== '') {
    $where = "WHERE name LIKE ?";
    $params[] = "%$search%";
    $types .= "s";
}

// ✅ Count total rows
$sql_count = "SELECT COUNT(*) FROM departments $where";
$stmt = $mysqli->prepare($sql_count);

if ($params) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$stmt->bind_result($total);
$stmt->fetch();
$stmt->close();

$total_pages = ceil($total / $limit);

// ✅ Fetch Data
$sql = "SELECT * FROM departments $where ORDER BY id DESC LIMIT ? OFFSET ?";
$stmt = $mysqli->prepare($sql);

if ($params) {
    $stmt->bind_param($types . "ii", ...array_merge($params, [$limit, $offset]));
} else {
    $stmt->bind_param("ii", $limit, $offset);
}

$stmt->execute();
$res = $stmt->get_result();
$departments = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<style>
.modal-dialog { max-width: 500px; }
.table thead th { text-align: center; vertical-align: middle; }
.table tbody td { vertical-align: middle; }
</style>

<h2 class="mb-4">Departments Management</h2>

<?php foreach ($messages as $m): ?>
<div class="alert alert-<?= $m['type'] ?>"><?= $m['text'] ?></div>
<?php endforeach; ?>

<!-- ✅ SEARCH -->
<div class="mb-3">
    <form method="get" class="row g-2">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control"
                   placeholder="Search Department"
                   value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-4">
            <button class="btn btn-primary">
                <i class="bi bi-search"></i> Search
            </button>
            <a href="departments.php" class="btn btn-secondary">
                <i class="bi bi-x-circle"></i> Reset
            </a>
        </div>
    </form>
</div>

<!-- ✅ ADD BUTTON -->
<div class="mb-3 text-end">
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">
        <i class="bi bi-plus-circle"></i> Add Department
    </button>
</div>

<!-- ✅ TABLE -->
<div class="card">
    <div class="card-header bg-secondary text-white">Department List</div>

    <div class="card-body table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr class="text-center">
                    <th>ID</th>
                    <th>Department Name</th>
                    <th width="180">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($departments): foreach ($departments as $row): ?>
                <tr>
                    <td class="text-center"><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#editModal<?= $row['id'] ?>">
                            <i class="bi bi-pencil-square"></i> Edit
                        </button>

                        <a href="?delete=<?= $row['id'] ?>"
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('Delete this department?')">
                            <i class="bi bi-trash"></i> Delete
                        </a>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr>
                    <td colspan="3" class="text-center">No records found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- ✅ PAGINATION -->
        <nav>
            <ul class="pagination justify-content-center">
                <li class="page-item <?= ($page <= 1 ? 'disabled' : '') ?>">
                    <a class="page-link" href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>">Previous</a>
                </li>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= ($i == $page ? 'active' : '') ?>">
                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>

                <li class="page-item <?= ($page >= $total_pages ? 'disabled' : '') ?>">
                    <a class="page-link" href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>">Next</a>
                </li>
            </ul>
        </nav>
    </div>
</div>

<!-- ✅ ADD MODAL -->
<div class="modal fade" id="addDepartmentModal">
    <div class="modal-dialog modal-dialog-centered">
        <form method="post" class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Add Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <label class="form-label">Department Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="modal-footer">
                <button type="submit" name="add_department" class="btn btn-success">
                    <i class="bi bi-plus-circle"></i> Save
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ✅ EDIT MODALS -->
<?php foreach ($departments as $row): ?>
<div class="modal fade" id="editModal<?= $row['id'] ?>">
    <div class="modal-dialog modal-dialog-centered">
        <form method="post" class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Edit Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" name="department_id" value="<?= $row['id'] ?>">
                <label class="form-label">Department Name</label>
                <input type="text" name="name" class="form-control"
                       value="<?= htmlspecialchars($row['name']) ?>" required>
            </div>

            <div class="modal-footer">
                <button type="submit" name="edit_department" class="btn btn-primary">
                    <i class="bi bi-save"></i> Save Changes
                </button>

                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>
<?php endforeach; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
