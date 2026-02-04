<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
$messages = [];

/* ✅ Add helper to fix "undefined function q()" error */
if (!function_exists('q')) {
    function q($sql, $types = "", $params = []) {
        global $mysqli;
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            die("SQL Error: " . $mysqli->error);
        }
        if (!empty($types) && !empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt;
    }
}
/* ✅ End of helper fix */

// Only Assessor can access
if(!in_array($_SESSION['role'], ['admin','assessor','assessment_clerk','cashier'])) {
    header("Location: ../index.php");
    exit;
}

// --- Handle Add Assessment ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_assessment'])) {
    $property_id = (int)($_POST['property_id'] ?? 0);
    $tax_year = (int)($_POST['tax_year'] ?? date("Y"));
    $assessed_value = (float)($_POST['assessed_value'] ?? 0);
    $basic_tax_rate = (float)($_POST['basic_tax_rate'] ?? 0.01);
    $adjustments = (float)($_POST['adjustments'] ?? 0);

    if ($property_id > 0 && $tax_year > 0) {
        $basic_tax = $assessed_value * $basic_tax_rate;
        $sef_tax = $assessed_value * 0.01;

        // ✅ Include the 'year' field in INSERT — same as tax_year
q("INSERT INTO assessments (property_id, tax_year, year, assessed_value, basic_tax_rate, basic_tax, sef_tax, adjustments, status)
   VALUES (?,?,?,?,?,?,?,?,?)",
   "iiiddddss", [$property_id, $tax_year, $tax_year, $assessed_value, $basic_tax_rate, $basic_tax, $sef_tax, $adjustments, 'draft']);


        $messages[] = ['type' => 'success', 'text' => 'Assessment created successfully!'];
    } else {
        $messages[] = ['type' => 'danger', 'text' => 'Invalid property or tax year.'];
    }
}

// --- Handle Delete Assessment ---
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = q("SELECT status FROM assessments WHERE id=?", "i", [$id]);
    $stmt->bind_result($status);
    $stmt->fetch();
    $stmt->close();

    if ($status === 'draft') {
        q("DELETE FROM assessments WHERE id=?", "i", [$id]);
        $messages[] = ['type' => 'warning', 'text' => 'Draft assessment deleted!'];
    } else {
        $messages[] = ['type' => 'danger', 'text' => 'Only draft assessments can be deleted!'];
    }
}

// --- Handle Update Assessment ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_assessment'])) {
    $id = (int)($_POST['id'] ?? 0);
    $tax_year = (int)($_POST['tax_year'] ?? date("Y"));
    $assessed_value = (float)($_POST['assessed_value'] ?? 0);
    $basic_tax_rate = (float)($_POST['basic_tax_rate'] ?? 0.01);
    $adjustments = (float)($_POST['adjustments'] ?? 0);

    $basic_tax = $assessed_value * $basic_tax_rate;
    $sef_tax = $assessed_value * 0.01;

    $stmt = q("SELECT status FROM assessments WHERE id=?", "i", [$id]);
    $stmt->bind_result($status);
    $stmt->fetch();
    $stmt->close();

    if ($status === 'draft') {
        q("UPDATE assessments 
              SET tax_year=?, assessed_value=?, basic_tax_rate=?, basic_tax=?, sef_tax=?, adjustments=? 
            WHERE id=?",
          "idddddi", [$tax_year, $assessed_value, $basic_tax_rate, $basic_tax, $sef_tax, $adjustments, $id]);

        $messages[] = ['type' => 'info', 'text' => 'Assessment updated successfully!'];
    } else {
        $messages[] = ['type' => 'danger', 'text' => 'Only draft assessments can be edited!'];
    }
}

// --- Handle Unfinalize ---
if (isset($_GET['unfinalize'])) {
    $id = (int)$_GET['unfinalize'];
    $stmt = q("SELECT status FROM assessments WHERE id=?", "i", [$id]);
    $stmt->bind_result($status);
    $stmt->fetch();
    $stmt->close();

    if ($status === 'finalized') {
        q("UPDATE assessments SET status='draft' WHERE id=?", "i", [$id]);
        $messages[] = ['type' => 'warning', 'text' => 'Assessment moved back to Draft for editing.'];
    } else {
        $messages[] = ['type' => 'danger', 'text' => 'Only finalized assessments can be unfinalized!'];
    }
}

// --- Pagination & Search ---
$limit = 10;
$page = isset($_GET['page']) ? max(1,intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

$where = [];
$params = [];
$types = '';

// Property filter
if (!empty($_GET['property'])) {
    $where[] = "(p.td_no LIKE ? OR p.lot_no LIKE ?)";
    $like = "%".$_GET['property']."%";
    $params[] = $like;
    $params[] = $like;
    $types .= "ss";
}

// Owner filter
if (!empty($_GET['owner'])) {
    $where[] = "o.name LIKE ?";
    $params[] = "%".$_GET['owner']."%";
    $types .= "s";
}

// Barangay filter
if (!empty($_GET['barangay'])) {
    $where[] = "p.barangay LIKE ?";
    $params[] = "%".$_GET['barangay']."%";
    $types .= "s";
}

// Location filter
if (!empty($_GET['location'])) {
    $where[] = "p.location LIKE ?";
    $params[] = "%".$_GET['location']."%";
    $types .= "s";
}

// Tax Year filter
if (!empty($_GET['tax_year'])) {
    $where[] = "a.tax_year = ?";
    $params[] = $_GET['tax_year'];
    $types .= "i";
}

// Assessed Value filter
if (!empty($_GET['assessed_value'])) {
    $where[] = "a.assessed_value = ?";
    $params[] = $_GET['assessed_value'];
    $types .= "d";
}

$where_sql = $where ? "WHERE ".implode(" AND ", $where) : "";

// Total count
$sql_count = "SELECT COUNT(*) FROM assessments a 
              JOIN properties p ON p.id=a.property_id
              LEFT JOIN owners o ON o.id=p.owner_id
              $where_sql";
$stmt = $mysqli->prepare($sql_count);
if($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$stmt->bind_result($total);
$stmt->fetch();
$stmt->close();
$total_pages = ceil($total / $limit);

// Fetch assessments
$sql = "SELECT a.*, p.td_no, p.lot_no, COALESCE(p.barangay,'Blank') AS barangay, COALESCE(p.location,'Blank') AS location, o.name AS owner_name
        FROM assessments a
        JOIN properties p ON p.id=a.property_id
        LEFT JOIN owners o ON o.id=p.owner_id
        $where_sql
        ORDER BY a.id DESC
        LIMIT ? OFFSET ?";
$stmt = $mysqli->prepare($sql);
if($params){
    $stmt->bind_param($types.'ii', ...array_merge($params, [$limit, $offset]));
}else{
    $stmt->bind_param("ii", $limit, $offset);
}
$stmt->execute();
$res = $stmt->get_result();

// Fetch properties for Add Assessment
$propRes = $mysqli->query("SELECT p.id, p.td_no, p.lot_no, COALESCE(p.barangay,'Blank') AS barangay, COALESCE(p.location,'Blank') AS location, o.name as owner_name, p.assessed_value, p.effective_year
                           FROM properties p
                           LEFT JOIN owners o ON p.owner_id=o.id
                           ORDER BY p.id DESC");

// Fetch distinct values for filters
$barangayRes = $mysqli->query("SELECT DISTINCT COALESCE(barangay,'Blank') AS barangay FROM properties ORDER BY barangay ASC");
$ownerRes = $mysqli->query("SELECT DISTINCT o.name AS owner_name FROM owners o ORDER BY o.name ASC");
$propertyRes = $mysqli->query("SELECT DISTINCT td_no, lot_no FROM properties ORDER BY td_no ASC");
$yearRes = $mysqli->query("SELECT DISTINCT tax_year FROM assessments ORDER BY tax_year DESC");

?>

<h2 class="mb-4">Assessments</h2>

<!-- Messages -->
<?php foreach($messages as $m): ?>
    <div class="alert alert-<?=$m['type']?>"><?=$m['text']?></div>
<?php endforeach; ?>

<!-- Multi Search Form -->
<div class="card mb-3">
  <div class="card-body">
    <form method="get" class="row g-2">

      <!-- Property -->
      <div class="col-md-3">
        <select name="property" class="form-select">
          <option value="">-- All Properties --</option>
          <?php while($pr = $propertyRes->fetch_assoc()): ?>
            <?php $val = $pr['td_no'].' '.$pr['lot_no']; ?>
            <option value="<?=$val?>" <?=($_GET['property']??'')==$val?'selected':''?>>
              <?=htmlspecialchars($pr['td_no'])?> | Lot <?=htmlspecialchars($pr['lot_no'])?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <!-- Owner -->
      <div class="col-md-2">
        <select name="owner" class="form-select">
          <option value="">-- All Owners --</option>
          <?php while($or = $ownerRes->fetch_assoc()): ?>
            <option value="<?=$or['owner_name']?>" <?=($_GET['owner']??'')==$or['owner_name']?'selected':''?>>
              <?=htmlspecialchars($or['owner_name'])?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <!-- Barangay -->
      <div class="col-md-2">
        <select name="barangay" class="form-select">
          <option value="">-- All Barangays --</option>
          <?php while($br = $barangayRes->fetch_assoc()): ?>
            <option value="<?=$br['barangay']?>" <?=($_GET['barangay']??'')==$br['barangay']?'selected':''?>>
              <?=htmlspecialchars($br['barangay'])?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <!-- Location -->
      <div class="col-md-2">
        <input type="text" name="location" class="form-control" placeholder="Location" value="<?=htmlspecialchars($_GET['location'] ?? '')?>">
      </div>

      <!-- Tax Year -->
      <div class="col-md-1">
        <select name="tax_year" class="form-select">
          <option value="">Year</option>
          <?php while($yr = $yearRes->fetch_assoc()): ?>
            <option value="<?=$yr['tax_year']?>" <?=($_GET['tax_year']??'')==$yr['tax_year']?'selected':''?>>
              <?=$yr['tax_year']?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <!-- Assessed Value -->
      <div class="col-md-2">
        <input type="number" step="0.01" name="assessed_value" class="form-control" placeholder="Assessed Value" value="<?=htmlspecialchars($_GET['assessed_value'] ?? '')?>">
      </div>

      <!-- Buttons -->
      <div class="col-md-12 text-end">
        <button class="btn btn-primary btn-sm">🔍 Search</button>
        <a href="assessments.php" class="btn btn-secondary btn-sm">Reset</a>
      </div>
    </form>
  </div>
</div>

<!-- Add Assessment Form -->
<div class="card mb-4">
  <div class="card-header bg-primary text-white">Add New Assessment</div>
  <div class="card-body">
    <form method="post" class="row g-3" id="assessment-form">
      <div class="col-md-3">
        <label class="form-label">Property</label>
        <select name="property_id" id="property-select" class="form-select" required>
          <option value="">-- Select Property --</option>
          <?php while($p = $propRes->fetch_assoc()): ?>
            <option value="<?=$p['id']?>" 
                    data-assessed="<?=$p['assessed_value']?>" 
                    data-effective="<?=$p['effective_year']?>"
                    data-barangay="<?=$p['barangay']?>"
                    data-location="<?=$p['location']?>">
              <?=htmlspecialchars($p['td_no'])?> | Lot <?=htmlspecialchars($p['lot_no'])?> | <?=htmlspecialchars($p['owner_name'])?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label">Tax Year</label>
        <input name="tax_year" id="tax-year-input" type="number" class="form-control" value="<?=date('Y')?>" required>
      </div>
      <div class="col-md-2">
        <label class="form-label">Assessed Value</label>
        <input name="assessed_value" id="assessed-value-input" type="number" step="0.01" class="form-control" required>
      </div>
      <div class="col-md-2">
        <label class="form-label">Basic Tax Rate</label>
        <input name="basic_tax_rate" type="number" step="0.01" class="form-control" value="0.01">
      </div>
      <div class="col-md-2">
        <label class="form-label">Adjustments</label>
        <input name="adjustments" type="number" step="0.01" class="form-control" value="0">
      </div>
      <div class="col-md-1 align-self-end">
        <button class="btn btn-success" name="add_assessment">Save</button>
      </div>
      <div class="col-md-2">
        <label class="form-label">Barangay</label>
        <input type="text" id="barangay-input" class="form-control" readonly>
      </div>
      <div class="col-md-2">
        <label class="form-label">Location</label>
        <input type="text" id="location-input" class="form-control" readonly>
      </div>
    </form>
  </div>
</div>

<!-- Assessment List Table -->
<div class="card">
  <div class="card-header bg-secondary text-white">Assessment List</div>
  <div class="card-body table-responsive">
    <table class="table table-bordered table-hover align-middle">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Property</th>
          <th>Owner</th>
          <th>Barangay</th>
          <th>Location</th>
          <th>Tax Year</th>
          <th>Assessed Value</th>
          <th>Basic Tax</th>
          <th>SEF Tax</th>
          <th>Adjustments</th>
          <th>Total Tax</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php while($row = $res->fetch_assoc()): ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= htmlspecialchars($row['td_no']) ?> | Lot <?= htmlspecialchars($row['lot_no']) ?></td>
          <td><?= htmlspecialchars($row['owner_name']) ?></td>
          <td><?= htmlspecialchars($row['barangay']) ?></td>
          <td><?= htmlspecialchars($row['location']) ?></td>
          <td><?= $row['tax_year'] ?></td>
          <td>₱<?= number_format($row['assessed_value'], 2) ?></td>
          <td>₱<?= number_format($row['basic_tax'], 2) ?></td>
          <td>₱<?= number_format($row['sef_tax'], 2) ?></td>
          <td>₱<?= number_format($row['adjustments'], 2) ?></td>
          <td>₱<?= number_format($row['basic_tax'] + $row['sef_tax'] + $row['adjustments'], 2) ?></td>
          <td><?= ucfirst($row['status']) ?></td>
          <td>
            <?php if($row['status']=='draft'): ?>
            <div class="btn-group btn-group-sm">
              <button type="button" class="btn btn-warning" 
                      data-bs-toggle="modal" data-bs-target="#editModal"
                      data-id="<?=$row['id']?>"
                      data-property-id="<?=$row['property_id']?>"
                      data-taxyear="<?=$row['tax_year']?>"
                      data-value="<?=$row['assessed_value']?>"
                      data-rate="<?=$row['basic_tax_rate']?>"
                      data-adj="<?=$row['adjustments']?>">✏️ Edit</button>
              <a href="assessments.php?delete=<?=$row['id']?>" class="btn btn-danger" onclick="return confirm('Delete this assessment?')">🗑 Delete</a>
              <a href="finalize_assessment.php?assessment_id=<?=$row['id']?>" class="btn btn-success">✔ Finalize</a>
            </div>
            <?php else: ?>
            <div class="btn-group btn-group-sm">
              <a href="assessments.php?unfinalize=<?=$row['id']?>" 
                 class="btn btn-secondary"
                 onclick="return confirm('Move this assessment back to Draft for editing?')">🔓 Unfinalize</a>
            </div>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>

    <!-- Pagination -->
    <nav>
      <ul class="pagination justify-content-center">
        <?php for ($i=1; $i<=$total_pages; $i++): ?>
          <li class="page-item <?=($i==$page)?'active':''?>">
            <a class="page-link" href="?page=<?=$i?>"><?=$i?></a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>
  </div>
</div>

<!-- Edit Assessment Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="post" id="editAssessmentForm">
        <div class="modal-header bg-warning text-dark">
          <h5 class="modal-title">Edit Assessment</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body row g-3">
          <input type="hidden" name="id" id="edit-id">
          <div class="col-md-4">
            <label class="form-label">Tax Year</label>
            <input name="tax_year" id="edit-taxyear" type="number" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Assessed Value</label>
            <input name="assessed_value" id="edit-value" type="number" step="0.01" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Basic Tax Rate</label>
            <input name="basic_tax_rate" id="edit-rate" type="number" step="0.01" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Adjustments</label>
            <input name="adjustments" id="edit-adj" type="number" step="0.01" class="form-control">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="update_assessment" class="btn btn-primary">💾 Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Auto-fill assessed value, barangay, location when property selected
document.getElementById('property-select').addEventListener('change', function() {
  const opt = this.options[this.selectedIndex];
  document.getElementById('assessed-value-input').value = opt.dataset.assessed || '';
  document.getElementById('barangay-input').value = opt.dataset.barangay || '';
  document.getElementById('location-input').value = opt.dataset.location || '';
});

// Edit modal data fill
const editModal = document.getElementById('editModal');
editModal.addEventListener('show.bs.modal', function (event) {
  const btn = event.relatedTarget;
  document.getElementById('edit-id').value = btn.getAttribute('data-id');
  document.getElementById('edit-taxyear').value = btn.getAttribute('data-taxyear');
  document.getElementById('edit-value').value = btn.getAttribute('data-value');
  document.getElementById('edit-rate').value = btn.getAttribute('data-rate');
  document.getElementById('edit-adj').value = btn.getAttribute('data-adj');
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
