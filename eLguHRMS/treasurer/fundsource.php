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
        if (!$stmt) {
            die("SQL Prepare Error: " . $mysqli->error . "<br>Query: " . htmlspecialchars($sql));
        }

        if (!empty($params)) {
            $types = '';
            foreach ($params as $p) {
                if (is_int($p)) $types .= 'i';
                elseif (is_float($p) || is_double($p)) $types .= 'd';
                else $types .= 's';
            }
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt->execute()) {
            die("SQL Exec Error: " . $stmt->error);
        }
        return $stmt;
    }
}

// Add fund source
if (isset($_POST['add_fund_source'])) {
    $name = $_POST['name'] ?? '';
    $code = $_POST['code'] ?? '';
    $status = intval($_POST['status']);

    q(
        "INSERT INTO fund_source (name, code, status, created_by, created_at)
         VALUES (?, ?, ?, ?, NOW())",
        [$name, $code, $status, $_SESSION['name']]
    );

    echo "<script>alert('Fund Source added successfully'); location.href='fund_source.php';</script>";
    exit;
}

// Edit fund source
if (isset($_POST['edit_fund_source'])) {
    $id = intval($_POST['id']);
    $name = $_POST['name'] ?? '';
    $code = $_POST['code'] ?? '';
    $status = intval($_POST['status']);

    q(
        "UPDATE fund_source SET name=?, code=?, status=?, created_by=?, created_at=created_at WHERE id=?",
        [$name, $code, $status, $_SESSION['name'], $id]
    );

    echo "<script>alert('Fund Source updated successfully'); location.href='fund_source.php';</script>";
    exit;
}

// Delete fund source
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    q("DELETE FROM fund_source WHERE id=?", [$del_id]);
    echo "<script>alert('Fund Source deleted'); location.href='fund_source.php';</script>";
    exit;
}

// Fetch records
$res = $mysqli->query("SELECT * FROM fund_source ORDER BY id DESC");
$list = [];
if ($res) {
    while ($row = $res->fetch_assoc()) $list[] = $row;
}
?>
<div class="container-fluid mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-cash-coin"></i> Fund Sources</h4>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">➕ Add Fund Source</button>
  </div>

  <div class="card">
    <div class="card-body table-responsive">
      <table class="table table-bordered table-hover">
        <thead class="table-dark text-center">
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Code</th>
            <th>Status</th>
            <th>Created By</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($list)): foreach ($list as $fs): ?>
          <tr class="text-center">
            <td><?= $fs['id'] ?></td>
            <td><?= htmlspecialchars($fs['name']) ?></td>
            <td><?= htmlspecialchars($fs['code']) ?></td>
            <td><?= $fs['status'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?></td>
            <td><?= htmlspecialchars($fs['created_by'] ?? '') ?></td>
            <td>
              <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $fs['id'] ?>">✏️</button>
              <a href="?delete=<?= $fs['id'] ?>" onclick="return confirm('Delete this fund source?')" class="btn btn-sm btn-danger">🗑️</a>
            </td>
          </tr>
          <?php endforeach; else: ?>
          <tr>
            <td colspan="6" class="text-center text-muted">No fund sources found.</td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">➕ Add Fund Source</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <label>Name</label>
        <input type="text" name="name" class="form-control mb-2" required>
        <label>Code</label>
        <input type="text" name="code" class="form-control mb-2" required>
        <label>Status</label>
        <select name="status" class="form-control mb-2">
          <option value="1">Active</option>
          <option value="0">Inactive</option>
        </select>
      </div>
      <div class="modal-footer">
        <button type="submit" name="add_fund_source" class="btn btn-success">Save</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Modals -->
<?php foreach ($list as $fs): ?>
<div class="modal fade" id="editModal<?= $fs['id'] ?>" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title">✏️ Edit Fund Source</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" value="<?= $fs['id'] ?>">
        <label>Name</label>
        <input type="text" name="name" class="form-control mb-2" value="<?= htmlspecialchars($fs['name']) ?>" required>
        <label>Code</label>
        <input type="text" name="code" class="form-control mb-2" value="<?= htmlspecialchars($fs['code']) ?>" required>
        <label>Status</label>
        <select name="status" class="form-control mb-2">
          <option value="1"<?= $fs['status'] ? ' selected' : '' ?>>Active</option>
          <option value="0"<?= !$fs['status'] ? ' selected' : '' ?>>Inactive</option>
        </select>
      </div>
      <div class="modal-footer">
        <button type="submit" name="edit_fund_source" class="btn btn-warning">Update</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </form>
  </div>
</div>
<?php endforeach; ?>

<?php
require_once __DIR__ . '/../layouts/footer.php';
ob_end_flush();
?>




<!-- IMPROVED UI ENHANCEMENTS -->
<style>
  .table thead {
    background: #0d6efd;
    color: white;
  }
  .table tbody tr:hover {
    background: #f3f6ff;
  }
</style>
