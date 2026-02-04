<?php
require 'db.php';
$messages = [];

// --- Handle Add Property ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_property'])) {
    $td_no = trim($_POST['td_no'] ?? '');
    $lot_no = trim($_POST['lot_no'] ?? '');
    $barangay = trim($_POST['barangay'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $owner_id = (int)($_POST['owner_id'] ?? 0);
    $classification = trim($_POST['classification'] ?? '');
    $assessed_value = (float)($_POST['assessed_value'] ?? 0);
    $effective_year = (int)($_POST['effective_year'] ?? 0);
    $revision_year = (int)($_POST['revision_year'] ?? 0);

    if ($td_no && $lot_no && $location && $owner_id > 0 && $assessed_value > 0 && $classification && $effective_year > 0 && $revision_year > 0) {
        $stmt = $mysqli->prepare("INSERT INTO properties (td_no, lot_no, barangay, location, owner_id, classification, assessed_value, effective_year, revision_year) VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("ssssisiis", $td_no, $lot_no, $barangay, $location, $owner_id, $classification, $assessed_value, $effective_year, $revision_year);
        $stmt->execute();
        $stmt->close();
        $messages[] = ['type'=>'success','text'=>'Property added successfully!'];
    } else {
        $messages[] = ['type'=>'danger','text'=>'Please fill all fields!'];
    }
}

// --- Handle Edit Property ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_property'])) {
    $id = (int)($_POST['property_id'] ?? 0);
    $td_no = trim($_POST['td_no'] ?? '');
    $lot_no = trim($_POST['lot_no'] ?? '');
    $barangay = trim($_POST['barangay'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $owner_id = (int)($_POST['owner_id'] ?? 0);
    $classification = trim($_POST['classification'] ?? '');
    $assessed_value = (float)($_POST['assessed_value'] ?? 0);
    $effective_year = (int)($_POST['effective_year'] ?? 0);
    $revision_year = (int)($_POST['revision_year'] ?? 0);

    if ($id && $td_no && $lot_no && $location && $owner_id > 0 && $assessed_value > 0 && $classification && $effective_year > 0 && $revision_year > 0) {
        $stmt = $mysqli->prepare("UPDATE properties SET td_no=?, lot_no=?, barangay=?, location=?, owner_id=?, classification=?, assessed_value=?, effective_year=?, revision_year=? WHERE id=?");
        $stmt->bind_param("ssssisiisi", $td_no, $lot_no, $barangay, $location, $owner_id, $classification, $assessed_value, $effective_year, $revision_year, $id);
        $stmt->execute();
        $stmt->close();
        $messages[] = ['type'=>'success','text'=>'Property updated successfully!'];
    } else {
        $messages[] = ['type'=>'danger','text'=>'Please fill all fields for update!'];
    }
}

// --- Handle Delete Property ---
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    $stmt = $mysqli->prepare("DELETE FROM properties WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: properties.php");
    exit;
}

// --- Pagination/Search ---
$limit = 10;
$page = isset($_GET['page']) ? max(1,intval($_GET['page'])) : 1;
$offset = ($page-1)*$limit;
$search = trim($_GET['search'] ?? '');
$where = [];
$params = [];
$types = '';

if ($search !== '') {
    $where[] = "(p.td_no LIKE ? OR p.lot_no LIKE ? OR p.location LIKE ? OR p.barangay LIKE ? OR o.name LIKE ? OR p.classification LIKE ?)";
    $like = "%$search%";
    $params = [$like,$like,$like,$like,$like,$like];
    $types = "ssssss";
}
$where_sql = $where ? "WHERE ".implode(" AND ", $where) : "";

// Total count
$sql_count = "SELECT COUNT(*) FROM properties p LEFT JOIN owners o ON o.id=p.owner_id $where_sql";
$stmt = $mysqli->prepare($sql_count);
if($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$stmt->bind_result($total);
$stmt->fetch();
$stmt->close();
$total_pages = ceil($total/$limit);

// Fetch properties
$sql = "SELECT p.*, o.name AS owner_name FROM properties p LEFT JOIN owners o ON o.id=p.owner_id $where_sql ORDER BY p.id DESC LIMIT ? OFFSET ?";
$stmt = $mysqli->prepare($sql);
if($params){
    $stmt->bind_param($types.'ii', ...array_merge($params, [$limit, $offset]));
}else{
    $stmt->bind_param("ii", $limit, $offset);
}
$stmt->execute();
$res = $stmt->get_result();

include 'header.php';
?>

<style>
/* Fixed Actions column and compact buttons */
table th:last-child,
table td:last-child {
    width: 160px;
    white-space: nowrap;
}
table td:last-child .btn {
    font-size: 0.75rem;
    padding: 0.25rem 0.4rem;
}
table td:last-child .d-flex {
    gap: 0.25rem;
}
</style>

<h2 class="mb-4">Properties</h2>

<?php foreach($messages as $m): ?>
<div class="alert alert-<?=$m['type']?>"><?=$m['text']?></div>
<?php endforeach; ?>

<!-- Search Form -->
<form method="get" class="row g-2 mb-3">
  <div class="col-md-3">
    <input type="text" name="search" class="form-control" placeholder="Search TD, Lot, Barangay, Owner, Location, Classification" value="<?=htmlspecialchars($search)?>">
  </div>
  <div class="col-md-2">
    <button class="btn btn-primary">Search</button>
    <a href="properties.php" class="btn btn-secondary">Reset</a>
  </div>
</form>

<!-- Add Property Card -->
<div class="card mb-4">
  <div class="card-header bg-primary text-white">Add New Property</div>
  <div class="card-body">
    <?php include 'modals/add_property_modal.php'; ?>
  </div>
</div>

<!-- Property Table -->
<div class="card">
  <div class="card-header bg-secondary text-white">Property List</div>
  <div class="card-body table-responsive">
    <table class="table table-bordered table-hover align-middle">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>TD No</th>
          <th>Lot</th>
          <th>Barangay</th>
          <th>Location</th>
          <th>Owner</th>
          <th>Classification</th>
          <th>Assessed Value</th>
          <th>Effective Year</th>
          <th>Revision Year</th>
          <th>Last Assessment</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = $res->fetch_assoc()): ?>
        <tr>
          <td><?=$row['id']?></td>
          <td><?=htmlspecialchars($row['td_no'])?></td>
          <td><?=htmlspecialchars($row['lot_no'])?></td>
          <td><?=htmlspecialchars($row['barangay'] ?: 'Blank')?></td>
          <td><?=htmlspecialchars($row['location'] ?: 'Blank')?></td>
          <td><?=htmlspecialchars($row['owner_name'])?></td>
          <td><?=htmlspecialchars($row['classification'])?></td>
          <td>₱<?=number_format($row['assessed_value'],2)?></td>
          <td><?=$row['effective_year']?></td>
          <td><?=$row['revision_year']?></td>
          <td>
            <?php
            $assessRes = $mysqli->query("
                SELECT * FROM assessments 
                WHERE property_id={$row['id']} 
                ORDER BY id DESC 
                LIMIT 1
            ");
            if($assessRes->num_rows){
                $latest = $assessRes->fetch_assoc();
                $total = $latest['basic_tax'] + $latest['sef_tax'] + $latest['adjustments'];
                echo "Year: {$latest['tax_year']} | Total: ₱".number_format($total,2);
            }else{
                echo '-';
            }
            ?>
          </td>
          <td>
            <div class="d-flex flex-wrap gap-1">
              <a class="btn btn-sm btn-primary" data-bs-toggle="collapse" href="#history<?=$row['id']?>" role="button">History</a>
              <a href="assessments.php?property_id=<?=$row['id']?>" class="btn btn-sm btn-success">Assessment</a>
              <a class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editPropertyModal<?=$row['id']?>">Edit</a>
              <a href="?delete_id=<?=$row['id']?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this property?')">Delete</a>
            </div>
          </td>
        </tr>
        <tr class="collapse bg-light" id="history<?=$row['id']?>"> 
          <td colspan="12">
            <table class="table table-sm table-bordered mb-0">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Year</th>
                  <th>Assessed Value</th>
                  <th>Barangay</th>
                  <th>Location</th>
                  <th>Basic Tax</th>
                  <th>SEF Tax</th>
                  <th>Adjustments</th>
                  <th>Total Tax</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $assessRes2 = $mysqli->query("SELECT * FROM assessments WHERE property_id={$row['id']} ORDER BY id DESC");
                while($a = $assessRes2->fetch_assoc()):
                    $total = $a['basic_tax'] + $a['sef_tax'] + $a['adjustments'];
                ?>
                <tr>
                  <td><?=$a['id']?></td>
                  <td><?=$a['tax_year']?></td>
                  <td>₱<?=number_format($a['assessed_value'],2)?></td>
                  <td><?=isset($a['barangay']) && $a['barangay'] !== '' ? htmlspecialchars($a['barangay']) : 'Blank'?></td>
                  <td><?=isset($a['location']) && $a['location'] !== '' ? htmlspecialchars($a['location']) : 'Blank'?></td>
                  <td>₱<?=number_format($a['basic_tax'],2)?></td>
                  <td>₱<?=number_format($a['sef_tax'],2)?></td>
                  <td>₱<?=number_format($a['adjustments'],2)?></td>
                  <td>₱<?=number_format($total,2)?></td>
                  <td><?=ucfirst($a['status'])?></td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>

    <!-- Pagination -->
    <nav>
      <ul class="pagination justify-content-center mt-3">
        <li class="page-item <?=($page<=1?'disabled':'')?>"><a class="page-link" href="?page=<?=$page-1?>&search=<?=urlencode($search)?>">Previous</a></li>
        <?php for($i=1;$i<=$total_pages;$i++): ?>
        <li class="page-item <?=($i==$page?'active':'')?>"><a class="page-link" href="?page=<?=$i?>&search=<?=urlencode($search)?>"><?=$i?></a></li>
        <?php endfor; ?>
        <li class="page-item <?=($page>=$total_pages?'disabled':'')?>"><a class="page-link" href="?page=<?=$page+1?>&search=<?=urlencode($search)?>">Next</a></li>
      </ul>
    </nav>
  </div>
</div>

<?php include 'modals/edit_property_modal.php'; ?>
<?php include 'footer.php'; ?>
