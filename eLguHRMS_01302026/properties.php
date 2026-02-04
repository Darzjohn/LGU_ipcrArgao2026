<?php
require 'db.php';
$messages = [];

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
        q("INSERT INTO properties (td_no, lot_no, owner_id, barangay, location, classification, assessed_value, effective_year) 
            VALUES (?,?,?,?,?,?,?,?)",
          "ssisssid", [$td_no, $lot_no, $owner_id, $barangay, $location, $classification, $assessed_value, $effective_year]);
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
        q("UPDATE properties 
              SET td_no=?, lot_no=?, owner_id=?, barangay=?, location=?, classification=?, assessed_value=?, effective_year=? 
            WHERE id=?",
          "ssisssidi", [$td_no, $lot_no, $owner_id, $barangay, $location, $classification, $assessed_value, $effective_year, $id]);
        $messages[] = "✅ Property updated successfully.";
    } else {
        $messages[] = "⚠️ TD No, Lot No, and Owner are required.";
    }
}

// --- Delete Property ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_property'])) {
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
        q("DELETE FROM properties WHERE id=?", "i", [$id]);
        $messages[] = "🗑️ Property deleted successfully.";
    }
}

// --- Fetch Owners for Dropdown ---
$owners = $mysqli->query("SELECT id, name FROM owners ORDER BY name ASC");

// --- Filtering ---
$where = "WHERE 1=1";
$params = [];
$types  = "";

$search = trim($_GET['search'] ?? '');
if ($search) {
    $where .= " AND (p.td_no LIKE ? OR p.lot_no LIKE ? OR o.name LIKE ? OR p.barangay LIKE ? OR p.location LIKE ? OR p.classification LIKE ?)";
    $like = "%$search%";
    $params = array_merge($params, [$like,$like,$like,$like,$like,$like]);
    $types .= "ssssss";
}

$filter_classification = trim($_GET['classification'] ?? '');
if ($filter_classification) {
    $where .= " AND p.classification = ?";
    $params[] = $filter_classification;
    $types .= "s";
}

// --- Pagination ---
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

$countSql = "SELECT COUNT(*) AS cnt 
             FROM properties p 
             LEFT JOIN owners o ON o.id=p.owner_id $where";
$countStmt = $mysqli->prepare($countSql);
if ($types) $countStmt->bind_param($types, ...$params);
$countStmt->execute();
$totalRows = $countStmt->get_result()->fetch_assoc()['cnt'];
$totalPages = ceil($totalRows / $limit);

$sql = "SELECT p.*, o.name AS owner_name 
        FROM properties p 
        LEFT JOIN owners o ON o.id=p.owner_id 
        $where 
        ORDER BY p.id DESC LIMIT $limit OFFSET $offset";
$stmt = $mysqli->prepare($sql);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

include 'header.php';
?>

<h2 class="mb-4">🏠 Properties</h2>

<?php foreach ($messages as $msg): ?>
  <div class="alert alert-info"><?= $msg ?></div>
<?php endforeach; ?>

<!-- Search + Filter -->
<div class="card mb-3">
  <div class="card-body">
    <form method="get" class="row g-2 align-items-center">
      <div class="col-md-4">
        <input type="text" name="search" value="<?=htmlspecialchars($search)?>" class="form-control" placeholder="🔍 Search...">
      </div>
      <div class="col-md-3">
        <input type="text" name="classification" value="<?=htmlspecialchars($filter_classification)?>" class="form-control" placeholder="Classification">
      </div>
      <div class="col-md-5 text-end">
        <button class="btn btn-primary">Search</button>
        <a href="properties.php?page=1" class="btn btn-secondary">Clear</a>
      </div>
    </form>
  </div>
</div>

<!-- Add Property -->
<div class="card mb-4">
  <div class="card-header bg-primary text-white">Add New Property</div>
  <div class="card-body">
    <form method="post" class="row g-3">
      <div class="col-md-3">
        <label class="form-label">TD No</label>
        <input type="text" name="td_no" class="form-control" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Lot No</label>
        <input type="text" name="lot_no" class="form-control" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Owner</label>
        <select class="form-select" name="owner_id" required>
          <option value="">-- Select Owner --</option>
          <?php while($o = $owners->fetch_assoc()): ?>
            <option value="<?=$o['id']?>"><?=$o['name']?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Barangay</label>
        <input type="text" name="barangay" class="form-control">
      </div>
      <div class="col-md-4">
        <label class="form-label">Location</label>
        <input type="text" name="location" class="form-control">
      </div>
      <div class="col-md-4">
        <label class="form-label">Classification</label>
        <input type="text" name="classification" class="form-control" placeholder="e.g. Residential">
      </div>
      <div class="col-md-2">
        <label class="form-label">Assessed Value</label>
        <input type="number" step="0.01" name="assessed_value" class="form-control">
      </div>
      <div class="col-md-2">
        <label class="form-label">Effective Year</label>
        <input type="number" name="effective_year" class="form-control">
      </div>
      <div class="col-md-12 text-end">
        <button class="btn btn-success" name="add_property">Save</button>
      </div>
    </form>
  </div>
</div>

<!-- Properties Table -->
<div class="card">
  <div class="card-header bg-secondary text-white">Property List</div>
  <div class="card-body table-responsive">
    <p class="text-muted">Showing <?=$res->num_rows?> of <?=$totalRows?> results.</p>
    <table class="table table-bordered table-hover align-middle">
      <thead class="table-dark">
        <tr>
          <th>ID</th><th>TD No</th><th>Lot No</th><th>Owner</th>
          <th>Barangay</th><th>Location</th><th>Classification</th>
          <th>Assessed Value</th><th>Effective Year</th><th>Actions</th>
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
            <button type="button" class="btn btn-warning btn-sm"
              data-bs-toggle="modal" data-bs-target="#editModal"
              data-id="<?=$row['id']?>"
              data-td="<?=$row['td_no']?>"
              data-lot="<?=$row['lot_no']?>"
              data-owner="<?=$row['owner_id']?>"
              data-barangay="<?=$row['barangay']?>"
              data-location="<?=$row['location']?>"
              data-classification="<?=$row['classification']?>"
              data-value="<?=$row['assessed_value']?>"
              data-year="<?=$row['effective_year']?>">
              ✏️
            </button>
            <button type="button" class="btn btn-info btn-sm"
              data-bs-toggle="modal" data-bs-target="#historyModal"
              data-id="<?=$row['id']?>" data-td="<?=$row['td_no']?>">
              📜
            </button>
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

    <!-- Pagination -->
    <nav>
      <ul class="pagination">
        <?php for($p=1;$p<=$totalPages;$p++): ?>
          <li class="page-item <?=($p==$page)?'active':''?>">
            <a class="page-link" href="?page=<?=$p?>&search=<?=urlencode($search)?>&classification=<?=urlencode($filter_classification)?>"><?=$p?></a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>
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
        <div class="col-md-4">
          <label class="form-label">TD No</label>
          <input type="text" name="td_no" id="edit-td" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Lot No</label>
          <input type="text" name="lot_no" id="edit-lot" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Owner</label>
          <select name="owner_id" id="edit-owner" class="form-select" required>
            <option value="">-- Select Owner --</option>
            <?php
            $ownerOpts = $mysqli->query("SELECT id,name FROM owners ORDER BY name ASC");
            while($o=$ownerOpts->fetch_assoc()): ?>
              <option value="<?=$o['id']?>"><?=$o['name']?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Barangay</label>
          <input type="text" name="barangay" id="edit-barangay" class="form-control">
        </div>
        <div class="col-md-6">
          <label class="form-label">Location</label>
          <input type="text" name="location" id="edit-location" class="form-control">
        </div>
        <div class="col-md-6">
          <label class="form-label">Classification</label>
          <input type="text" name="classification" id="edit-classification" class="form-control">
        </div>
        <div class="col-md-6">
          <label class="form-label">Assessed Value</label>
          <input type="number" step="0.01" name="assessed_value" id="edit-value" class="form-control">
        </div>
        <div class="col-md-6">
          <label class="form-label">Effective Year</label>
          <input type="number" name="effective_year" id="edit-year" class="form-control">
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary" name="update_property">Save changes</button>
      </div>
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
          <thead class="table-light">
            <tr>
              <th>Tax Year</th>
              <th>Assessed Value</th>
              <th>Barangay</th>
              <th>Location</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
// Edit modal
var editModal = document.getElementById('editModal');
editModal.addEventListener('show.bs.modal', function (event) {
  var button = event.relatedTarget;
  document.getElementById('edit-id').value = button.getAttribute('data-id');
  document.getElementById('edit-td').value = button.getAttribute('data-td');
  document.getElementById('edit-lot').value = button.getAttribute('data-lot');
  document.getElementById('edit-owner').value = button.getAttribute('data-owner');
  document.getElementById('edit-barangay').value = button.getAttribute('data-barangay');
  document.getElementById('edit-location').value = button.getAttribute('data-location');
  document.getElementById('edit-classification').value = button.getAttribute('data-classification');
  document.getElementById('edit-value').value = button.getAttribute('data-value');
  document.getElementById('edit-year').value = button.getAttribute('data-year');
});

// History modal
var historyModal = document.getElementById('historyModal');
historyModal.addEventListener('show.bs.modal', function (event) {
  var button = event.relatedTarget;
  var propertyId = button.getAttribute('data-id');
  var tbody = historyModal.querySelector('#historyTable tbody');
  tbody.innerHTML = "<tr><td colspan='4'>⏳ Loading...</td></tr>";
  fetch("property_history.php?property_id=" + propertyId)
    .then(res => res.text())
    .then(html => tbody.innerHTML = html);
});
</script>

<?php include 'footer.php'; ?>
