<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';

// Role restriction
if (!in_array($_SESSION['role'], ['admin','hr','hr_staff'])) {
    header("Location: ../index.php");
    exit;
}

$messages = [];

/* ---------------------------
   Fetch departments & positions
---------------------------- */
$departments = $mysqli->query("SELECT id, name FROM departments ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
$positions   = $mysqli->query("SELECT id, name FROM positions ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

/* ---------------------------
   Add Employee
---------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_employee'])) {
    $emp_idno   = trim($_POST['emp_idno'] ?? '');
    $name       = trim($_POST['emp_name'] ?? '');

    // other fields
    $email      = trim($_POST['email'] ?? '');
    $contact_no = trim($_POST['contact_no'] ?? '');
    $address    = trim($_POST['address'] ?? '');
    $dob        = trim($_POST['dob'] ?? '');
    $blood_type = trim($_POST['blood_type'] ?? '');
    $department = trim($_POST['department_id'] ?? '');
    $position   = trim($_POST['position_id'] ?? '');
    $sss_no     = trim($_POST['sss_no'] ?? '');
    $gsis_no    = trim($_POST['gsis_no'] ?? '');
    $tin_no     = trim($_POST['tin_no'] ?? '');
    $pagibig_no = trim($_POST['pagibig_no'] ?? '');
    $phic_no    = trim($_POST['phic_no'] ?? '');
    $emergency_contact_person = trim($_POST['emergency_contact_person'] ?? '');
    $emergency_contact_no     = trim($_POST['emergency_contact_no'] ?? '');

    if ($emp_idno !== '' && $name !== '') {
        $stmt = $mysqli->prepare("
            INSERT INTO employees
            (emp_idno, name, email, contact_no, address, dob, blood_type,
             department_id, position_id, sss_no, gsis_no, tin_no, pagibig_no,
             phic_no, emergency_contact_person, emergency_contact_no)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        // using 's' for all params for simplicity
        $stmt->bind_param(
            'ssssssssssssssss',
            $emp_idno, $name, $email, $contact_no, $address, $dob, $blood_type,
            $department, $position, $sss_no, $gsis_no, $tin_no, $pagibig_no,
            $phic_no, $emergency_contact_person, $emergency_contact_no
        );
        $stmt->execute();
        $stmt->close();

        $messages[] = ['type'=>'success', 'text'=>'Employee added successfully!'];
    } else {
        $messages[] = ['type'=>'danger', 'text'=>'Employee ID and Name are required!'];
    }
}

/* ---------------------------
   Edit Employee
---------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_employee'])) {
    $id         = trim($_POST['emp_id'] ?? '');
    $emp_idno   = trim($_POST['emp_idno'] ?? '');
    $name       = trim($_POST['emp_name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $contact_no = trim($_POST['contact_no'] ?? '');
    $address    = trim($_POST['address'] ?? '');
    $dob        = trim($_POST['dob'] ?? '');
    $blood_type = trim($_POST['blood_type'] ?? '');
    $department = trim($_POST['department_id'] ?? '');
    $position   = trim($_POST['position_id'] ?? '');
    $sss_no     = trim($_POST['sss_no'] ?? '');
    $gsis_no    = trim($_POST['gsis_no'] ?? '');
    $tin_no     = trim($_POST['tin_no'] ?? '');
    $pagibig_no = trim($_POST['pagibig_no'] ?? '');
    $phic_no    = trim($_POST['phic_no'] ?? '');
    $emergency_contact_person = trim($_POST['emergency_contact_person'] ?? '');
    $emergency_contact_no     = trim($_POST['emergency_contact_no'] ?? '');

    if ($id !== '' && $emp_idno !== '' && $name !== '') {
        $stmt = $mysqli->prepare("
            UPDATE employees SET
                emp_idno=?, name=?, email=?, contact_no=?, address=?, dob=?, blood_type=?,
                department_id=?, position_id=?, sss_no=?, gsis_no=?, tin_no=?, pagibig_no=?,
                phic_no=?, emergency_contact_person=?, emergency_contact_no=?
            WHERE id=?
        ");
        // all 's' for simplicity (last param is id)
        $stmt->bind_param(
            'sssssssssssssssss',
            $emp_idno, $name, $email, $contact_no, $address, $dob, $blood_type,
            $department, $position, $sss_no, $gsis_no, $tin_no, $pagibig_no,
            $phic_no, $emergency_contact_person, $emergency_contact_no, $id
        );
        $stmt->execute();
        $stmt->close();

        $messages[] = ['type'=>'success', 'text'=>'Employee updated successfully!'];
    } else {
        $messages[] = ['type'=>'danger', 'text'=>'Invalid employee update!'];
    }
}

/* ---------------------------
   Delete Employee (kept as GET as original)
---------------------------- */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 0) {
        $stmt = $mysqli->prepare("DELETE FROM employees WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        $messages[] = ['type'=>'success', 'text'=>'Employee deleted successfully!'];
    }
}

/* ---------------------------
   Search & Pagination
---------------------------- */
$limit  = 10;
$page   = max(1, intval($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;
$search = trim($_GET['search'] ?? '');

$where  = "";
$params = [];
$types  = "";

if ($search !== '') {
    $where = "WHERE e.emp_idno LIKE ? OR e.name LIKE ? OR e.email LIKE ?";
    $like = "%$search%";
    $params = [$like, $like, $like];
    $types  = "sss";
}

// Count total
$sql_count = "SELECT COUNT(*) FROM employees e $where";
$stmt = $mysqli->prepare($sql_count);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$stmt->bind_result($total);
$stmt->fetch();
$stmt->close();

$total_pages = $total > 0 ? ceil($total / $limit) : 1;

// Fetch employees with joins
$sql = "
    SELECT e.*, d.name AS department_name, p.name AS position_name
    FROM employees e
    LEFT JOIN departments d ON e.department_id = d.id
    LEFT JOIN positions p ON e.position_id = p.id
    $where
    ORDER BY e.id DESC
    LIMIT ? OFFSET ?
";
$stmt = $mysqli->prepare($sql);
if ($params) {
    // types + two ints for limit/offset -> we keep all as strings for simplicity
    $stmt->bind_param($types . "ii", ...array_merge($params, [$limit, $offset]));
} else {
    $stmt->bind_param("ii", $limit, $offset);
}
$stmt->execute();
$res = $stmt->get_result();
$employees = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<style>
.modal-dialog { max-width: 900px; }
.table thead th { text-align: center; vertical-align: middle; }
.table tbody td { vertical-align: middle; }
</style>

<h2 class="mb-4">Employees Management</h2>

<?php foreach ($messages as $m): ?>
    <div class="alert alert-<?= $m['type'] ?>"><?= htmlspecialchars($m['text']) ?></div>
<?php endforeach; ?>

<!-- Search -->
<div class="mb-3">
  <form method="get" class="row g-2">
    <div class="col-md-4">
      <input type="text" name="search" class="form-control" placeholder="Search Employee ID, Name, Email" value="<?= htmlspecialchars($search) ?>">
    </div>
    <div class="col-md-4">
      <button class="btn btn-primary"><i class="bi bi-search"></i> Search</button>
      <a href="employees.php" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Reset</a>
    </div>
  </form>
</div>

<!-- Add button -->
<div class="mb-3 text-end">
  <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
    <i class="bi bi-person-plus"></i> Add Employee
  </button>
</div>

<!-- Table -->
<div class="card">
  <div class="card-header bg-secondary text-white">Employee List</div>
  <div class="card-body table-responsive">
    <table class="table table-bordered table-hover align-middle">
      <thead class="table-dark">
        <tr class="text-center">
          <th>ID</th>
          <th>Employee ID</th>
          <th>Name</th>
          <th>Email</th>
          <th>Contact No</th>
          <th>Department</th>
          <th>Position</th>
          <th width="200">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($employees): foreach ($employees as $row): ?>
        <tr>
          <td class="text-center"><?= htmlspecialchars($row['id']) ?></td>
          <td><?= htmlspecialchars($row['emp_idno']) ?></td>
          <td><?= htmlspecialchars($row['name']) ?></td>
          <td><?= htmlspecialchars($row['email']) ?></td>
          <td><?= htmlspecialchars($row['contact_no']) ?></td>
          <td><?= htmlspecialchars($row['department_name']) ?></td>
          <td><?= htmlspecialchars($row['position_name']) ?></td>
          <!-- <td class="text-center">
            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewModal<?= $row['id'] ?>"><i class="bi bi-eye"></i> View</button>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>"><i class="bi bi-pencil-square"></i> Edit</button>
            <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this employee?')"><i class="bi bi-trash"></i> Delete</a>
          </td> -->
       <td class="text-center">
  <button 
    class="btn btn-sm btn-outline-info me-1" 
    data-bs-toggle="modal" 
    data-bs-target="#viewModal<?= $row['id'] ?>" 
    title="View">
    <i class="bi bi-eye"></i>
  </button>

  <button 
    class="btn btn-sm btn-outline-primary me-1" 
    data-bs-toggle="modal" 
    data-bs-target="#editModal<?= $row['id'] ?>" 
    title="Edit">
    <i class="bi bi-pencil-square"></i>
  </button>

  <a 
    href="?delete=<?= $row['id'] ?>" 
    class="btn btn-sm btn-outline-danger" 
    title="Delete"
    onclick="return confirm('Delete this employee?')">
    <i class="bi bi-trash"></i>
  </a>
</td>



        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="8" class="text-center">No employees found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <!-- Pagination -->
    <nav>
      <ul class="pagination justify-content-center">
        <li class="page-item <?= ($page <= 1 ? 'disabled' : '') ?>">
          <a class="page-link" href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>">Previous</a>
        </li>
        <?php for ($i=1; $i <= $total_pages; $i++): ?>
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

<!-- Add Employee Modal (all fields) -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <form method="post" class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">Add New Employee</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="row g-2">
          <div class="col-md-3"><label class="form-label">Employee ID</label><input type="text" name="emp_idno" class="form-control" required></div>
          <div class="col-md-9"><label class="form-label">Name</label><input type="text" name="emp_name" class="form-control" required></div>

          <div class="col-md-4"><label class="form-label">Email</label><input type="email" name="email" class="form-control"></div>
          <div class="col-md-4"><label class="form-label">Contact No</label><input type="text" name="contact_no" class="form-control"></div>
          <div class="col-md-4"><label class="form-label">Date of Birth</label><input type="date" name="dob" class="form-control"></div>

          <div class="col-md-6"><label class="form-label">Department</label>
            <select name="department_id" class="form-select">
              <option value="">-- Select Department --</option>
              <?php foreach ($departments as $d): ?>
                <option value="<?= htmlspecialchars($d['id']) ?>"><?= htmlspecialchars($d['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-6"><label class="form-label">Position</label>
            <select name="position_id" class="form-select">
              <option value="">-- Select Position --</option>
              <?php foreach ($positions as $p): ?>
                <option value="<?= htmlspecialchars($p['id']) ?>"><?= htmlspecialchars($p['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-12"><label class="form-label">Address</label><input type="text" name="address" class="form-control"></div>

          <div class="col-md-2"><label class="form-label">Blood Type</label><input type="text" name="blood_type" class="form-control"></div>
          <div class="col-md-2"><label class="form-label">SSS No.</label><input type="text" name="sss_no" class="form-control"></div>
          <div class="col-md-2"><label class="form-label">GSIS No.</label><input type="text" name="gsis_no" class="form-control"></div>
          <div class="col-md-2"><label class="form-label">TIN No.</label><input type="text" name="tin_no" class="form-control"></div>
          <div class="col-md-2"><label class="form-label">Pag-IBIG No.</label><input type="text" name="pagibig_no" class="form-control"></div>
          <div class="col-md-2"><label class="form-label">PHIC No.</label><input type="text" name="phic_no" class="form-control"></div>

          <div class="col-md-6"><label class="form-label">Emergency Contact Person</label><input type="text" name="emergency_contact_person" class="form-control"></div>
          <div class="col-md-6"><label class="form-label">Emergency Contact No</label><input type="text" name="emergency_contact_no" class="form-control"></div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="submit" name="add_employee" class="btn btn-success"><i class="bi bi-save"></i> Save</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit & View Modals -->
<?php foreach ($employees as $row): ?>
  <!-- Edit -->
  <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
      <form method="post" class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">Edit Employee</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="emp_id" value="<?= htmlspecialchars($row['id']) ?>">
          <div class="row g-2">
            <div class="col-md-3"><label class="form-label">Employee ID</label><input type="text" name="emp_idno" class="form-control" value="<?= htmlspecialchars($row['emp_idno']) ?>" required></div>
            <div class="col-md-9"><label class="form-label">Name</label><input type="text" name="emp_name" class="form-control" value="<?= htmlspecialchars($row['name']) ?>" required></div>

            <div class="col-md-4"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($row['email']) ?>"></div>
            <div class="col-md-4"><label class="form-label">Contact No</label><input type="text" name="contact_no" class="form-control" value="<?= htmlspecialchars($row['contact_no']) ?>"></div>
            <div class="col-md-4"><label class="form-label">Date of Birth</label><input type="date" name="dob" class="form-control" value="<?= htmlspecialchars($row['dob']) ?>"></div>

            <div class="col-md-6"><label class="form-label">Department</label>
              <select name="department_id" class="form-select">
                <option value="">-- Select Department --</option>
                <?php foreach ($departments as $d): ?>
                  <option value="<?= htmlspecialchars($d['id']) ?>" <?= $d['id'] == $row['department_id'] ? 'selected' : '' ?>><?= htmlspecialchars($d['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-6"><label class="form-label">Position</label>
              <select name="position_id" class="form-select">
                <option value="">-- Select Position --</option>
                <?php foreach ($positions as $p): ?>
                  <option value="<?= htmlspecialchars($p['id']) ?>" <?= $p['id'] == $row['position_id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-12"><label class="form-label">Address</label><input type="text" name="address" class="form-control" value="<?= htmlspecialchars($row['address']) ?>"></div>

            <div class="col-md-2"><label class="form-label">Blood Type</label><input type="text" name="blood_type" class="form-control" value="<?= htmlspecialchars($row['blood_type']) ?>"></div>
            <div class="col-md-2"><label class="form-label">SSS No.</label><input type="text" name="sss_no" class="form-control" value="<?= htmlspecialchars($row['sss_no']) ?>"></div>
            <div class="col-md-2"><label class="form-label">GSIS No.</label><input type="text" name="gsis_no" class="form-control" value="<?= htmlspecialchars($row['gsis_no']) ?>"></div>
            <div class="col-md-2"><label class="form-label">TIN No.</label><input type="text" name="tin_no" class="form-control" value="<?= htmlspecialchars($row['tin_no']) ?>"></div>
            <div class="col-md-2"><label class="form-label">Pag-IBIG No.</label><input type="text" name="pagibig_no" class="form-control" value="<?= htmlspecialchars($row['pagibig_no']) ?>"></div>
            <div class="col-md-2"><label class="form-label">PHIC No.</label><input type="text" name="phic_no" class="form-control" value="<?= htmlspecialchars($row['phic_no']) ?>"></div>

            <div class="col-md-6"><label class="form-label">Emergency Contact Person</label><input type="text" name="emergency_contact_person" class="form-control" value="<?= htmlspecialchars($row['emergency_contact_person']) ?>"></div>
            <div class="col-md-6"><label class="form-label">Emergency Contact No</label><input type="text" name="emergency_contact_no" class="form-control" value="<?= htmlspecialchars($row['emergency_contact_no']) ?>"></div>

            <div class="col-md-12"><label class="form-label">Created At</label><input type="text" class="form-control" value="<?= htmlspecialchars($row['created_at'] ?? '') ?>" readonly></div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" name="edit_employee" class="btn btn-primary"><i class="bi bi-save"></i> Update</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- View -->
  <div class="modal fade" id="viewModal<?= $row['id'] ?>" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="modal-content">
        <div class="modal-header bg-info text-white">
          <h5 class="modal-title">View Employee</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <table class="table table-borderless">
            <tr><th>Employee ID</th><td><?= htmlspecialchars($row['emp_idno']) ?></td></tr>
            <tr><th>Name</th><td><?= htmlspecialchars($row['name']) ?></td></tr>
            <tr><th>Email</th><td><?= htmlspecialchars($row['email']) ?></td></tr>
            <tr><th>Contact No</th><td><?= htmlspecialchars($row['contact_no']) ?></td></tr>
            <tr><th>Address</th><td><?= htmlspecialchars($row['address']) ?></td></tr>
            <tr><th>Date of Birth</th><td><?= htmlspecialchars($row['dob']) ?></td></tr>
            <tr><th>Blood Type</th><td><?= htmlspecialchars($row['blood_type']) ?></td></tr>
            <tr><th>Department</th><td><?= htmlspecialchars($row['department_name']) ?></td></tr>
            <tr><th>Position</th><td><?= htmlspecialchars($row['position_name']) ?></td></tr>
            <tr><th>SSS No.</th><td><?= htmlspecialchars($row['sss_no']) ?></td></tr>
            <tr><th>GSIS No.</th><td><?= htmlspecialchars($row['gsis_no']) ?></td></tr>
            <tr><th>TIN No.</th><td><?= htmlspecialchars($row['tin_no']) ?></td></tr>
            <tr><th>Pag-IBIG No.</th><td><?= htmlspecialchars($row['pagibig_no']) ?></td></tr>
            <tr><th>PHIC No.</th><td><?= htmlspecialchars($row['phic_no']) ?></td></tr>
            <tr><th>Emergency Contact Person</th><td><?= htmlspecialchars($row['emergency_contact_person']) ?></td></tr>
            <tr><th>Emergency Contact No</th><td><?= htmlspecialchars($row['emergency_contact_no']) ?></td></tr>
            <tr><th>Created At</th><td><?= htmlspecialchars($row['created_at'] ?? '') ?></td></tr>
          </table>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
<?php endforeach; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
