<?php 
ob_start();
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';

// Only admin can manage employees
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Fetch Employment Status
$empStatusResult = $mysqli->query("SELECT id, name FROM employment_status ORDER BY name ASC");
$employment_status = [];
while ($row = $empStatusResult->fetch_assoc()) {
    $employment_status[] = $row;
}

/* ================================
   ✅ POST HANDLING: ADD / EDIT
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    function post($key){ return trim($_POST[$key] ?? ''); }

    $action = post('action');

    // --- Personal Info ---
    $emp_idno = post('emp_idno');
    $first_name = post('first_name');
    $middle_name = post('middle_name');
    $surname = post('surname');
    $name_extension = post('name_extension');
    $dob = post('dob') ?: null;
    $sex = post('sex') ?: null;
    $civil_status = post('civil_status') ?: null;
    $blood_type = post('blood_type') ?: null;
    $place_of_birth = post('place_of_birth') ?: null;
    $citizenship = post('citizenship') ?: null;
    $height = post('height') ?: null;
    $weight = post('weight') ?: null;

    // --- Contact & Employment ---
    $telephon_no = post('telephon_no') ?: null;
    $mobile_no = post('mobile_no') ?: null;
    $email = post('email') ?: null;
    $email_address = post('email_address') ?: null;
    $department_id = isset($_POST['department_id']) && $_POST['department_id'] !== '' ? (int)$_POST['department_id'] : null;
    $position_id = isset($_POST['position_id']) && $_POST['position_id'] !== '' ? (int)$_POST['position_id'] : null;
    $employment_status_id = isset($_POST['employment_status_id']) && $_POST['employment_status_id'] !== '' ? (int)$_POST['employment_status_id'] : null;
    $Agency_EmployeeNo = post('Agency_EmployeeNo') ?: null;
    $emergency_contact_person = post('emergency_contact_person') ?: null;
    $emergency_contact_no = post('emergency_contact_no') ?: null;

    // --- Government IDs ---
    $sss_no = post('sss_no') ?: null;
    $gsis_no = post('gsis_no') ?: null;
    $tin_no = post('tin_no') ?: null;
    $pagibig_no = post('pagibig_no') ?: null;
    $phic_no = post('phic_no') ?: null;
    $UMID_IdNo = post('UMID_IdNo') ?: null;
    $PhilSys_IdNo = post('PhilSys_IdNo') ?: null;

    // --- Residential Address ---
    $ra_house_block_lotno = post('ra_house_block_lotno') ?: null;
    $ra_street = post('ra_street') ?: null;
    $ra_subdivisionvillage = post('ra_subdivisionvillage') ?: null;
    $ra_barangay = post('ra_barangay') ?: null;
    $ra_citymunicipality = post('ra_citymunicipality') ?: null;
    $ra_province = post('ra_province') ?: null;

    // --- Permanent Address ---
    $pa_house_block_lotno = post('pa_house_block_lotno') ?: null;
    $pa_street = post('pa_street') ?: null;
    $pa_subdivisionvillage = post('pa_subdivisionvillage') ?: null;
    $pa_barangay = post('pa_barangay') ?: null;
    $pa_citymunicipality = post('pa_citymunicipality') ?: null;
    $pa_province = post('pa_province') ?: null;

    // --- PHOTO HANDLING ---
    $photo_filename = null;
    if (!empty($_FILES['photo_file']['name'])) {
        $ext = pathinfo($_FILES['photo_file']['name'], PATHINFO_EXTENSION);
        $photo_filename = 'EMP_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['photo_file']['tmp_name'], __DIR__ . '/../uploads/' . $photo_filename);
    } elseif (!empty($_POST['photo']) && str_starts_with($_POST['photo'], 'data:image')) {
        $data = explode(',', $_POST['photo'])[1];
        $photo_filename = 'EMP_' . time() . '.png';
        file_put_contents(__DIR__ . '/../uploads/' . $photo_filename, base64_decode($data));
    }

    /* ----------------------------
       ✅ ADD EMPLOYEE
    ---------------------------- */
    if ($action === 'add') {

        $stmt = $mysqli->prepare("
            INSERT INTO employees (
                emp_idno, photo, email, dob, blood_type,
                department_id, position_id, employment_status_id,
                sss_no, gsis_no, tin_no, pagibig_no, phic_no,
                emergency_contact_person, emergency_contact_no,
                surname, first_name, middle_name, name_extension,
                place_of_birth, citizenship, sex, civil_status,
                height, weight,
                UMID_IdNo, PhilSys_IdNo, Agency_EmployeeNo,
                ra_house_block_lotno, ra_street, ra_subdivisionvillage,
                ra_barangay, ra_citymunicipality, ra_province,
                pa_house_block_lotno, pa_street, pa_subdivisionvillage,
                pa_barangay, pa_citymunicipality, pa_province,
                telephon_no, mobile_no, email_address
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ");

        $stmt->bind_param(
            "sssssiiisssssssssssssssssssssssssssssssssss",
            $emp_idno, $photo_filename, $email, $dob, $blood_type,
            $department_id, $position_id, $employment_status_id,
            $sss_no, $gsis_no, $tin_no, $pagibig_no, $phic_no,
            $emergency_contact_person, $emergency_contact_no,
            $surname, $first_name, $middle_name, $name_extension,
            $place_of_birth, $citizenship, $sex, $civil_status,
            $height, $weight,
            $UMID_IdNo, $PhilSys_IdNo, $Agency_EmployeeNo,
            $ra_house_block_lotno, $ra_street, $ra_subdivisionvillage,
            $ra_barangay, $ra_citymunicipality, $ra_province,
            $pa_house_block_lotno, $pa_street, $pa_subdivisionvillage,
            $pa_barangay, $pa_citymunicipality, $pa_province,
            $telephon_no, $mobile_no, $email_address
        );

        $stmt->execute();
        $stmt->close();
    }

    /* ----------------------------
       ✅ EDIT EMPLOYEE
    ---------------------------- */
    if ($action === 'edit') {

        $id = (int)($_POST['employee_id'] ?? 0);

        if (!$photo_filename) {
            $photo_filename = $mysqli->query("SELECT photo FROM employees WHERE id=$id")
                                     ->fetch_assoc()['photo'];
        }

        $stmt = $mysqli->prepare("
            UPDATE employees SET
                emp_idno=?, photo=?, email=?, dob=?, blood_type=?,
                department_id=?, position_id=?, employment_status_id=?,
                sss_no=?, gsis_no=?, tin_no=?, pagibig_no=?, phic_no=?,
                emergency_contact_person=?, emergency_contact_no=?,
                surname=?, first_name=?, middle_name=?, name_extension=?,
                place_of_birth=?, citizenship=?, sex=?, civil_status=?,
                height=?, weight=?,
                UMID_IdNo=?, PhilSys_IdNo=?, Agency_EmployeeNo=?,
                ra_house_block_lotno=?, ra_street=?, ra_subdivisionvillage=?,
                ra_barangay=?, ra_citymunicipality=?, ra_province=?,
                pa_house_block_lotno=?, pa_street=?, pa_subdivisionvillage=?,
                pa_barangay=?, pa_citymunicipality=?, pa_province=?,
                telephon_no=?, mobile_no=?, email_address=?
            WHERE id=?
        ");

        $stmt->bind_param(
            "sssssiiisssssssssssssssssssssssssssssssssssi",
            $emp_idno, $photo_filename, $email, $dob, $blood_type,
            $department_id, $position_id, $employment_status_id,
            $sss_no, $gsis_no, $tin_no, $pagibig_no, $phic_no,
            $emergency_contact_person, $emergency_contact_no,
            $surname, $first_name, $middle_name, $name_extension,
            $place_of_birth, $citizenship, $sex, $civil_status,
            $height, $weight,
            $UMID_IdNo, $PhilSys_IdNo, $Agency_EmployeeNo,
            $ra_house_block_lotno, $ra_street, $ra_subdivisionvillage,
            $ra_barangay, $ra_citymunicipality, $ra_province,
            $pa_house_block_lotno, $pa_street, $pa_subdivisionvillage,
            $pa_barangay, $pa_citymunicipality, $pa_province,
            $telephon_no, $mobile_no, $email_address,
            $id
        );

        $stmt->execute();
        $stmt->close();

        header("Location: employees.php");
        exit;
    }
}

/* ================================
   ✅ DELETE EMPLOYEE
================================ */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $mysqli->prepare("DELETE FROM employees WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

/* ================================
   ✅ PAGINATION & SEARCH
================================ */
$search = trim($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 15;
$offset = ($page - 1) * $limit;

$where = '';
$params = [];
$types = '';
if ($search) {
    $where = "WHERE emp_idno LIKE ? OR first_name LIKE ? OR surname LIKE ?";
    $like = "%$search%";
    $params = [$like, $like, $like];
    $types = "sss";
}

$total_users = 0;
if ($where) {
    $stmt = $mysqli->prepare("SELECT COUNT(*) AS total FROM employees $where");
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $total_users = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();
} else {
    $total_users = $mysqli->query("SELECT COUNT(*) AS total FROM employees")->fetch_assoc()['total'];
}

$sql = "SELECT e.*, d.name AS department_name, p.name AS position_name, es.name AS employment_status_name
        FROM employees e
        LEFT JOIN departments d ON e.department_id=d.id
        LEFT JOIN positions p ON e.position_id=p.id
        LEFT JOIN employment_status es ON e.employment_status_id=es.id
        $where
        ORDER BY e.id DESC
        LIMIT ? OFFSET ?";

$stmt = $mysqli->prepare($sql);
if ($where) {
    $stmt->bind_param($types . "ii", ...array_merge($params, [$limit, $offset]));
} else {
    $stmt->bind_param("ii", $limit, $offset);
}
$stmt->execute();
$employees = $stmt->get_result();
$stmt->close();

$total_pages = ceil($total_users / $limit);
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-people-fill"></i> Employees</h4>
        <div class="d-flex gap-2">
            <form class="d-flex" method="get">
                <input class="form-control me-2" type="search" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-outline-primary" type="submit"><i class="bi bi-search"></i></button>
            </form>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#employeeModal" onclick="openAddModal()">+ Add Employee</button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Photo</th>
                    <th>Employee ID</th>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Position</th>
                    <th>Status</th>
                    <th>Contact</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($employees->num_rows > 0): ?>
                    <?php while ($e = $employees->fetch_assoc()): ?>
                        <?php $fullName = trim($e['first_name'] . ' ' . $e['middle_name'] . ' ' . $e['surname'] . ' ' . $e['name_extension']); ?>
                        <tr>
                            <td><?= $e['id'] ?></td>
                            <td><img src="<?= $e['photo'] ? '../uploads/' . $e['photo'] : '../assets/default_user.png' ?>" width="50" height="50" class="rounded-circle"></td>
                            <td><?= htmlspecialchars($e['emp_idno']) ?></td>
                            <td><?= htmlspecialchars($fullName) ?></td>
                            <td><?= htmlspecialchars($e['department_name']) ?></td>
                            <td><?= htmlspecialchars($e['position_name']) ?></td>
                            <td><?= htmlspecialchars($e['employment_status_name']) ?></td>
                            <td><?= htmlspecialchars($e['mobile_no']) ?></td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-primary" onclick='openEditModal(<?= json_encode($e) ?>)'>
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <a href="print_id.php?id=<?= $e['id'] ?>" class="btn btn-sm btn-warning" target="_blank" title="Print ID">
                                    <i class="bi bi-printer"></i>
                                </a>
                                <a href="?delete=<?= $e['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this employee?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                                <button class="btn btn-sm btn-info" onclick="window.location='employee_profile.php?id=<?= $e['id'] ?>'">
                                    <i class="bi bi-person-lines-fill"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="9" class="text-center text-muted">No employees found</td></tr>
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

<?php include 'employee_modal.php'; ?>
<?php require_once __DIR__ . '/../layouts/footer.php'; ob_end_flush(); ?>
