<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';

$messages = [];

// --- Role restriction ---
if ($_SESSION['role'] !== 'assessor') {
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
        q("INSERT INTO properties (td_no, lot_no, owner_id, barangay, location, classification, assessed_value, effective_year)
           VALUES (?,?,?,?,?,?,?,?)",
          "ssisssid", [$td_no, $lot_no, $owner_id, $barangay, $location, $classification, $assessed_value, $effective_year]);
        $messages[] = ['type'=>'success','text'=>'Property added successfully!'];
    } else {
        $messages[] = ['type'=>'danger','text'=>'Please fill out all required fields.'];
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
        q("UPDATE properties SET td_no=?, lot_no=?, owner_id=?, barangay=?, location=?, classification=?, assessed_value=?, effective_year=? WHERE id=?",
          "ssisssidi", [$td_no, $lot_no, $owner_id, $barangay, $location, $classification, $assessed_value, $effective_year, $id]);
        $messages[] = ['type'=>'success','text'=>'Property updated successfully!'];
    } else {
        $messages[] = ['type'=>'danger','text'=>'Invalid property update!'];
    }
}

// --- Delete Property ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_property'])) {
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
        q("DELETE FROM properties WHERE id=?", "i", [$id]);
        $messages[] = ['type'=>'success','text'=>'Property deleted successfully!'];
    }
}

// --- Fetch owners ---
$owners = $mysqli->query("SELECT id, name FROM owners ORDER BY name ASC");

// --- Search, Filter, Pagination ---
$where = "WHERE 1=1";
$params = [];
$types = "";

$search = trim($_GET['search'] ?? '');
if ($search) {
    $where .= " AND (p.td_no LIKE ? OR p.lot_no LIKE ? OR o.name LIKE ? OR p.location LIKE ? OR p.classification LIKE ?)";
    $like = "%$search%";
    $params = array_merge($params, [$like,$like,$like,$like,$like]);
    $types .= "sssss";
}

$filter_classification = trim($_GET['classification'] ?? '');
if ($filter_classification) {
    $where .= " AND p.classification LIKE ?";
    $params[] = "%$filter_classification%";
    $types .= "s";
}

$filter_barangay = trim($_GET['barangay'] ?? '');
if ($filter_barangay) {
    $where .= " AND p.barangay LIKE ?";
    $params[] = "%$filter_barangay%";
    $types .= "s";
}

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

$countSql = "SELECT COUNT(*) AS cnt FROM properties p LEFT JOIN owners o ON o.id=p.owner_id $where";
$countStmt = $mysqli->prepare($countSql);
if ($types) $countStmt->bind_param($types, ...$params);
$countStmt->execute();
$totalRows = $countStmt->get_result()->fetch_assoc()['cnt'];
$totalPages = ceil($totalRows / $limit);

$sql = "SELECT p.*, o.name AS owner_name FROM properties p LEFT JOIN owners o ON o.id=p.owner_id $where ORDER BY p.id DESC LIMIT $limit OFFSET $offset";
$stmt = $mysqli->prepare($sql);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();
?>

<h2 class="mb-4">🏠 Properties</h2>

<?php foreach ($messages as $m): ?>
<div class="alert alert-<?=$m['type']?>"><?=$m['text']?></div>
<?php endforeach; ?>

<!-- Search & Filter -->
<div class="card mb-3">
  <div class="card-body">
    <form method="get" class="row g-2 align-items-center">
      <div class="col-md-3">
        <input type="text" name="search" value="<?=htmlspecialchars($search)?>" class="form-control" placeholder="Search TD No, Lot No, Owner...">
      </div>
      <div class="col-md-3">
        <input type="text" name="classification" value="<?=htmlspecialchars($filter_classification)?>" class="form-control" placeholder="Classification">
      </div>
      <div class="col-md-3">
        <input type="text" name="barangay" value="<?=htmlspecialchars($filter_barangay)?>" class="form-control" placeholder="Barangay">
      </div>
      <div class="col-md-3 text-end">
        <button class="btn btn-primary">Search</button>
        <a href="properties.php" class="btn btn-secondary">Reset</a>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addPropertyModal">+ Add Property</button>
      </div>
    </form>
  </div>
</div>

<!-- Property List -->
<div class="card">
  <div class="card-header bg-secondary text-white">Property List</div>
  <div class="card-body table-responsive">
    <table class="table table-bordered table-hover align-middle">
      <thead class="table-dark">
        <tr>
          <th>ID</th><th>TD No</th><th>Lot No</th><th>Owner</th><th>Barangay</th>
          <th>Location</th><th>Classification</th><th>Assessed Value</th><th>Effective Year</th><th>Actions</th>
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
          <td>
            <button class="btn btn-sm btn-primary"
              data-bs-toggle="modal"
              data-bs-target="#editModal"
              data-id="<?=$row['id']?>"
              data-td="<?=$row['td_no']?>"
              data-lot="<?=$row['lot_no']?>"
              data-owner="<?=$row['owner_id']?>"
              data-barangay="<?=$row['barangay']?>"
              data-location="<?=$row['location']?>"
              data-classification="<?=$row['classification']?>"
              data-value="<?=$row['assessed_value']?>"
              data-year="<?=$row['effective_year']?>">Edit</button>
            <form method="post" class="d-inline" onsubmit="return confirm('Delete this property?')">
              <input type="hidden" name="id" value="<?=$row['id']?>">
              <button class="btn btn-sm btn-danger" name="delete_property">Delete</button>
            </form>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>

    <!-- Pagination -->
    <nav>
      <ul class="pagination justify-content-center">
        <?php for($p=1;$p<=$totalPages;$p++): ?>
          <li class="page-item <?=($p==$page)?'active':''?>">
            <a class="page-link" href="?page=<?=$p?>&search=<?=urlencode($search)?>&classification=<?=urlencode($filter_classification)?>&barangay=<?=urlencode($filter_barangay)?>"><?=$p?></a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>
  </div>
</div>

<!-- Add Property Modal -->
<div class="modal fade" id="addPropertyModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <form method="post" class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">Add New Property</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <div class="col-md-4">
          <label class="form-label">TD No</label>
          <input type="text" name="td_no" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Lot No</label>
          <input type="text" name="lot_no" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Owner</label>
          <select name="owner_id" class="form-select" required>
            <option value="">-- Select Owner --</option>
            <?php
            $ownerOpts = $mysqli->query("SELECT id,name FROM owners ORDER BY name ASC");
            while($o=$ownerOpts->fetch_assoc()): ?>
              <option value="<?=$o['id']?>"><?=$o['name']?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Barangay</label>
          <input type="text" name="barangay" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Location</label>
          <input type="text" name="location" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Classification</label>
          <input type="text" name="classification" class="form-control">
        </div>
        <div class="col-md-3">
          <label class="form-label">Assessed Value</label>
          <input type="number" step="0.01" name="assessed_value" class="form-control">
        </div>
        <div class="col-md-3">
          <label class="form-label">Effective Year</label>
          <input type="number" name="effective_year" class="form-control">
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-success" name="add_property">Save</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Property Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <form method="post" class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Edit Property</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <input type="hidden" name="id" id="edit-id">
        <div class="col-md-4"><label class="form-label">TD No</label>
          <input type="text" name="td_no" id="edit-td" class="form-control" required></div>
        <div class="col-md-4"><label class="form-label">Lot No</label>
          <input type="text" name="lot_no" id="edit-lot" class="form-control" required></div>
        <div class="col-md-4"><label class="form-label">Owner</label>
          <select name="owner_id" id="edit-owner" class="form-select" required>
            <option value="">-- Select Owner --</option>
            <?php
            $ownerOpts = $mysqli->query("SELECT id,name FROM owners ORDER BY name ASC");
            while($o=$ownerOpts->fetch_assoc()): ?>
              <option value="<?=$o['id']?>"><?=$o['name']?></option>
            <?php endwhile; ?>
          </select></div>
        <div class="col-md-4"><label class="form-label">Barangay</label>
          <input type="text" name="barangay" id="edit-barangay" class="form-control"></div>
        <div class="col-md-4"><label class="form-label">Location</label>
          <input type="text" name="location" id="edit-location" class="form-control"></div>
        <div class="col-md-4"><label class="form-label">Classification</label>
          <input type="text" name="classification" id="edit-classification" class="form-control"></div>
        <div class="col-md-3"><label class="form-label">Assessed Value</label>
          <input type="number" step="0.01" name="assessed_value" id="edit-value" class="form-control"></div>
        <div class="col-md-3"><label class="form-label">Effective Year</label>
          <input type="number" name="effective_year" id="edit-year" class="form-control"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary" name="update_property">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
document.getElementById('editModal').addEventListener('show.bs.modal', e => {
  const b = e.relatedTarget;
  document.getElementById('edit-id').value = b.dataset.id;
  document.getElementById('edit-td').value = b.dataset.td;
  document.getElementById('edit-lot').value = b.dataset.lot;
  document.getElementById('edit-owner').value = b.dataset.owner;
  document.getElementById('edit-barangay').value = b.dataset.barangay;
  document.getElementById('edit-location').value = b.dataset.location;
  document.getElementById('edit-classification').value = b.dataset.classification;
  document.getElementById('edit-value').value = b.dataset.value;
  document.getElementById('edit-year').value = b.dataset.year;
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
