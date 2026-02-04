<!-- Edit Property Modal -->
<div class="modal fade" id="editPropertyModal<?=$row['id']?>" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="post">
        <input type="hidden" name="property_id" value="<?=$row['id']?>">
        <div class="modal-header">
          <h5 class="modal-title">Edit Property ID <?=$row['id']?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body row g-3">
          <div class="col-md-2"><input name="td_no" type="text" class="form-control" value="<?=htmlspecialchars($row['td_no'])?>" required></div>
          <div class="col-md-2"><input name="lot_no" type="text" class="form-control" value="<?=htmlspecialchars($row['lot_no'])?>" required></div>
          <div class="col-md-2"><input name="barangay" type="text" class="form-control" value="<?=htmlspecialchars($row['barangay'])?>"></div>
          <div class="col-md-2"><input name="location" type="text" class="form-control" value="<?=htmlspecialchars($row['location'])?>" required></div>
          <div class="col-md-2"><input name="classification" type="text" class="form-control" value="<?=htmlspecialchars($row['classification'])?>" required></div>
          <div class="col-md-2"><input name="assessed_value" type="number" step="0.01" class="form-control" value="<?=$row['assessed_value']?>" required></div>
          <div class="col-md-2"><input name="effective_year" type="number" class="form-control" value="<?=$row['effective_year']?>" required></div>
          <div class="col-md-2"><input name="revision_year" type="number" class="form-control" value="<?=$row['revision_year']?>" required></div>
          <div class="col-md-4">
            <select name="owner_id" class="form-select" required>
              <option value="">-- Select Owner --</option>
              <?php
              $ownersRes2 = $mysqli->query("SELECT id,name FROM owners ORDER BY name ASC");
              while($o2 = $ownersRes2->fetch_assoc()):
              ?>
              <option value="<?=$o2['id']?>" <?=($row['owner_id']==$o2['id']?'selected':'')?>><?=htmlspecialchars($o2['name'])?></option>
              <?php endwhile; ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary" name="edit_property">Update Property</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>
