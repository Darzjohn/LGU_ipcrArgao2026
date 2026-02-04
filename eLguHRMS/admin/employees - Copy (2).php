<?php
session_start();
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';

// Only admin or HR can access
if (!in_array($_SESSION['role'], ['admin','hr','hr_staff'])) {
    header("Location: ../index.php");
    exit;
}

// Handle Add/Edit/Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $emp_idno = trim($_POST['emp_idno']);
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $surname = trim($_POST['surname']);
    $name_extension = trim($_POST['name_extension']);
    $email = trim($_POST['email']);
    $contact_no = trim($_POST['contact_no']);
    $address = trim($_POST['address']);
    $dob = $_POST['dob'] ?: NULL;
    $blood_type = $_POST['blood_type'];
    $department_id = $_POST['department_id'] ?: NULL;
    $position_id = $_POST['position_id'] ?: NULL;
    $sss_no = $_POST['sss_no'];
    $gsis_no = $_POST['gsis_no'];
    $tin_no = $_POST['tin_no'];
    $pagibig_no = $_POST['pagibig_no'];
    $phic_no = $_POST['phic_no'];
    $emergency_contact_person = $_POST['emergency_contact_person'];
    $emergency_contact_no = $_POST['emergency_contact_no'];
    $place_of_birth = $_POST['place_of_birth'];
    $citizenship = $_POST['citizenship'];
    $sex = $_POST['sex'];
    $civil_status = $_POST['civil_status'];
    $civil_status_specify = $_POST['civil_status_specify'];
    $height = $_POST['height'] ?: NULL;
    $weight = $_POST['weight'] ?: NULL;
    $UMID_IdNo = $_POST['UMID_IdNo'];
    $PhilSys_IdNo = $_POST['PhilSys_IdNo'];
    $Agency_EmployeeNo = $_POST['Agency_EmployeeNo'];
    $ra_house_block_lotno = $_POST['ra_house_block_lotno'];
    $ra_street = $_POST['ra_street'];
    $ra_subdivisionvillage = $_POST['ra_subdivisionvillage'];
    $ra_barangay = $_POST['ra_barangay'];
    $ra_citymunicipality = $_POST['ra_citymunicipality'];
    $ra_province = $_POST['ra_province'];
    $pa_house_block_lotno = $_POST['pa_house_block_lotno'];
    $pa_street = $_POST['pa_street'];
    $pa_subdivisionvillage = $_POST['pa_subdivisionvillage'];
    $pa_barangay = $_POST['pa_barangay'];
    $pa_citymunicipality = $_POST['pa_citymunicipality'];
    $pa_province = $_POST['pa_province'];
    $telephon_no = $_POST['telephon_no'];
    $mobile_no = $_POST['mobile_no'];
    $email_address = $_POST['email_address'];
    $photo = $_POST['photo'] ?? NULL; // Base64 image

    // Combine name for stored generated column
    $full_name = trim("$first_name $middle_name $surname $name_extension");

    if ($action === 'add') {
        // Save photo if exists
        if ($photo) {
            $img_data = explode(',', $photo)[1];
            $img_name = 'EMP_'.time().'.png';
            file_put_contents(__DIR__."/../uploads/$img_name", base64_decode($img_data));
        } else {
            $img_name = NULL;
        }

        $stmt = $mysqli->prepare("
            INSERT INTO employees (
                emp_idno, photo, email, contact_no, address, dob, blood_type, 
                department_id, position_id, sss_no, gsis_no, tin_no, pagibig_no, phic_no, 
                emergency_contact_person, emergency_contact_no, first_name, middle_name, surname, name_extension, 
                place_of_birth, citizenship, sex, civil_status, civil_status_specify, height, weight,
                UMID_IdNo, PhilSys_IdNo, Agency_EmployeeNo,
                ra_house_block_lotno, ra_street, ra_subdivisionvillage, ra_barangay, ra_citymunicipality, ra_province,
                pa_house_block_lotno, pa_street, pa_subdivisionvillage, pa_barangay, pa_citymunicipality, pa_province,
                telephon_no, mobile_no, email_address
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )
        ");
        $stmt->bind_param("ssssssiiissssssssssssssssssssssssssssssssssss",
            $emp_idno, $img_name, $email, $contact_no, $address, $dob, $blood_type,
            $department_id, $position_id, $sss_no, $gsis_no, $tin_no, $pagibig_no, $phic_no,
            $emergency_contact_person, $emergency_contact_no, $first_name, $middle_name, $surname, $name_extension,
            $place_of_birth, $citizenship, $sex, $civil_status, $civil_status_specify, $height, $weight,
            $UMID_IdNo, $PhilSys_IdNo, $Agency_EmployeeNo,
            $ra_house_block_lotno, $ra_street, $ra_subdivisionvillage, $ra_barangay, $ra_citymunicipality, $ra_province,
            $pa_house_block_lotno, $pa_street, $pa_subdivisionvillage, $pa_barangay, $pa_citymunicipality, $pa_province,
            $telephon_no, $mobile_no, $email_address
        );
        $stmt->execute();
        $stmt->close();
    }

    if ($action === 'edit') {
        $employee_id = (int)$_POST['employee_id'];
        // Save photo if exists
        if ($photo) {
            $img_data = explode(',', $photo)[1];
            $img_name = 'EMP_'.time().'.png';
            file_put_contents(__DIR__."/../uploads/$img_name", base64_decode($img_data));
            $stmt = $mysqli->prepare("UPDATE employees SET photo=? WHERE id=?");
            $stmt->bind_param("si",$img_name,$employee_id);
            $stmt->execute();
            $stmt->close();
        }
        // Update remaining fields
        $stmt = $mysqli->prepare("
            UPDATE employees SET
                emp_idno=?, email=?, contact_no=?, address=?, dob=?, blood_type=?, 
                department_id=?, position_id=?, sss_no=?, gsis_no=?, tin_no=?, pagibig_no=?, phic_no=?, 
                emergency_contact_person=?, emergency_contact_no=?,
                first_name=?, middle_name=?, surname=?, name_extension=?,
                place_of_birth=?, citizenship=?, sex=?, civil_status=?, civil_status_specify=?, height=?, weight=?,
                UMID_IdNo=?, PhilSys_IdNo=?, Agency_EmployeeNo=?,
                ra_house_block_lotno=?, ra_street=?, ra_subdivisionvillage=?, ra_barangay=?, ra_citymunicipality=?, ra_province=?,
                pa_house_block_lotno=?, pa_street=?, pa_subdivisionvillage=?, pa_barangay=?, pa_citymunicipality=?, pa_province=?,
                telephon_no=?, mobile_no=?, email_address=?
            WHERE id=?
        ");
        $stmt->bind_param("ssssssiiisssssssssssssssssssssssssssssssssi",
            $emp_idno, $email, $contact_no, $address, $dob, $blood_type,
            $department_id, $position_id, $sss_no, $gsis_no, $tin_no, $pagibig_no, $phic_no,
            $emergency_contact_person, $emergency_contact_no,
            $first_name, $middle_name, $surname, $name_extension,
            $place_of_birth, $citizenship, $sex, $civil_status, $civil_status_specify, $height, $weight,
            $UMID_IdNo, $PhilSys_IdNo, $Agency_EmployeeNo,
            $ra_house_block_lotno, $ra_street, $ra_subdivisionvillage, $ra_barangay, $ra_citymunicipality, $ra_province,
            $pa_house_block_lotno, $pa_street, $pa_subdivisionvillage, $pa_barangay, $pa_citymunicipality, $pa_province,
            $telephon_no, $mobile_no, $email_address,
            $employee_id
        );
        $stmt->execute();
        $stmt->close();
    }
}

// Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $mysqli->prepare("DELETE FROM employees WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// Fetch all employees
$employees = $mysqli->query("SELECT * FROM employees ORDER BY id DESC");

?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-people-fill"></i> Employee Management</h4>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#employeeModal" onclick="openAddModal()">+ Add Employee</button>
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
                    <th>Contact</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while($row = $employees->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td>
                        <?php if($row['photo']): ?>
                            <img src="../uploads/<?= $row['photo'] ?>" width="50" height="50" class="rounded-circle">
                        <?php else: ?>
                            <span class="text-muted">No Photo</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($row['emp_idno']) ?></td>
                    <td><?= htmlspecialchars($row['first_name'].' '.$row['middle_name'].' '.$row['surname'].' '.$row['name_extension']) ?></td>
                    <td><?= getDepartmentName($row['department_id'],$mysqli) ?></td>
                    <td><?= getPositionName($row['position_id'],$mysqli) ?></td>
                    <td><?= htmlspecialchars($row['contact_no']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick='openEditModal(<?= json_encode($row) ?>)'><i class="bi bi-pencil-square"></i></button>
                        <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this employee?');"><i class="bi bi-trash"></i></a>
                        <button class="btn btn-sm btn-info" onclick="openProfileModal(<?= $row['id'] ?>)"><i class="bi bi-card-list"></i></button>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/employee_modal.php'; ?>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
