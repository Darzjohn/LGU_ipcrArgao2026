<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';

// Only admin can manage users
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Handle Add / Edit / Delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $contact_no = trim($_POST['contact_no']);
    $role = $_POST['role'];
    $status = $_POST['status'];
    $emp_idno = trim($_POST['emp_idno'] ?? null); // link to employees

    if ($action === 'add') {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare("INSERT INTO users (emp_idno, username, password, name, role, email, contact_no, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $emp_idno, $username, $password, $name, $role, $email, $contact_no, $status);
        $stmt->execute();
        $stmt->close();
    }

    if ($action === 'edit') {
        $id = (int)$_POST['id'];
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $mysqli->prepare("UPDATE users SET emp_idno=?, username=?, password=?, name=?, role=?, email=?, contact_no=?, status=? WHERE id=?");
            $stmt->bind_param("ssssssssi", $emp_idno, $username, $password, $name, $role, $email, $contact_no, $status, $id);
        } else {
            $stmt = $mysqli->prepare("UPDATE users SET emp_idno=?, username=?, name=?, role=?, email=?, contact_no=?, status=? WHERE id=?");
            $stmt->bind_param("sssssssi", $emp_idno, $username, $name, $role, $email, $contact_no, $status, $id);
        }
        $stmt->execute();
        $stmt->close();
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $mysqli->prepare("DELETE FROM users WHERE id=?");
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
$params = [];
$types = '';
if ($search) {
    $where = "WHERE u.username LIKE ? OR u.name LIKE ? OR u.role LIKE ? OR u.status LIKE ?";
    $like = "%$search%";
    $params = [$like, $like, $like, $like];
    $types = "ssss";
}

// Count total users
if ($where) {
    $stmt = $mysqli->prepare("SELECT COUNT(*) AS total FROM users u $where");
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $count_result = $stmt->get_result()->fetch_assoc();
    $total_users = $count_result['total'];
    $stmt->close();
} else {
    $total_users = $mysqli->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
}

// Fetch users with employee data
// $sql = "SELECT u.*, e.name AS employee_name 
//         FROM users u
//         LEFT JOIN employees e ON u.emp_idno = e.emp_idno
//         $where
//         ORDER BY u.id DESC
//         LIMIT ?, ?";
$sql = "SELECT u.*, e.name AS employee_name 
        FROM users u
        LEFT JOIN employees e 
            ON CONVERT(u.emp_idno USING utf8mb4) COLLATE utf8mb4_unicode_ci 
             = CONVERT(e.emp_idno USING utf8mb4) COLLATE utf8mb4_unicode_ci
        $where
        ORDER BY u.id DESC
        LIMIT ?, ?";

$stmt = $mysqli->prepare($sql);
if ($where) {
    $stmt->bind_param($types."ii", ...array_merge($params, [$offset, $limit]));
} else {
    $stmt->bind_param("ii", $offset, $limit);
}
$stmt->execute();
$users = $stmt->get_result();
$stmt->close();

$total_pages = ceil($total_users / $limit);
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-people-fill"></i> User Management</h4>
        <div class="d-flex gap-2">
            <form class="d-flex" method="get" role="search">
                <input class="form-control me-2" type="search" name="search" placeholder="Search user..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-outline-primary" type="submit"><i class="bi bi-search"></i></button>
            </form>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#userModal" onclick="openAddModal()">+ Add User</button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Employee ID</th>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Email</th>
                    <th>Contact</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($users->num_rows > 0): ?>
                <?php while ($row = $users->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['emp_idno']) ?></td>
                    <td><?= htmlspecialchars($row['employee_name'] ?: $row['name']) ?></td>
                    <td><?= htmlspecialchars(ucwords(str_replace('_', ' ', $row['role']))) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['contact_no']) ?></td>
                    <td>
                        <span class="badge bg-<?= $row['status'] === 'active' ? 'success' : 'secondary' ?>">
                            <?= ucfirst($row['status']) ?>
                        </span>
                    </td>
                    <td><?= $row['last_login'] ?: '-' ?></td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-primary" onclick='openEditModal(<?= json_encode($row) ?>)'><i class="bi bi-pencil-square"></i></button>
                        <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this user?');"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="9" class="text-center text-muted">No users found.</td></tr>
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
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" id="userForm">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="userModalLabel">Add User</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body row g-3">
          <input type="hidden" name="id" id="user_id">
          <input type="hidden" name="action" id="form_action" value="add">

          <div class="col-md-6">
            <label class="form-label">Employee ID</label>
            <input type="text" class="form-control" name="emp_idno" id="emp_idno">
          </div>

          <div class="col-md-6">
            <label class="form-label">Full Name</label>
            <input type="text" class="form-control" name="name" id="name" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Username</label>
            <input type="text" class="form-control" name="username" id="username" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Password</label>
            <input type="password" class="form-control" name="password" id="password">
            <small class="text-muted" id="passwordNote">Required for new user</small>
          </div>

          <div class="col-md-6">
            <label class="form-label">Role</label>
            <select class="form-select" name="role" id="role" required>
              <option value="admin">Admin</option>
              <option value="hr">HR</option>
              <option value="hr_staff">HR Staff</option>
              <option value="employee">Employee</option>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" id="email">
          </div>

          <div class="col-md-6">
            <label class="form-label">Contact No.</label>
            <input type="text" class="form-control" name="contact_no" id="contact_no">
          </div>

          <div class="col-md-6">
            <label class="form-label">Status</label>
            <select class="form-select" name="status" id="status" required>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
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
    document.getElementById('userModalLabel').textContent = 'Add User';
    document.getElementById('form_action').value = 'add';
    document.getElementById('userForm').reset();
    document.getElementById('passwordNote').textContent = 'Required for new user';
}

function openEditModal(user) {
    document.getElementById('userModalLabel').textContent = 'Edit User';
    document.getElementById('form_action').value = 'edit';
    document.getElementById('user_id').value = user.id;
    document.getElementById('emp_idno').value = user.emp_idno;
    document.getElementById('name').value = user.name;
    document.getElementById('username').value = user.username;
    document.getElementById('email').value = user.email;
    document.getElementById('contact_no').value = user.contact_no;
    document.getElementById('role').value = user.role;
    document.getElementById('status').value = user.status;
    document.getElementById('password').value = '';
    document.getElementById('passwordNote').textContent = 'Leave blank to keep existing password';
    var modal = new bootstrap.Modal(document.getElementById('userModal'));
    modal.show();
}

// Auto-fill full name when Employee ID changes
document.getElementById('emp_idno').addEventListener('input', function() {
    var empId = this.value.trim();
    if (empId) {
        fetch('get_employee_name.php?emp_idno=' + encodeURIComponent(empId))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('name').value = data.name;
            }
        });
    }
});


</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
