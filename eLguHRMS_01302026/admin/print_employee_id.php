<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) exit('Invalid employee');

$stmt = $mysqli->prepare("SELECT * FROM employees WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$employee) exit('Employee not found');

function full_name($e) {
    return trim($e['first_name'] . ' ' . $e['middle_name'] . ' ' . $e['surname'] . ' ' . $e['name_extension']);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Print Employee ID</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .id-card { width: 350px; height: 220px; border: 1px solid #000; margin: 20px auto; position: relative; page-break-after: always; }
        .front, .back { width: 100%; height: 100%; padding: 10px; box-sizing: border-box; }
        .front { background: url('<?= __DIR__ ?>/front_id.png') no-repeat center/cover; color: #fff; }
        .back { background: url('<?= __DIR__ ?>/back_id.png') no-repeat center/cover; color: #000; }
        .photo { width: 80px; height: 80px; border-radius: 50%; overflow: hidden; }
        .photo img { width: 100%; height: 100%; object-fit: cover; }
        .info { margin-left: 10px; display: inline-block; vertical-align: top; }
        .info strong { display: block; }
    </style>
</head>
<body>
    <div class="id-card front">
        <div class="photo">
            <img src="<?= $employee['photo'] ? '../uploads/' . $employee['photo'] : '../assets/default_user.png' ?>" alt="Photo">
        </div>
        <div class="info">
            <strong><?= htmlspecialchars(full_name($employee)) ?></strong>
            <span>ID No: <?= htmlspecialchars($employee['emp_idno']) ?></span>
            <span>Office: <?= htmlspecialchars($employee['position_id']) ?></span>
        </div>
    </div>

    <div class="id-card back">
        <div>
            <p><strong>Address:</strong> <?= htmlspecialchars($employee['ra_barangay'] . ', ' . $employee['ra_citymunicipality'] . ', ' . $employee['ra_province']) ?></p>
            <p><strong>Contact No:</strong> <?= htmlspecialchars($employee['mobile_no']) ?></p>
            <p><strong>Date of Birth:</strong> <?= htmlspecialchars($employee['dob']) ?></p>
            <p><strong>Blood Type:</strong> <?= htmlspecialchars($employee['blood_type']) ?></p>
            <p><strong>SSS:</strong> <?= htmlspecialchars($employee['sss_no']) ?></p>
            <p><strong>GSIS:</strong> <?= htmlspecialchars($employee['gsis_no']) ?></p>
            <p><strong>TIN:</strong> <?= htmlspecialchars($employee['tin_no']) ?></p>
            <p><strong>Pag-IBIG:</strong> <?= htmlspecialchars($employee['pagibig_no']) ?></p>
            <p><strong>PHIC:</strong> <?= htmlspecialchars($employee['phic_no']) ?></p>
            <p><strong>Emergency Contact:</strong> <?= htmlspecialchars($employee['emergency_contact_person'] . ' - ' . $employee['emergency_contact_no']) ?></p>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
