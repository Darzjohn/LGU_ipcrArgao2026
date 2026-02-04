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

// Handle Daily Form51 General Collection Remittance
if (isset($_POST['daily_form51_remittance']) && !empty($_POST['remittance_date'])) {
    $selected_date = $_POST['remittance_date'];
    $current_user = $_SESSION['name'] ?? '';
    $today = date('Y-m-d');
    $form_no = "51"; // Form51 form_no

    // Check if remittance already exists for this date & collector
    $check_stmt = $mysqli->prepare("SELECT COUNT(*) AS count FROM remittance WHERE date_paid = ? AND form_no = ? AND created_by = ?");
    $check_stmt->bind_param('sss', $selected_date, $form_no, $current_user);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result()->fetch_assoc();

    if ($check_result['count'] > 0) {
        echo "<script>alert('This Date is Already Remitted'); location.href='form51_list.php?date_issued=$selected_date';</script>";
        exit;
    }

    // Fetch all Form51 records for the selected date by the current user
    $stmt = $mysqli->prepare("SELECT or_no, date_issued, grand_total, created_by FROM form51 WHERE DATE(date_issued) = ? AND created_by = ?");
    $stmt->bind_param('ss', $selected_date, $current_user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $insert_stmt = $mysqli->prepare("INSERT INTO remittance (form_no, or_no, total_paid, date_paid, created_by, remittance_date) VALUES (?, ?, ?, ?, ?, ?)");
        while ($row = $result->fetch_assoc()) {
            $or_no = $row['or_no'];
            $total_paid = $row['grand_total'];
            $date_paid = $row['date_issued'];
            $created_by = $row['created_by'];
            $insert_stmt->bind_param('ssdsss', $form_no, $or_no, $total_paid, $date_paid, $created_by, $today);
            $insert_stmt->execute();
        }
        $insert_stmt->close();
        echo "<script>alert('Daily Form51 General Collection Remittance successfully created!'); location.href='form51_list.php?date_issued=$selected_date';</script>";
        exit;
    } else {
        echo "<script>alert('No Form51 records found for this date.');</script>";
    }
}



// Fetch NGAS items for dropdown
$ngas_items = [];
$ngas_res = $mysqli->query("SELECT id, ngas_code, nature_of_collection, set_fix_amount 
                            FROM ngas_settings 
                            WHERE status='enable' 
                            ORDER BY ngas_code ASC");
while ($row = $ngas_res->fetch_assoc()) $ngas_items[] = $row;

// // AJAX: Suggested OR
// if (isset($_GET['get_suggested_or'])) {
//     $stmt = $mysqli->prepare("SELECT MAX(or_no) AS max_or FROM form51");
//     $stmt->execute();
//     $res = $stmt->get_result()->fetch_assoc();
//     $max_or = $res['max_or'] ?? 0;
//     echo intval($max_or) + 1;
//     exit;
// }

// Add Form 51
if (isset($_POST['add_form51'])) {
    $or_no = $_POST['or_no'];
    $check_date = empty($_POST['check_date']) ? null : $_POST['check_date'];

    // Duplicate check
    $stmt = $mysqli->prepare("SELECT COUNT(*) as cnt FROM form51 WHERE or_no=?");
    $stmt->bind_param('i', $or_no);
    $stmt->execute();
    $count = $stmt->get_result()->fetch_assoc()['cnt'];
    if ($count > 0) {
        echo "<script>alert('Duplicate OR No! Please use another OR No.'); location.href='form51_list.php';</script>";
        exit;
    }

    // Insert new record
    q(
        "INSERT INTO form51 (or_no, date_issued, payor_name, address, payment_mode, total_cash_paid, check_number, bank_name, check_date, check_amount, treasurer, grand_total, created_by, created_at)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())",
        [
            $or_no, $_POST['date_issued'], $_POST['payor_name'], $_POST['address'] ?? '',
            $_POST['payment_mode'] ?? 'cash',
            $_POST['total_cash_paid'] ?? 0,
            $_POST['check_number'] ?? '',
            $_POST['bank_name'] ?? '',
            $check_date,
            $_POST['check_amount'] ?? 0,
            $_SESSION['name'],
            $_POST['grand_total'] ?? 0,
            $_SESSION['name']
        ]
    );
    $form51_id = $mysqli->insert_id;

    // Save items
    if(!empty($_POST['ngas_id'])){
        foreach($_POST['ngas_id'] as $i => $ngas_id){
            $amount = $_POST['amount'][$i] ?? 0;
            if(!empty($ngas_id) && $amount>0){
                q("INSERT INTO form51_items (form51_id, ngas_id, amount) VALUES (?,?,?)",
                    [$form51_id, $ngas_id, $amount]);
            }
        }
    }

    echo "<script>alert('Form 51 added successfully!'); location.href='form51_list.php';</script>";
    exit;
}

// Edit Form 51
if (isset($_POST['edit_form51'])) {
    $check_date = empty($_POST['check_date']) ? null : $_POST['check_date'];

    q(
        "UPDATE form51 
         SET or_no=?, date_issued=?, payor_name=?, address=?, payment_mode=?, total_cash_paid=?, check_number=?, bank_name=?, check_date=?, check_amount=?, treasurer=?, grand_total=?, updated_by=?, updated_at=NOW()
         WHERE id=?",
        [
            $_POST['or_no'], $_POST['date_issued'], $_POST['payor_name'], $_POST['address'] ?? '',
            $_POST['payment_mode'] ?? 'cash',
            $_POST['total_cash_paid'] ?? 0,
            $_POST['check_number'] ?? '',
            $_POST['bank_name'] ?? '',
            $check_date,
            $_POST['check_amount'] ?? 0,
            $_SESSION['name'],
            $_POST['grand_total'] ?? 0,
            $_SESSION['name'],
            $_POST['form51_id']
        ]
    );
    $form51_id = $_POST['form51_id'];

    // Remove old items
    q("DELETE FROM form51_items WHERE form51_id=?", [$form51_id]);

    // Save new items
    if(!empty($_POST['ngas_id'])){
        foreach($_POST['ngas_id'] as $i => $ngas_id){
            $amount = $_POST['amount'][$i] ?? 0;
            if(!empty($ngas_id) && $amount>0){
                q("INSERT INTO form51_items (form51_id, ngas_id, amount) VALUES (?,?,?)",
                    [$form51_id, $ngas_id, $amount]);
            }
        }
    }

    echo "<script>alert('Form 51 updated successfully!'); location.href='form51_list.php';</script>";
    exit;
}

// Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    q("DELETE FROM form51_items WHERE form51_id=?", [$id]);
    q("DELETE FROM form51 WHERE id=?", [$id]);
    echo "<script>alert('Form 51 deleted successfully!'); location.href='form51_list.php';</script>";
    exit;
}

// Search / Filter — show only records for current user
$where = "WHERE created_by = ?";
$params = [$_SESSION['name']]; // bind current user's name

if (!empty($_GET['search'])) {
    $where .= " AND (or_no LIKE ? OR payor_name LIKE ?)";
    $search = "%".$_GET['search']."%";
    array_push($params, $search, $search);
}
if (!empty($_GET['date_issued'])) {
    $where .= " AND date_issued = ?";
    array_push($params, $_GET['date_issued']);
}

// Fetch records
$form51_records = [];
$stmt = q("SELECT * FROM form51 $where ORDER BY id DESC", $params);
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $form51_records[] = $row;



?>

<div class="container-fluid mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-receipt"></i> Form 51 - General Collections</h4>
    <!-- <div class="d-flex gap-2">
  <button id="openAddFormBtn" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addForm51Modal">➕ New Form 51</button>
  
  <?php if (!empty($_GET['date_issued'])): ?>
    <a href="print_form51_daily_abstract.php?date=<?= urlencode($_GET['date_issued']) ?>" 
       target="_blank" 
       class="btn btn-outline-primary">
       🖨️ Print Gen Collection Daily Abstract
    </a>
  <?php else: ?>
    <button class="btn btn-outline-primary" disabled>🖨️ Print Gen Collection Daily Abstract</button>
  <?php endif; ?>
</div> -->

<div class="d-flex gap-2 mb-3">
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addForm51Modal">➕ New Form51</button>

    <?php if (!empty($_GET['date_issued'])): ?>
        <!-- Daily Form51 General Collection Remittance Button -->
        <form method="post" style="display:inline;">
            <input type="hidden" name="remittance_date" value="<?= htmlspecialchars($_GET['date_issued']) ?>">
            <button type="submit" name="daily_form51_remittance" class="btn btn-primary">
                💰 Daily Form51 General Collection Remittance
            </button>
        </form>

        <!-- Print General Collection Daily Abstract -->
        <a href="print_form51_daily_abstract.php?date=<?= urlencode($_GET['date_issued']) ?>" 
           target="_blank" 
           class="btn btn-outline-primary">
           🖨️ Print Gen Collection Daily Abstract
        </a>
    <?php else: ?>
        <button class="btn btn-primary" disabled>💰 Daily Form51 General Collection Remittance</button>
        <button class="btn btn-outline-primary" disabled>🖨️ Print Gen Collection Daily Abstract</button>
    <?php endif; ?>
</div>


  </div>

  <!-- Search & Filter -->
  <form class="row mb-3" method="get">
    <div class="col-md-4">
      <input type="text" name="search" class="form-control" placeholder="Search by OR No or Payor Name"
             value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
    </div>
    <div class="col-md-3">
      <input type="date" name="date_issued" class="form-control"
             value="<?= htmlspecialchars($_GET['date_issued'] ?? '') ?>">
    </div>
    <div class="col-md-2">
      <button class="btn btn-primary w-100">Filter</button>
    </div>
    <div class="col-md-2">
      <a href="form51_list.php" class="btn btn-secondary w-100">Reset</a>
    </div>
  </form>

  <!-- Data Table -->
  <div class="card">
    <div class="card-body table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-dark text-center">
          <tr>
            <th>ID</th><th>OR No</th><th>Payor Name</th><th>Address</th><th>Grand Total</th><th>Date Issued</th><th>Cashier/Treasurer</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if(!empty($form51_records)): foreach($form51_records as $r): ?>
          <tr class="text-center">
            <td><?= $r['id'] ?></td>
            <td><strong><?= htmlspecialchars($r['or_no']) ?></strong></td>
            <td><?= htmlspecialchars($r['payor_name']) ?></td>
            <td><?= htmlspecialchars($r['address']) ?></td>
            <td>₱<?= number_format($r['grand_total'],2) ?></td>
            <td><?= htmlspecialchars($r['date_issued']) ?></td>
            <td><?= htmlspecialchars($r['treasurer']) ?></td>
            <td>
              <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $r['id'] ?>">✏️</button>
              <a href="?delete=<?= $r['id'] ?>" onclick="return confirm('Delete this record?')" class="btn btn-sm btn-danger">🗑️</a>
              <a href="print_form51.php?id=<?= $r['id'] ?>" target="_blank" class="btn btn-sm btn-primary">🖨️</a>
            </td>
          </tr>
          <?php endforeach; else: ?>
          <tr><td colspan="8" class="text-center text-muted">No Form 51 records found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__.'/form51_modals.php'; ?>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
<?php ob_end_flush(); ?>
