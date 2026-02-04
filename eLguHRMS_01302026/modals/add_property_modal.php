<form method="post" class="row g-3">
  <div class="col-md-2">
    <label class="form-label">TD No</label>
    <input name="td_no" type="text" class="form-control" required>
  </div>
  <div class="col-md-2">
    <label class="form-label">Lot No</label>
    <input name="lot_no" type="text" class="form-control" required>
  </div>
  <div class="col-md-2">
    <label class="form-label">Barangay</label>
    <input name="barangay" type="text" class="form-control">
  </div>
  <div class="col-md-2">
    <label class="form-label">Location</label>
    <input name="location" type="text" class="form-control" required>
  </div>
  <div class="col-md-2">
    <label class="form-label">Classification</label>
    <input name="classification" type="text" class="form-control" required>
  </div>
  <div class="col-md-2">
    <label class="form-label">Assessed Value</label>
    <input name="assessed_value" type="number" step="0.01" class="form-control" required>
  </div>
  <div class="col-md-2">
    <label class="form-label">Effective Year</label>
    <input name="effective_year" type="number" class="form-control" required>
  </div>
  <div class="col-md-2">
    <label class="form-label">Revision Year</label>
    <input name="revision_year" type="number" class="form-control" required>
  </div>
  <div class="col-md-4">
    <label class="form-label">Owner</label>
    <select name="owner_id" class="form-select" required>
      <option value="">-- Select Owner --</option>
      <?php
      $ownersRes = $mysqli->query("SELECT id,name FROM owners ORDER BY name ASC");
      while($o = $ownersRes->fetch_assoc()):
      ?>
      <option value="<?=$o['id']?>"><?=htmlspecialchars($o['name'])?></option>
      <?php endwhile; ?>
    </select>
  </div>
  <div class="col-md-12 text-end">
    <button class="btn btn-success" name="add_property">Add Property</button>
  </div>
</form>
