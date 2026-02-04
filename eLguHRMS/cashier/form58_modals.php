<?php
// AUTO-SUGGEST OR NO for Add Form58 (current logged-in user)
$current_user_id = $_SESSION['user_id'] ?? 0; // assuming your session stores user_id
$current_user_name = $_SESSION['name'] ?? '';
$last_or = 0;

if ($current_user_id || $current_user_name) {
    // Use created_by as username if user_id not available in form58 table
    $stmt = $mysqli->prepare("SELECT or_no FROM form58 WHERE created_by=? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param('s', $current_user_name); // adjust to 'i' if using numeric user_id
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        $last_or = intval($res->fetch_assoc()['or_no']);
    }
    $stmt->close();
}
$next_or = $last_or + 1;
?>

<!-- Add Form58 Modal -->
<div class="modal fade" id="addForm58Modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form method="POST" class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">➕ New Form 58</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row mb-2">
          <div class="col-md-4">
            <label>OR No</label>
            <input type="text" name="or_no" class="form-control" value="<?= $next_or ?>" required>
          </div>
          <div class="col-md-4">
            <label>Date Paid</label>
            <input type="date" name="date_paid" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label>Payor Name</label>
            <input type="text" name="payor_name" class="form-control" required>
          </div>
        </div>
        <!-- Rest of the fields remain unchanged -->
        <div class="row mb-2">
          <div class="col-md-6"><label>City/Municipality</label><input type="text" name="city_or_municipality" class="form-control"></div>
          <div class="col-md-6"><label>Province</label><input type="text" name="province" class="form-control"></div>
        </div>
        <div class="row mb-2">
          <div class="col-md-6"><label>Name of Deceased</label><input type="text" name="name_of_deceased" class="form-control"></div>
          <div class="col-md-6"><label>Nationality</label><input type="text" name="nationality" class="form-control"></div>
        </div>
        <div class="row mb-2">
          <div class="col-md-3"><label>Age</label><input type="number" name="age" class="form-control"></div>
          <div class="col-md-3"><label>Sex</label>
            <select name="sex" class="form-select">
              <option value="Male">Male</option>
              <option value="Female">Female</option>
            </select>
          </div>
          <div class="col-md-6"><label>Date of Death</label><input type="date" name="date_of_death" class="form-control"></div>
        </div>
        <div class="row mb-2">
          <div class="col-md-6"><label>Case of Death</label><input type="text" name="case_of_death" class="form-control"></div>
          <div class="col-md-6"><label>Name of Cemetery</label><input type="text" name="name_of_cemetery" class="form-control"></div>
        </div>
        <div class="row mb-2">
          <div class="col-md-4"><label>Infectious/Non-Infectious</label><input type="text" name="infectious_or_noninfectious" class="form-control"></div>
          <div class="col-md-4"><label>Embalmed / Not Embalmed</label><input type="text" name="embalmed_or_notembalmed" class="form-control"></div>
          <div class="col-md-4"><label>Disposition of Remains</label><input type="text" name="disposition_of_remains" class="form-control"></div>
        </div>
        <div class="row mb-2">
          <div class="col-md-4"><label>Amount of Fee</label><input type="number" name="amount_of_fee" class="form-control" step="0.01"></div>
          <div class="col-md-4"><label>Payment Date</label><input type="date" name="payment_date" class="form-control"></div>
          <div class="col-md-4"><label>Amount Received</label><input type="number" name="amount_received" class="form-control" step="0.01"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="add_form58" class="btn btn-success">💾 Save Form 58</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </form>
  </div>
</div>


<!-- Edit Form58 Modal -->
<?php foreach($form58_records as $r): ?>
<div class="modal fade" id="editModal<?= $r['id'] ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form method="POST" class="modal-content">
      <input type="hidden" name="form58_id" value="<?= $r['id'] ?>">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title">✏️ Edit Form 58</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <!-- Same fields as Add Modal, prefilled -->
        <div class="row mb-2">
          <div class="col-md-4"><label>OR No</label><input type="text" name="or_no" class="form-control" value="<?= htmlspecialchars($r['or_no']) ?>" required></div>
          <div class="col-md-4"><label>Date Paid</label><input type="date" name="date_paid" class="form-control" value="<?= $r['date_paid'] ?>" required></div>
          <div class="col-md-4"><label>Payor Name</label><input type="text" name="payor_name" class="form-control" value="<?= htmlspecialchars($r['payor_name']) ?>" required></div>
        </div>
        <div class="row mb-2">
          <div class="col-md-6"><label>City/Municipality</label><input type="text" name="city_or_municipality" class="form-control" value="<?= htmlspecialchars($r['city_or_municipality']) ?>"></div>
          <div class="col-md-6"><label>Province</label><input type="text" name="province" class="form-control" value="<?= htmlspecialchars($r['province']) ?>"></div>
        </div>
        <div class="row mb-2">
          <div class="col-md-6"><label>Name of Deceased</label><input type="text" name="name_of_deceased" class="form-control" value="<?= htmlspecialchars($r['name_of_deceased']) ?>"></div>
          <div class="col-md-6"><label>Nationality</label><input type="text" name="nationality" class="form-control" value="<?= htmlspecialchars($r['nationality']) ?>"></div>
        </div>
        <div class="row mb-2">
          <div class="col-md-3"><label>Age</label><input type="number" name="age" class="form-control" value="<?= $r['age'] ?>"></div>
          <div class="col-md-3"><label>Sex</label>
            <select name="sex" class="form-select">
              <option value="Male" <?= $r['sex']=='Male'?'selected':'' ?>>Male</option>
              <option value="Female" <?= $r['sex']=='Female'?'selected':'' ?>>Female</option>
            </select>
          </div>
          <div class="col-md-6"><label>Date of Death</label><input type="date" name="date_of_death" class="form-control" value="<?= $r['date_of_death'] ?>"></div>
        </div>
        <div class="row mb-2">
          <div class="col-md-6"><label>Case of Death</label><input type="text" name="case_of_death" class="form-control" value="<?= htmlspecialchars($r['case_of_death']) ?>"></div>
          <div class="col-md-6"><label>Name of Cemetery</label><input type="text" name="name_of_cemetery" class="form-control" value="<?= htmlspecialchars($r['name_of_cemetery']) ?>"></div>
        </div>
        <div class="row mb-2">
          <div class="col-md-4"><label>Infectious/Non-Infectious</label><input type="text" name="infectious_or_noninfectious" class="form-control" value="<?= htmlspecialchars($r['infectious_or_noninfectious']) ?>"></div>
          <div class="col-md-4"><label>Embalmed / Not Embalmed</label><input type="text" name="embalmed_or_notembalmed" class="form-control" value="<?= htmlspecialchars($r['embalmed_or_notembalmed']) ?>"></div>
          <div class="col-md-4"><label>Disposition of Remains</label><input type="text" name="disposition_of_remains" class="form-control" value="<?= htmlspecialchars($r['disposition_of_remains']) ?>"></div>
        </div>
        <div class="row mb-2">
          <div class="col-md-4"><label>Amount of Fee</label><input type="number" name="amount_of_fee" class="form-control" step="0.01" value="<?= $r['amount_of_fee'] ?>"></div>
          <div class="col-md-4"><label>Payment Date</label><input type="date" name="payment_date" class="form-control" value="<?= $r['payment_date'] ?>"></div>
          <div class="col-md-4"><label>Amount Received</label><input type="number" name="amount_received" class="form-control" step="0.01" value="<?= $r['amount_received'] ?>"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="edit_form58" class="btn btn-warning">💾 Update Form 58</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </form>
  </div>
</div>
<?php endforeach; ?>
