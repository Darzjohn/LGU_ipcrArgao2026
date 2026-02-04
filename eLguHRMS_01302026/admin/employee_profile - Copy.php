<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';

// Get employee ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if(!$id){
    echo "<div class='alert alert-danger'>Invalid Employee ID</div>";
    exit;
}

// Fetch employee data
$stmt = $mysqli->prepare("SELECT * FROM employees WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if($result->num_rows === 0){
    echo "<div class='alert alert-warning'>Employee not found</div>";
    exit;
}
$emp = $result->fetch_assoc();
$stmt->close();

// Full Name
$fullName = trim($emp['first_name'].' '.$emp['middle_name'].' '.$emp['surname'].' '.$emp['name_extension']);
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-person-circle"></i> Employee Profile</h4>
        <a href="employees.php" class="btn btn-secondary">Back to Employees</a>
    </div>

    <div class="row g-3">
        <!-- Profile Card -->
        <div class="col-md-4">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <img src="<?= $emp['photo'] ? '../uploads/'.$emp['photo'] : '../assets/default_user.png' ?>" class="img-fluid rounded-circle mb-2" style="width:150px;height:150px;">
                    <h5 class="card-title"><?= htmlspecialchars($fullName) ?></h5>
                    <p class="text-muted"><?= htmlspecialchars($emp['emp_idno']) ?></p>
                    <div id="qrCodeProfile" class="mt-2"></div>
                </div>
            </div>

            <!-- Employee ID Card Preview -->
            <div class="card mt-3 p-3">
                <h6>ID Card Preview</h6>
                <div id="idCardPreviewProfile" class="border p-2 d-flex justify-content-between align-items-center">
                    <img src="<?= $emp['photo'] ? '../uploads/'.$emp['photo'] : '../assets/default_user.png' ?>" width="80" height="80" class="rounded-circle border">
                    <div>
                        <strong><?= htmlspecialchars($fullName) ?></strong><br>
                        <span><?= htmlspecialchars($emp['emp_idno']) ?></span>
                    </div>
                    <div id="qrCodeID"></div>
                </div>
                <button class="btn btn-sm btn-info mt-2" id="exportPDFProfile">Export PDF</button>
            </div>
        </div>

        <!-- Employee Details -->
        <div class="col-md-8">
            <div class="card shadow-sm p-3">
                <h5>Personal Information</h5>
                <table class="table table-bordered">
                    <tbody>
                        <tr><th>Full Name</th><td><?= htmlspecialchars($fullName) ?></td></tr>
                        <tr><th>Employee ID</th><td><?= htmlspecialchars($emp['emp_idno']) ?></td></tr>
                        <tr><th>Email</th><td><?= htmlspecialchars($emp['email']) ?></td></tr>
                        <tr><th>Contact</th><td><?= htmlspecialchars($emp['contact_no']) ?></td></tr>
                        <tr><th>Address</th><td><?= htmlspecialchars($emp['address']) ?></td></tr>
                        <tr><th>Date of Birth</th><td><?= htmlspecialchars($emp['dob']) ?></td></tr>
                        <tr><th>Blood Type</th><td><?= htmlspecialchars($emp['blood_type']) ?></td></tr>
                        <tr><th>Sex</th><td><?= htmlspecialchars($emp['sex']) ?></td></tr>
                        <tr><th>Civil Status</th><td><?= htmlspecialchars($emp['civil_status']) ?></td></tr>
                        <tr><th>Emergency Contact</th><td><?= htmlspecialchars($emp['emergency_contact_person'].' / '.$emp['emergency_contact_no']) ?></td></tr>
                        <!-- Add all other fields from your table as needed -->
                    </tbody>
                </table>

                <!-- Attendance History -->
                <?php
                $attendanceStmt = $mysqli->prepare("SELECT * FROM attendance WHERE emp_idno=? ORDER BY date DESC LIMIT 20");
                $attendanceStmt->bind_param("s", $emp['emp_idno']);
                $attendanceStmt->execute();
                $attResult = $attendanceStmt->get_result();
                ?>
                <h5>Attendance History</h5>
                <table class="table table-sm table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Date</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($attResult->num_rows>0): ?>
                            <?php while($a=$attResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $a['date'] ?></td>
                                    <td><?= $a['time_in'] ?></td>
                                    <td><?= $a['time_out'] ?></td>
                                    <td><?= $a['status'] ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center text-muted">No attendance records</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?php $attendanceStmt->close(); ?>
            </div>
        </div>
    </div>
</div>

<!-- Dependencies -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>

<script>
// Generate QR codes
new QRCode(document.getElementById("qrCodeProfile"), { text: "<?= $emp['emp_idno'] ?>", width: 100, height: 100 });
new QRCode(document.getElementById("qrCodeID"), { text: "<?= $emp['emp_idno'] ?>", width: 80, height: 80 });

// Export ID card PDF
document.getElementById('exportPDFProfile').addEventListener('click', () => {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    const card = document.getElementById('idCardPreviewProfile');
    html2canvas(card).then(canvas=>{
        const imgData = canvas.toDataURL('image/png');
        doc.addImage(imgData,'PNG',10,10,180,60);
        doc.save('Employee_ID_<?= $emp['emp_idno'] ?>.pdf');
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
