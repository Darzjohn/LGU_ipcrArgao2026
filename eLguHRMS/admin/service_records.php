<?php
ob_start();
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';

// Only admin can manage service records
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

/* ================================
   ✅ POST HANDLING: ADD / EDIT
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    function post($key){ return trim($_POST[$key] ?? ''); }

    $action = post('action');
    $id = post('record_id') ?: null;

    $emp_idno = post('emp_idno');
    $recfrom = post('recfrom') ?: null;
    $recto = post('recto') ?: null;
    $position_id = post('position_id') ?: null;
    $status_id = post('status_id') ?: null;
    $assignment_id = post('assignment_id') ?: null;
    $lawop = post('lawop') ?: null;
    $separation_cause = post('separation_cause') ?: null;
    $separation_date = post('separation_date') ?: null;
    $remarks = post('remarks') ?: null;

    // --- New fields ---
    $salary_input = post('salary');
    $salary_clean = str_replace(',', '', $salary_input);
    $salary = is_numeric($salary_clean) ? (float)$salary_clean : 0.00;

    $salary_grade = post('salary_grade') ?: null;

    $step_increment = post('step_increment'); // store as-is, e.g. "Step 1"

    // --- ADD RECORD ---
    if ($action === 'add') {
        $stmt = $mysqli->prepare("
            INSERT INTO service_records (
                emp_idno, recfrom, recto, position, status, assignment, lawop, separation_cause, separation_date, remarks,
                salary, salary_grade, step_increment
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)
        ");
        // types: emp_idno(s), recfrom(s), recto(s), position(i), status(i), assignment(i),
        // lawop(s), separation_cause(s), separation_date(s), remarks(s),
        // salary(d), salary_grade(s), step_increment(s)
        $stmt->bind_param(
            "sssiiissssdss",
            $emp_idno, $recfrom, $recto, $position_id, $status_id, $assignment_id,
            $lawop, $separation_cause, $separation_date, $remarks,
            $salary, $salary_grade, $step_increment
        );
        $stmt->execute();
        $stmt->close();
    }

    // --- EDIT RECORD ---
    if ($action === 'edit' && $id) {
        $stmt = $mysqli->prepare("
            UPDATE service_records SET
                emp_idno=?, recfrom=?, recto=?, position=?, status=?, assignment=?, lawop=?, separation_cause=?, separation_date=?, remarks=?,
                salary=?, salary_grade=?, step_increment=?
            WHERE id=?
        ");
        // same types + id (i)
        $stmt->bind_param(
            "sssiiissssdssi",
            $emp_idno, $recfrom, $recto, $position_id, $status_id, $assignment_id,
            $lawop, $separation_cause, $separation_date, $remarks,
            $salary, $salary_grade, $step_increment,
            $id
        );
        $stmt->execute();
        $stmt->close();
    }

    header("Location: service_records.php");
    exit;
}


/* ================================
   ✅ DELETE RECORD
================================ */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $mysqli->prepare("DELETE FROM service_records WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

/* ================================
   ✅ SEARCH + FILTER + PAGINATION
================================ */
/* Filters expected via GET:
   - search (text)
   - department (assignment id)
   - position (position id)
   - status (status id)
   - from (recfrom >=)
   - to (recto <=)
   - page
*/

$search = trim($_GET['search'] ?? '');
$department_filter = $_GET['department'] ?? '';
$position_filter   = $_GET['position'] ?? '';
$status_filter     = $_GET['status'] ?? '';
$date_from_filter  = $_GET['from'] ?? '';
$date_to_filter    = $_GET['to'] ?? '';

$page  = max(1, (int)($_GET['page'] ?? 1));
$limit = 30;
$offset = ($page - 1) * $limit;

$where = "WHERE 1=1";
$params = [];
$types = "";

/* TEXT SEARCH: match emp_idno (sr), employee names (e), lawop, remarks */
if ($search !== "") {
    $where .= " AND (
        sr.emp_idno LIKE ? OR 
        e.first_name LIKE ? OR
        e.middle_name LIKE ? OR
        e.surname LIKE ? OR
        sr.lawop LIKE ? OR
        sr.remarks LIKE ?
    )";
    $like = "%$search%";
    array_push($params, $like, $like, $like, $like, $like, $like);
    $types .= "ssssss";
}

/* FILTERS */
if ($department_filter !== "") {
    $where .= " AND sr.assignment = ?";
    array_push($params, $department_filter);
    $types .= "i";
}

if ($position_filter !== "") {
    $where .= " AND sr.position = ?";
    array_push($params, $position_filter);
    $types .= "i";
}

if ($status_filter !== "") {
    $where .= " AND sr.status = ?";
    array_push($params, $status_filter);
    $types .= "i";
}

if ($date_from_filter !== "") {
    $where .= " AND sr.recfrom >= ?";
    array_push($params, $date_from_filter);
    $types .= "s";
}

if ($date_to_filter !== "") {
    $where .= " AND sr.recto <= ?";
    array_push($params, $date_to_filter);
    $types .= "s";
}

/* COUNT total matching records */
$sql_count = "SELECT COUNT(*) AS total
              FROM service_records sr
              LEFT JOIN employees e ON sr.emp_idno = e.emp_idno
              $where";

$stmt = $mysqli->prepare($sql_count);
if ($stmt === false) {
    die("Prepare failed: " . $mysqli->error);
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$total_records = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$total_pages = ($total_records > 0) ? ceil($total_records / $limit) : 1;

/* FETCH actual records with joins */
$sql = "SELECT sr.*, e.first_name, e.middle_name, e.surname, e.name_extension,
               d.name AS department_name, p.name AS position_name, es.name AS status_name
        FROM service_records sr
        LEFT JOIN employees e ON sr.emp_idno = e.emp_idno
        LEFT JOIN departments d ON sr.assignment = d.id
        LEFT JOIN positions p ON sr.position = p.id
        LEFT JOIN employment_status es ON sr.status = es.id
        $where
        ORDER BY sr.id DESC
        LIMIT ? OFFSET ?";

$stmt = $mysqli->prepare($sql);
if ($stmt === false) {
    die("Prepare failed: " . $mysqli->error);
}

if (!empty($params)) {
    // append limit and offset ints
    $bind_types = $types . "ii";
    $bind_values = array_merge($params, [$limit, $offset]);
    $stmt->bind_param($bind_types, ...$bind_values);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}

$stmt->execute();
$records = $stmt->get_result();
$stmt->close();

?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-file-text"></i> Service Records</h4>
        <div class="d-flex gap-2">
            <form class="d-flex" method="get">
                <input class="form-control me-2" type="search" name="search" placeholder="Search emp id / name / law/op / remarks" value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-outline-primary" type="submit"><i class="bi bi-search"></i></button>
            </form>

            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#serviceRecordModal" onclick="openAddModal()">
                <i class="bi bi-plus-circle"></i> Add Record
            </button>

            <!-- Print Button (initially disabled) -->
            <button class="btn btn-primary" id="printSelected" disabled>
                <i class="bi bi-printer"></i> Print Selected
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th><input type="checkbox" id="checkAll"></th> <!-- Check All -->
                    <th>ID</th>
                    <th>Employee</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Position</th>
                    <th>Status</th>
                    <th>Assignment</th>
                    <th>Salary</th>
                    <th>Salary Grade</th>
                    <th>Step Increment</th>
                    <th>LAW/OP</th>
                    <th>Separation Cause</th>
                    <th>Separation Date</th>
                    <th>Remarks</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($records->num_rows > 0): ?>
                    <?php while ($r = $records->fetch_assoc()): ?>
                        <?php $fullName = trim($r['first_name'] . ' ' . $r['middle_name'] . ' ' . $r['surname'] . ' ' . $r['name_extension']); ?>
                        <tr>
                            <td><input type="checkbox" class="recordCheckbox" value="<?= $r['id'] ?>"></td>
                            <td><?= $r['id'] ?></td>
                            <td><?= htmlspecialchars($fullName) ?></td>
                            <td><?= htmlspecialchars($r['recfrom']) ?></td>
                            <td><?= htmlspecialchars($r['recto']) ?></td>
                            <td><?= htmlspecialchars($r['position_name']) ?></td>
                            <td><?= htmlspecialchars($r['status_name']) ?></td>
                            <td><?= htmlspecialchars($r['department_name']) ?></td>
                            <td><?= htmlspecialchars(number_format((float)$r['salary'],2,'.',',')) ?></td>
                            <td><?= htmlspecialchars($r['salary_grade']) ?></td>
                            <td><?= htmlspecialchars($r['step_increment']) ?></td>
                            <td><?= htmlspecialchars($r['lawop']) ?></td>
                            <td><?= htmlspecialchars($r['separation_cause']) ?></td>
                            <td><?= htmlspecialchars($r['separation_date']) ?></td>
                            <td><?= htmlspecialchars($r['remarks']) ?></td>
  <td class="text-center">
    <div class="d-inline-flex gap-1 flex-wrap justify-content-center">
        <button
            type="button"
            class="btn btn-sm btn-outline-primary"
            title="Edit Service Record"
            onclick='openEditModal(<?= htmlspecialchars(json_encode($r, JSON_HEX_TAG|JSON_HEX_QUOT|JSON_HEX_APOS|JSON_HEX_AMP), ENT_QUOTES, "UTF-8") ?>)'>
            <i class="bi bi-pencil-square"></i>
        </button>

        <a href="?delete=<?= $r['id'] ?>" class="btn btn-sm btn-danger"
           onclick="return confirm('Delete this record?')">
           <i class="bi bi-trash"></i>
        </a>
    </div>
</td>






                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="16" class="text-center text-muted">No records found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <nav>
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                    <?php
                        // preserve current filters/search in pagination links
                        $qs = $_GET;
                        $qs['page'] = $i;
                        $query = http_build_query($qs);
                    ?>
                    <a class="page-link" href="?<?= $query ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>

<?php include 'service_record_modal.php'; ?>
<?php require_once __DIR__ . '/../layouts/footer.php'; ob_end_flush(); ?>

<script>
// Check All functionality & enabling Print button
const checkAll = document.getElementById('checkAll');
const printBtn = document.getElementById('printSelected');

function updatePrintButtonState() {
    const anyChecked = document.querySelectorAll('.recordCheckbox:checked').length > 0;
    printBtn.disabled = !anyChecked;
}

if (checkAll) {
    checkAll.addEventListener('change', function(){
        let checkboxes = document.querySelectorAll('.recordCheckbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
        updatePrintButtonState();
    });
}

// individual checkboxes
document.addEventListener('change', function(e){
    if (e.target && e.target.classList && e.target.classList.contains('recordCheckbox')) {
        // if any is unchecked, uncheck checkAll
        if (!e.target.checked) checkAll.checked = false;
        // if all are checked, set checkAll
        const all = document.querySelectorAll('.recordCheckbox');
        const checked = document.querySelectorAll('.recordCheckbox:checked');
        if (all.length > 0 && all.length === checked.length) checkAll.checked = true;
        updatePrintButtonState();
    }
});

// Print Selected handler
if (printBtn) {
    printBtn.addEventListener('click', function(){
        const checked = Array.from(document.querySelectorAll('.recordCheckbox:checked')).map(cb => cb.value);
        if (checked.length === 0) return;
        // open the TCPDF print view in a new window/tab
        const url = 'print_servicerecord.php?ids=' + encodeURIComponent(checked.join(','));
        window.open(url, '_blank');
    });
}
</script>
