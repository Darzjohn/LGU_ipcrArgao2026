<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
$messages = [];

// Only Assessor can access
if(!in_array($_SESSION['role'], ['admin','assessor','assessment_clerk','cashier'])) {
    header("Location: ../index.php");
    exit;
}

// --- Add Property ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_property'])) {
    $td_no = trim($_POST['td_no'] ?? '');
    $lot_no = trim($_POST['lot_no'] ?? '');
    $owner_id = (int)($_POST['owner_id'] ?? 0);
    $barangay = trim($_POST['barangay'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $classification = trim($_POST['classification'] ?? '');
    $assessed_value = (float)($_POST['assessed_value'] ?? 0);
    $effective_year = (int)($_POST['effective_year'] ?? 0);

    if ($td_no && $lot_no && $owner_id > 0) {
        $stmt = $mysqli->prepare("INSERT INTO properties 
            (td_no, lot_no, owner_id, barangay, location, classification, assessed_value, effective_year) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssisssid", $td_no, $lot_no, $owner_id, $barangay, $location, $classification, $assessed_value, $effective_year);
        $stmt->execute();
        $messages[] = "✅ Property added successfully.";
    } else {
        $messages[] = "⚠️ TD No, Lot No, and Owner are required.";
    }
}

// --- Update Property ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_property'])) {
    $id = (int)($_POST['id'] ?? 0);
    $td_no = trim($_POST['td_no'] ?? '');
    $lot_no = trim($_POST['lot_no'] ?? '');
    $owner_id = (int)($_POST['owner_id'] ?? 0);
    $barangay = trim($_POST['barangay'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $classification = trim($_POST['classification'] ?? '');
    $assessed_value = (float)($_POST['assessed_value'] ?? 0);
    $effective_year = (int)($_POST['effective_year'] ?? 0);

    if ($id && $td_no && $lot_no && $owner_id > 0) {
        $stmt = $mysqli->prepare("UPDATE properties 
            SET td_no=?, lot_no=?, owner_id=?, barangay=?, location=?, classification=?, assessed_value=?, effective_year=? 
            WHERE id=?");
        $stmt->bind_param("ssisssidi", $td_no, $lot_no, $owner_id, $barangay, $location, $classification, $assessed_value, $effective_year, $id);
        $stmt->execute();
        $messages[] = "✅ Property updated successfully.";
    } else {
        $messages[] = "⚠️ TD No, Lot No, and Owner are required.";
    }
}

// --- Delete Property ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_property'])) {
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
        $stmt = $mysqli->prepare("DELETE FROM properties WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $messages[] = "🗑️ Property deleted successfully.";
    }
}

// --- Fetch Owners ---
$owners = $mysqli->query("SELECT id, name FROM owners ORDER BY name ASC");

// --- Filter & Search ---
$where = "WHERE 1=1";
$params = [];
$types = "";

$search = trim($_GET['search'] ?? '');
if ($search) {
    $where .= " AND (p.td_no LIKE ? OR p.lot_no LIKE ? OR o.name LIKE ? OR p.barangay LIKE ? OR p.location LIKE ? OR p.classification LIKE ?)";
    $like = "%$search%";
    $params = array_fill(0, 6, $like);
    $types = "ssssss";
}

$filter_classification = trim($_GET['classification'] ?? '');
if ($filter_classification) {
    $where .= " AND p.classification = ?";
    $params[] = $filter_classification;
    $types .= "s";
}

$filter_barangay = trim($_GET['barangay'] ?? '');
if ($filter_barangay) {
    $where .= " AND p.barangay LIKE ?";
    $params[] = "%$filter_barangay%";
    $types .= "s";
}

// --- Pagination ---
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

$countSql = "SELECT COUNT(*) AS cnt FROM properties p LEFT JOIN owners o ON o.id=p.owner_id $where";
$countStmt = $mysqli->prepare($countSql);
if ($types) $countStmt->bind_param($types, ...$params);
$countStmt->execute();
$totalRows = $countStmt->get_result()->fetch_assoc()['cnt'];
$totalPages = ceil($totalRows / $limit);

$sql = "SELECT p.*, o.name AS owner_name 
        FROM properties p 
        LEFT JOIN owners o ON o.id=p.owner_id 
        $where 
        ORDER BY p.id DESC 
        LIMIT $limit OFFSET $offset";
$stmt = $mysqli->prepare($sql);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();
?>

<h2 class="mb-4">🏠 Properties</h2>

<?php foreach ($messages as $msg): ?>
  <div class="alert alert-info"><?= $msg ?></div>
<?php endforeach; ?>

<!-- Search & Filter -->
<div class="card mb-3">
  <div class="card-body">
    <form method="get" class="row g-2 align-items-center">
      <div class="col-md-3"><input type="text" name="search" value="<?=htmlspecialchars($search)?>" class="form-control" placeholder="🔍 Search (TD, Lot, Owner, Location)"></div>
      <div class="col-md-3"><input type="text" name="classification" value="<?=htmlspecialchars($filter_classification)?>" class="form-control" placeholder="Classification"></div>
      <div class="col-md-3"><input type="text" name="barangay" value="<?=htmlspecialchars($filter_barangay)?>" class="form-control" placeholder="Barangay"></div>
      <div class="col-md-3 text-end">
        <button class="btn btn-primary">Search</button>
        <a href="properties.php" class="btn btn-secondary">Clear</a>
      </div>
    </form>
  </div>
</div>

<div class="text-end mb-3">
  <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">➕ Add Property</button>
</div>

<!-- Properties Table -->
<div class="card">
  <div class="card-header bg-secondary text-white">Property List</div>
  <div class="card-body table-responsive">
    <table class="table table-bordered table-hover align-middle">
      <thead class="table-dark">
        <tr>
          <th>ID</th><th>TD No</th><th>Lot No</th><th>Owner</th><th>Barangay</th><th>Location</th>
          <th>Classification</th><th>Assessed Value</th><th>Effective Year</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = $res->fetch_assoc()): ?>
        <tr>
          <td><?=$row['id']?></td>
          <td><?=htmlspecialchars($row['td_no'])?></td>
          <td><?=htmlspecialchars($row['lot_no'])?></td>
          <td><?=htmlspecialchars($row['owner_name'])?></td>
          <td><?=htmlspecialchars($row['barangay'])?></td>
          <td><?=htmlspecialchars($row['location'])?></td>
          <td><?=htmlspecialchars($row['classification'])?></td>
          <td>₱<?=number_format($row['assessed_value'],2)?></td>
          <td><?=$row['effective_year']?></td>
          <td class="text-nowrap">
            <button class="btn btn-warning btn-sm edit-btn"
              data-id="<?=$row['id']?>"
              data-td="<?=$row['td_no']?>"
              data-lot="<?=$row['lot_no']?>"
              data-owner="<?=$row['owner_id']?>"
              data-barangay="<?=$row['barangay']?>"
              data-location="<?=$row['location']?>"
              data-classification="<?=$row['classification']?>"
              data-value="<?=$row['assessed_value']?>"
              data-year="<?=$row['effective_year']?>">✏️</button>
            <button class="btn btn-info btn-sm history-btn" data-id="<?=$row['id']?>">📜</button>
            <a href="assessments.php?property_id=<?=$row['id']?>" class="btn btn-success btn-sm">📑</a>
            <form method="post" class="d-inline" onsubmit="return confirm('Delete this property?')">
              <input type="hidden" name="id" value="<?=$row['id']?>">
              <button class="btn btn-danger btn-sm" name="delete_property">🗑️</button>
            </form>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form method="post" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Property</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <div class="col-md-4"><label>TD No</label><input name="td_no" class="form-control" required></div>
        <div class="col-md-4"><label>Lot No</label><input name="lot_no" class="form-control" required></div>
        <div class="col-md-4">
          <label>Owner</label>
          <select name="owner_id" class="form-select" required>
            <option value="">-- Select Owner --</option>
            <?php $owners->data_seek(0); while($o=$owners->fetch_assoc()): ?>
              <option value="<?=$o['id']?>"><?=$o['name']?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-md-4"><label>Barangay</label><input name="barangay" class="form-control"></div>
        <div class="col-md-4"><label>Location</label><input name="location" class="form-control"></div>
        <div class="col-md-4"><label>Classification</label><input name="classification" class="form-control"></div>
        <div class="col-md-4"><label>Assessed Value</label><input type="number" step="0.01" name="assessed_value" class="form-control"></div>
        <div class="col-md-4"><label>Effective Year</label><input type="number" name="effective_year" class="form-control"></div>
      </div>
      <div class="modal-footer"><button class="btn btn-success" name="add_property">Save</button></div>
    </form>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form method="post" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Property</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <input type="hidden" name="id" id="edit-id">
        <div class="col-md-4"><label>TD No</label><input id="edit-td" name="td_no" class="form-control" required></div>
        <div class="col-md-4"><label>Lot No</label><input id="edit-lot" name="lot_no" class="form-control" required></div>
        <div class="col-md-4">
          <label>Owner</label>
          <select id="edit-owner" name="owner_id" class="form-select" required>
            <option value="">-- Select Owner --</option>
            <?php $owners->data_seek(0); while($o=$owners->fetch_assoc()): ?>
              <option value="<?=$o['id']?>"><?=$o['name']?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-md-6"><label>Barangay</label><input id="edit-barangay" name="barangay" class="form-control"></div>
        <div class="col-md-6"><label>Location</label><input id="edit-location" name="location" class="form-control"></div>
        <div class="col-md-6"><label>Classification</label><input id="edit-classification" name="classification" class="form-control"></div>
        <div class="col-md-6"><label>Assessed Value</label><input type="number" step="0.01" id="edit-value" name="assessed_value" class="form-control"></div>
        <div class="col-md-6"><label>Effective Year</label><input id="edit-year" name="effective_year" class="form-control"></div>
      </div>
      <div class="modal-footer"><button class="btn btn-primary" name="update_property">Save Changes</button></div>
    </form>
  </div>
</div>

<!-- History Modal -->
<div class="modal fade" id="historyModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Assessment History</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered" id="historyTable">
          <thead><tr><th>Tax Year</th><th>Assessed Value</th><th>Barangay</th><th>Location</th></tr></thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
  const editModal = new bootstrap.Modal(document.getElementById("editModal"));
  const historyModal = new bootstrap.Modal(document.getElementById("historyModal"));

  document.querySelectorAll(".edit-btn").forEach(btn => {
    btn.addEventListener("click", e => {
      e.preventDefault();
      document.getElementById("edit-id").value = btn.dataset.id;
      document.getElementById("edit-td").value = btn.dataset.td;
      document.getElementById("edit-lot").value = btn.dataset.lot;
      document.getElementById("edit-owner").value = btn.dataset.owner;
      document.getElementById("edit-barangay").value = btn.dataset.barangay;
      document.getElementById("edit-location").value = btn.dataset.location;
      document.getElementById("edit-classification").value = btn.dataset.classification;
      document.getElementById("edit-value").value = btn.dataset.value;
      document.getElementById("edit-year").value = btn.dataset.year;
      editModal.show();
    });
  });

  document.querySelectorAll(".history-btn").forEach(btn => {
    btn.addEventListener("click", e => {
      e.preventDefault();
      const id = btn.dataset.id;
      const tbody = document.querySelector("#historyTable tbody");
      tbody.innerHTML = "<tr><td colspan='4' class='text-center'>⏳ Loading...</td></tr>";
      fetch("property_history.php?property_id=" + id)
        .then(r => r.text())
        .then(html => { tbody.innerHTML = html; historyModal.show(); })
        .catch(() => { tbody.innerHTML = "<tr><td colspan='4' class='text-center text-danger'>⚠️ Failed to load history.</td></tr>"; historyModal.show(); });
    });
  });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
