<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';

$messages = [];

// Get logged-in user's employee ID
$emp_idno = $_SESSION['emp_idno'] ?? null;

if (!$emp_idno) {
    echo '<div class="alert alert-warning">No Employee ID associated with your account.</div>';
    require_once __DIR__ . '/../layouts/footer.php';
    exit;
}

// Handle employee info update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_employee'])) {
    $name      = trim($_POST['name']);
    $email     = trim($_POST['email']);
    $contact   = trim($_POST['contact_no']);
    $address   = trim($_POST['address']);
    $dob       = trim($_POST['dob']);
    $blood     = trim($_POST['blood_type']);
    $sss_no    = trim($_POST['sss_no']);
    $gsis_no   = trim($_POST['gsis_no']);
    $tin_no    = trim($_POST['tin_no']);
    $pagibig   = trim($_POST['pagibig_no']);
    $phic_no   = trim($_POST['phic_no']);
    $emergency_name = trim($_POST['emergency_contact_person']);
    $emergency_no   = trim($_POST['emergency_contact_no']);

    $stmt = $mysqli->prepare("UPDATE employees SET name=?, email=?, contact_no=?, address=?, dob=?, blood_type=?, sss_no=?, gsis_no=?, tin_no=?, pagibig_no=?, phic_no=?, emergency_contact_person=?, emergency_contact_no=? WHERE emp_idno=?");
    $stmt->bind_param("ssssssssssssss", $name, $email, $contact, $address, $dob, $blood, $sss_no, $gsis_no, $tin_no, $pagibig, $phic_no, $emergency_name, $emergency_no, $emp_idno);
    $stmt->execute();
    $stmt->close();

    $messages[] = ['type' => 'success', 'text' => 'Employee information updated successfully!'];
}

// Fetch employee data
$stmt = $mysqli->prepare("SELECT * FROM employees WHERE emp_idno=?");
$stmt->bind_param("s", $emp_idno);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();
$stmt->close();

?>

<div class="container-fluid mt-4">
    <h4 class="mb-3">My Employee Information</h4>

    <?php foreach ($messages as $msg): ?>
        <div class="alert alert-<?= $msg['type'] ?>"><?= $msg['text'] ?></div>
    <?php endforeach; ?>

    <form method="post" class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Employee ID</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($employee['emp_idno']) ?>" readonly>
        </div>
        <div class="col-md-6">
            <label class="form-label">Full Name</label>
            <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($employee['name']) ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($employee['email']) ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Contact No.</label>
            <input type="text" class="form-control" name="contact_no" value="<?= htmlspecialchars($employee['contact_no']) ?>">
        </div>
        <div class="col-md-12">
            <label class="form-label">Address</label>
            <input type="text" class="form-control" name="address" value="<?= htmlspecialchars($employee['address']) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Date of Birth</label>
            <input type="date" class="form-control" name="dob" value="<?= $employee['dob'] ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Blood Type</label>
            <input type="text" class="form-control" name="blood_type" value="<?= htmlspecialchars($employee['blood_type']) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">SSS No.</label>
            <input type="text" class="form-control" name="sss_no" value="<?= htmlspecialchars($employee['sss_no']) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">GSIS No.</label>
            <input type="text" class="form-control" name="gsis_no" value="<?= htmlspecialchars($employee['gsis_no']) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">TIN No.</label>
            <input type="text" class="form-control" name="tin_no" value="<?= htmlspecialchars($employee['tin_no']) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Pag-IBIG No.</label>
            <input type="text" class="form-control" name="pagibig_no" value="<?= htmlspecialchars($employee['pagibig_no']) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">PHIC No.</label>
            <input type="text" class="form-control" name="phic_no" value="<?= htmlspecialchars($employee['phic_no']) ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Emergency Contact Person</label>
            <input type="text" class="form-control" name="emergency_contact_person" value="<?= htmlspecialchars($employee['emergency_contact_person']) ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Emergency Contact No</label>
            <input type="text" class="form-control" name="emergency_contact_no" value="<?= htmlspecialchars($employee['emergency_contact_no']) ?>">
        </div>

        <div class="col-12 text-end mt-2">
            <button type="submit" name="update_employee" class="btn btn-primary">Update Information</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
