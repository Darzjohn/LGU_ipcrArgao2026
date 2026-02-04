<?php
ob_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';

// ✅ Role restriction
if (!in_array($_SESSION['role'], ['admin', 'treasurer'])) {
  header("Location: ../index.php");
  exit;
}

// ✅ Helper for safe HTML output (prevents null errors)
function e($val) {
  return htmlspecialchars($val ?? '', ENT_QUOTES, 'UTF-8');
}

// ✅ Helper for database queries
if (!function_exists('q')) {
  function q($sql, $params = []) {
    global $mysqli;
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) die("SQL Error: " . $mysqli->error);

    if (!empty($params)) {
      $types = '';
      foreach ($params as $p) $types .= is_numeric($p) ? 'd' : 's';
      $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt;
  }
}

// ✅ Handle Add
if (isset($_POST['add_ngas'])) {
  q(
    "INSERT INTO ngas_settings (ngas_code, nature_of_collection, set_fix_amount, status, created_by)
     VALUES (?, ?, ?, ?, ?)",
    [$_POST['ngas_code'], $_POST['nature_of_collection'], $_POST['set_fix_amount'], $_POST['status'], $_SESSION['name']]
  );
  echo "<script>alert('✅ NGAS Code added successfully!'); location.href='ngas_settings.php';</script>";
  exit;
}

// ✅ Handle Edit
if (isset($_POST['edit_ngas'])) {
  q(
    "UPDATE ngas_settings SET ngas_code=?, nature_of_collection=?, set_fix_amount=?, status=?, updated_by=? WHERE id=?",
    [$_POST['ngas_code'], $_POST['nature_of_collection'], $_POST['set_fix_amount'], $_POST['status'], $_SESSION['name'], $_POST['id']]
  );
  echo "<script>alert('✏️ NGAS Code updated successfully!'); location.href='ngas_settings.php';</script>";
  exit;
}

// ✅ Handle Delete
if (isset($_GET['delete'])) {
  q("DELETE FROM ngas_settings WHERE id=?", [intval($_GET['delete'])]);
  echo "<script>alert('🗑️ NGAS Code deleted successfully!'); location.href='ngas_settings.php';</script>";
  exit;
}

// ✅ Search filter
$where = "WHERE 1";
$params = [];
if (!empty($_GET['search'])) {
  $where .= " AND (ngas_code LIKE ? OR nature_of_collection LIKE ?)";
  $search = "%" . $_GET['search'] . "%";
  array_push($params, $search, $search);
}

$stmt = q("SELECT * FROM ngas_settings $where ORDER BY id DESC", $params);
$records = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="container-fluid mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-gear"></i> NGAS CODE SETTINGS</h4>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">➕ Add NGAS Code</button>
  </div>

  <form class="row mb-3" method="get">
    <div class="col-md-5">
      <input type="text" name="search" class="form-control" placeholder="Search by NGAS Code or Nature"
             value="<?= e($_GET['search'] ?? '') ?>">
    </div>
    <div class="col-md-2">
      <button class="btn btn-primary w-100">Search</button>
    </div>
    <div class="col-md-2">
      <a href="ngas_settings.php" class="btn btn-secondary w-100">Reset</a>
    </div>
  </form>

  <div class="card">
    <div class="card-body table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-dark text-center">
          <tr>
            <th>ID</th>
            <th>NGAS Code</th>
            <th>Nature of Collection</th>
            <th>Set Fix Amount</th>
            <th>Status</th>
            <th>Created By</th>
            <th>Updated By</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($records): foreach ($records as $r): ?>
          <tr class="text-center">
            <td><?= e($r['id']) ?></td>
            <td><strong><?= e($r['ngas_code']) ?></strong></td>
            <td><?= e($r['nature_of_collection']) ?></td>
            <td>₱<?= number_format($r['set_fix_amount'] ?? 0, 2) ?></td>
            <td>
              <span class="badge bg-<?= ($r['status'] ?? 'disable') === 'enable' ? 'success' : 'secondary' ?>">
                <?= ucfirst(e($r['status'] ?? 'disable')) ?>
              </span>
            </td>
            <td><?= e($r['created_by']) ?></td>
            <td><?= e($r['updated_by']) ?></td>
            <td>
              <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= e($r['id']) ?>">✏️</button>
              <a href="?delete=<?= e($r['id']) ?>" onclick="return confirm('Delete this record?')" class="btn btn-sm btn-danger">🗑️</a>
            </td>
          </tr>

          <!-- Edit Modal -->
          <div class="modal fade" id="editModal<?= e($r['id']) ?>" tabindex="-1">
            <div class="modal-dialog">
              <div class="modal-content">
                <form method="post">
                  <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title">✏️ Edit NGAS Code</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <input type="hidden" name="id" value="<?= e($r['id']) ?>">
                    <div class="mb-3">
                      <label>NGAS Code</label>
                      <input type="text" name="ngas_code" class="form-control" value="<?= e($r['ngas_code']) ?>" required>
                    </div>
                    <div class="mb-3">
                      <label>Nature of Collection</label>
                      <input type="text" name="nature_of_collection" class="form-control" value="<?= e($r['nature_of_collection']) ?>" required>
                    </div>
                    <div class="mb-3">
                      <label>Set Fix Amount</label>
                      <input type="number" step="0.01" name="set_fix_amount" class="form-control" value="<?= e($r['set_fix_amount']) ?>">
                    </div>
                    <div class="mb-3">
                      <label>Status</label>
                      <select name="status" class="form-select">
                        <option value="enable" <?= ($r['status'] ?? '') === 'enable' ? 'selected' : '' ?>>Enable</option>
                        <option value="disable" <?= ($r['status'] ?? '') === 'disable' ? 'selected' : '' ?>>Disable</option>
                      </select>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="submit" name="edit_ngas" class="btn btn-primary">Save Changes</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <?php endforeach; else: ?>
          <tr><td colspan="8" class="text-center text-muted">No NGAS records found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ✅ Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title">➕ Add NGAS Code</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label>NGAS Code</label>
            <input type="text" name="ngas_code" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Nature of Collection</label>
            <input type="text" name="nature_of_collection" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Set Fix Amount</label>
            <input type="number" step="0.01" name="set_fix_amount" class="form-control" value="0.00">
          </div>
          <div class="mb-3">
            <label>Status</label>
            <select name="status" class="form-select">
              <option value="enable">Enable</option>
              <option value="disable">Disable</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="add_ngas" class="btn btn-success">Add</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
<?php ob_end_flush(); ?>
