<?php
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';

// Role restriction
if (!in_array($_SESSION['role'], ['admin','treasurer','cashier'])) {
    header("Location: ../index.php");
    exit;
}

// Smart query helper
if (!function_exists('q')) {
    function q($sql, $params = []) {
        global $mysqli;
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) die("SQL Prepare Error: " . $mysqli->error);

        if (!empty($params)) {
            $types = '';
            foreach ($params as $p) {
                if (is_int($p)) $types .= 'i';
                elseif (is_float($p) || is_double($p)) $types .= 'd';
                else $types .= 's';
            }
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt->execute()) die("SQL Exec Error: " . $stmt->error);
        return $stmt;
    }
}

if (isset($_POST['daily_form58_remittance']) && !empty($_POST['remittance_date'])) {

    $selected_date = $_POST['remittance_date'];
    $current_user = $_SESSION['name'] ?? '';
    $today = date('Y-m-d');
    $form_no = "58";

    // CHECK DUPLICATE REMITTANCE FOR THIS DATE + USER + FORM58
    $check = $mysqli->prepare("
        SELECT COUNT(*) AS c 
        FROM remittance 
        WHERE date_paid = ? AND form_no = ? AND created_by = ?
    ");
    $check->bind_param('sss', $selected_date, $form_no, $current_user);
    $check->execute();
    $exists = $check->get_result()->fetch_assoc()['c'];

    if ($exists > 0) {
        echo "<script>alert('This Date is Already Remitted');</script>";
        exit;
    }

    // FETCH FORM 58 RECORDS FOR THIS DATE
    $stmt = $mysqli->prepare("
        SELECT or_no, date_paid, amount_received, created_by
        FROM form58
        WHERE DATE(date_paid) = ? AND created_by = ?
    ");
    $stmt->bind_param('ss', $selected_date, $current_user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "<script>alert('No Form 58 records found for this date.');</script>";
        exit;
    }

    // INSERT INTO REMITTANCE TABLE
    $insert = $mysqli->prepare("
        INSERT INTO remittance (form_no, or_no, total_paid, date_paid, created_by, remittance_date)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    while ($row = $result->fetch_assoc()) {
        $or_no        = $row['or_no'];
        $total_paid   = $row['amount_received']; 
        $date_paid    = $row['date_paid'];
        $created_by   = $row['created_by'];

        $insert->bind_param('ssdsss', 
            $form_no, 
            $or_no, 
            $total_paid, 
            $date_paid, 
            $created_by, 
            $today
        );
        $insert->execute();
    }

    echo "<script>alert('Daily Form 58 Remittance Successfully Created!'); 
          window.location.href='form58_list.php?date_paid=$selected_date';
          </script>";
    exit;
}


// Initialize
$form58_records = [];

// Handle Add
if (isset($_POST['add_form58'])) {
    $date_of_death = $_POST['date_of_death'] ?: null;
    $payment_date = $_POST['payment_date'] ?: null;

    q(
        "INSERT INTO form58 (
            or_no, date_paid, payor_name, city_or_municipality, province,
            name_of_deceased, nationality, age, sex, date_of_death, case_of_death,
            name_of_cemetery, infectious_or_noninfectious, embalmed_or_notembalmed,
            disposition_of_remains, amount_of_fee, payment_date, amount_received, treasurer, created_by, created_at
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, ?, NOW())",
        [
            $_POST['or_no'], $_POST['date_paid'], $_POST['payor_name'], $_POST['city_or_municipality'], $_POST['province'],
            $_POST['name_of_deceased'], $_POST['nationality'], $_POST['age'], $_POST['sex'], $date_of_death, $_POST['case_of_death'],
            $_POST['name_of_cemetery'], $_POST['infectious_or_noninfectious'], $_POST['embalmed_or_notembalmed'],
            $_POST['disposition_of_remains'], $_POST['amount_of_fee'], $payment_date, $_POST['amount_received'], $_SESSION['name'], $_SESSION['name']
        ]
    );
    echo "<script>alert('Form 58 added successfully!'); location.href='form58_list.php';</script>";
    exit;
}

// Handle Edit
if (isset($_POST['edit_form58'])) {
    $date_of_death = $_POST['date_of_death'] ?: null;
    $payment_date = $_POST['payment_date'] ?: null;

    q(
        "UPDATE form58 SET
            or_no=?, date_paid=?, payor_name=?, city_or_municipality=?, province=?,
            name_of_deceased=?, nationality=?, age=?, sex=?, date_of_death=?, case_of_death=?,
            name_of_cemetery=?, infectious_or_noninfectious=?, embalmed_or_notembalmed=?,
            disposition_of_remains=?, amount_of_fee=?, payment_date=?, amount_received=?, treasurer=?, updated_by=?, updated_at=NOW()
         WHERE id=?",
        [
            $_POST['or_no'], $_POST['date_paid'], $_POST['payor_name'], $_POST['city_or_municipality'], $_POST['province'],
            $_POST['name_of_deceased'], $_POST['nationality'], $_POST['age'], $_POST['sex'], $date_of_death, $_POST['case_of_death'],
            $_POST['name_of_cemetery'], $_POST['infectious_or_noninfectious'], $_POST['embalmed_or_notembalmed'],
            $_POST['disposition_of_remains'], $_POST['amount_of_fee'], $payment_date, $_POST['amount_received'], $_SESSION['name'], $_SESSION['name'], $_POST['form58_id']
        ]
    );
    echo "<script>alert('Form 58 updated successfully!'); location.href='form58_list.php';</script>";
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    q("DELETE FROM form58 WHERE id=?", [intval($_GET['delete'])]);
    echo "<script>alert('Form 58 deleted successfully!'); location.href='form58_list.php';</script>";
    exit;
}

// Search / Filter
$where = "WHERE created_by = ?";
$params = [$_SESSION['name']]; // filter by current logged-in user

if (!empty($_GET['search'])) {
    $where .= " AND (or_no LIKE ? OR payor_name LIKE ? OR name_of_deceased LIKE ?)";
    $search = "%" . $_GET['search'] . "%";
    array_push($params, $search, $search, $search);
}

if (!empty($_GET['date_paid'])) {
    $where .= " AND date_paid = ?";
    array_push($params, $_GET['date_paid']);
}

$sql = "SELECT * FROM form58 $where ORDER BY id DESC";
$stmt = q($sql, $params);
$res = $stmt->get_result();
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) $form58_records[] = $row;
}
?>

<div class="container-fluid mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-file-text"></i> Form 58 - Burial Permit Collections</h4>
    
       <div class="d-flex gap-2 mb-3">
    <!-- NEW FORM 58 BUTTON -->
    <!-- <a href="form58_list.php" class="btn btn-success">➕ New Form 58</a> -->
<button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addForm58Modal">➕ New Form 58</button>
  <!-- DAILY REMITTANCE BUTTON -->
    <?php if (!empty($_GET['date_paid'])): ?>
        <form method="POST">
            <input type="hidden" name="remittance_date" value="<?= $_GET['date_paid'] ?>">
            <button name="daily_form58_remittance" class="btn btn-warning">
                📤 Daily Form 58 Remittance
            </button>
        </form>
    <?php else: ?>
        <button class="btn btn-warning" disabled>📤 Daily Form 58 Remittance</button>
    <?php endif; ?>
    <!-- PRINT DAILY ABSTRACT BUTTON -->
    <?php if (!empty($_GET['date_paid'])): ?>
        <a href="print_form58_daily_abstract.php?date=<?= urlencode($_GET['date_paid']) ?>" 
           target="_blank" 
           class="btn btn-outline-primary">
            🖨️ Print Form 58 Daily Abstract
        </a>
    <?php else: ?>
        <button class="btn btn-outline-primary" disabled>🖨️ Print Daily Abstract</button>
    <?php endif; ?>

  
</div>

  </div>

  <!-- Search -->
  <form class="row mb-3" method="get">
    <div class="col-md-4">
      <input type="text" name="search" class="form-control" placeholder="Search OR No, Payor, Deceased"
             value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
    </div>

    <div class="col-md-2">
      <input type="date" name="date_paid" class="form-control"
             value="<?= htmlspecialchars($_GET['date_paid'] ?? '') ?>">
    </div>
    
    <div class="col-md-2">
      <button class="btn btn-primary w-100">Filter</button>
    </div>
    <div class="col-md-2">
      <a href="form58_list.php" class="btn btn-secondary w-100">Reset</a>
    </div>
  </form>

  <!-- Table -->
  <div class="card">
    <div class="card-body table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-dark text-center">
          <tr>
            <th>ID</th><th>OR No</th><th>Payor Name</th><th>Name of Deceased</th><th>Amount Received</th><th>Date Paid</th><th>Cashier/Treasurer</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($form58_records)): foreach ($form58_records as $r): ?>
          <tr class="text-center">
            <td><?= $r['id'] ?></td>
            <td><strong><?= htmlspecialchars($r['or_no']) ?></strong></td>
            <td><?= htmlspecialchars($r['payor_name']) ?></td>
            <td><?= htmlspecialchars($r['name_of_deceased']) ?></td>
            <td>₱<?= number_format($r['amount_received'],2) ?></td>
            <td><?= htmlspecialchars($r['date_paid']) ?></td>
            <td><?= htmlspecialchars($r['treasurer'] ?? $_SESSION['name']) ?></td>
            <td>
              <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $r['id'] ?>">✏️</button>
              <a href="?delete=<?= $r['id'] ?>" onclick="return confirm('Delete this record?')" class="btn btn-sm btn-danger">🗑️</a>
              <a href="form58_print.php?id=<?= $r['id'] ?>" target="_blank" class="btn btn-sm btn-primary">🖨️</a>
            </td>
          </tr>
          <?php endforeach; else: ?>
          <tr><td colspan="8" class="text-center text-muted">No Form 58 records found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modals -->
<?php require_once __DIR__ . '/form58_modals.php'; ?>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
<?php ob_end_flush(); ?>
