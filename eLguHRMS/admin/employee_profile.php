<?php
ob_start();
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';

// Only admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Include PHP QR Code
require_once __DIR__ . '/../libraries/phpqrcode/qrlib.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: employees.php");
    exit;
}

// Fetch employee
$stmt = $mysqli->prepare("SELECT e.*, d.name AS department_name, p.name AS position_name 
                          FROM employees e 
                          LEFT JOIN departments d ON e.department_id=d.id 
                          LEFT JOIN positions p ON e.position_id=p.id
                          WHERE e.id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$employee) {
    header("Location: employees.php");
    exit;
}

// QR code
$qrFile = null;
if (function_exists('imagecreate')) {
    $qrFile = __DIR__ . "/../uploads/qr_{$employee['id']}.png";
    if (!file_exists($qrFile)) {
        QRcode::png($employee['emp_idno'], $qrFile, 'L', 4, 2);
    }
}

function full_name($e) {
    return trim($e['first_name'] . ' ' . $e['middle_name'] . ' ' . $e['surname'] . ' ' . $e['name_extension']);
}

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-person-lines-fill"></i> Employee Profile</h4>
        <div class="d-flex gap-2">
            <a href="employees.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#employeeModal" 
                    onclick='openEditModal(<?= json_encode($employee) ?>)'>
                <i class="bi bi-pencil-square"></i> Edit
            </button>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-3 text-center">
            <img src="<?= $employee['photo'] ? '../uploads/' . $employee['photo'] : '../assets/default_user.png' ?>" 
                 class="img-fluid rounded mb-2" style="max-height:250px;">
            <?php if ($qrFile): ?>
                <img src="<?= '../uploads/qr_' . $employee['id'] . '.png' ?>" class="img-fluid mt-2" alt="QR Code">
            <?php endif; ?>
            <h5 class="mt-2"><?= htmlspecialchars(full_name($employee)) ?></h5>
            <p class="text-muted"><?= htmlspecialchars($employee['emp_idno']) ?></p>
        </div>

        <div class="col-lg-9">
            <!-- Personal Info Card -->
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">Personal Information</div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-6"><strong>First Name:</strong> <?= htmlspecialchars($employee['first_name']) ?></div>
                        <div class="col-md-6"><strong>Middle Name:</strong> <?= htmlspecialchars($employee['middle_name']) ?></div>
                        <div class="col-md-6"><strong>Surname:</strong> <?= htmlspecialchars($employee['surname']) ?></div>
                        <div class="col-md-6"><strong>Name Extension:</strong> <?= htmlspecialchars($employee['name_extension']) ?></div>
                        <div class="col-md-6"><strong>Sex:</strong> <?= htmlspecialchars($employee['sex']) ?></div>
                        <div class="col-md-6"><strong>Civil Status:</strong> <?= htmlspecialchars($employee['civil_status']) ?></div>
                        <div class="col-md-6"><strong>Date of Birth:</strong> <?= htmlspecialchars($employee['dob']) ?></div>
                        <div class="col-md-6"><strong>Place of Birth:</strong> <?= htmlspecialchars($employee['place_of_birth']) ?></div>
                        <div class="col-md-6"><strong>Blood Type:</strong> <?= htmlspecialchars($employee['blood_type']) ?></div>
                        <div class="col-md-6"><strong>Height (cm):</strong> <?= htmlspecialchars($employee['height']) ?></div>
                        <div class="col-md-6"><strong>Weight (kg):</strong> <?= htmlspecialchars($employee['weight']) ?></div>
                        <div class="col-md-6"><strong>Citizenship:</strong> <?= htmlspecialchars($employee['citizenship']) ?></div>
                    </div>
                </div>
            </div>

            <!-- Contact & Address Card -->
            <div class="card mb-3">
                <div class="card-header bg-success text-white">Contact & Address</div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-6"><strong>Email:</strong> <?= htmlspecialchars($employee['email']) ?></div>
                        <div class="col-md-6"><strong>Alternate Email:</strong> <?= htmlspecialchars($employee['email_address']) ?></div>
                        <div class="col-md-6"><strong>Telephone:</strong> <?= htmlspecialchars($employee['telephon_no']) ?></div>
                        <div class="col-md-6"><strong>Mobile:</strong> <?= htmlspecialchars($employee['mobile_no']) ?></div>
                        <div class="col-md-6"><strong>Emergency Contact Person:</strong> <?= htmlspecialchars($employee['emergency_contact_person']) ?></div>
                        <div class="col-md-6"><strong>Emergency Contact No.:</strong> <?= htmlspecialchars($employee['emergency_contact_no']) ?></div>

                        <div class="col-12 mt-2"><strong>Residential Address:</strong></div>
                        <div class="col-md-6"><?= htmlspecialchars($employee['ra_house_block_lotno'] . ', ' . $employee['ra_street']) ?></div>
                        <div class="col-md-6"><?= htmlspecialchars($employee['ra_subdivisionvillage'] . ', ' . $employee['ra_barangay']) ?></div>
                        <div class="col-md-6"><?= htmlspecialchars($employee['ra_citymunicipality'] . ', ' . $employee['ra_province']) ?></div>

                        <div class="col-12 mt-2"><strong>Permanent Address:</strong></div>
                        <div class="col-md-6"><?= htmlspecialchars($employee['pa_house_block_lotno'] . ', ' . $employee['pa_street']) ?></div>
                        <div class="col-md-6"><?= htmlspecialchars($employee['pa_subdivisionvillage'] . ', ' . $employee['pa_barangay']) ?></div>
                        <div class="col-md-6"><?= htmlspecialchars($employee['pa_citymunicipality'] . ', ' . $employee['pa_province']) ?></div>
                    </div>
                </div>
            </div>

            <!-- Employment & Gov IDs Card -->
            <div class="card mb-3">
                <div class="card-header bg-warning text-dark">Employment & Government IDs</div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-6"><strong>Department:</strong> <?= htmlspecialchars($employee['department_name']) ?></div>
                        <div class="col-md-6"><strong>Position:</strong> <?= htmlspecialchars($employee['position_name']) ?></div>
                        <div class="col-md-6"><strong>Agency Employee No.:</strong> <?= htmlspecialchars($employee['Agency_EmployeeNo']) ?></div>
                        <div class="col-md-6"><strong>SSS:</strong> <?= htmlspecialchars($employee['sss_no']) ?></div>
                        <div class="col-md-6"><strong>GSIS:</strong> <?= htmlspecialchars($employee['gsis_no']) ?></div>
                        <div class="col-md-6"><strong>TIN:</strong> <?= htmlspecialchars($employee['tin_no']) ?></div>
                        <div class="col-md-6"><strong>Pag-IBIG:</strong> <?= htmlspecialchars($employee['pagibig_no']) ?></div>
                        <div class="col-md-6"><strong>PHIC:</strong> <?= htmlspecialchars($employee['phic_no']) ?></div>
                        <div class="col-md-6"><strong>UMID:</strong> <?= htmlspecialchars($employee['UMID_IdNo']) ?></div>
                        <div class="col-md-6"><strong>PhilSys:</strong> <?= htmlspecialchars($employee['PhilSys_IdNo']) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<?php
include 'employee_modal.php'; // modal used for Add/Edit
require_once __DIR__ . '/../layouts/footer.php';
ob_end_flush();
?>

