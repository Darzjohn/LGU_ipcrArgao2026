<?php
$assessRes2 = $mysqli->query("SELECT * FROM assessments WHERE property_id={$row['id']} ORDER BY id DESC");
while($a = $assessRes2->fetch_assoc()):
?>
<div class="modal fade" id="editAssessmentModal<?=$a['id']?>" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="post">
        <input type="hidden" name="assessment_id" value="<?=$a['id']?>">
        <div class="modal-header">
          <h5 class="modal-title">Edit Assessment ID <?=$a['id']?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body row g-3">
          <div class="col-md-3"><input type="number" name="tax_year" class="form-control" value="<?=$a['tax_year']?>" required></div>
          <div class="col-md-3"><input type="number" step="0.01" name="assessed_value" class="form-control" value="<?=$a['assessed_value']?>" required></div>
          <div class="col-md-3"><input type="text" name="barangay" class="form-control" value="<?=htmlspecialchars($a['barangay'])?>"></div>
          <div class="col-md-3"><input type="text" name="location" class="form-control" value="<?=htmlspecialchars($a['location'])?>"></div>
          <div class="col-md-3"><input type="number" step="0.01" name="basic_tax" class="form-control" value="<?=$a['basic_tax']?>"></div>
          <div class="col-md-3"><input type="number" step="0.01" name="sef_tax" class="form-control" value="<?=$a['sef_tax']?>"></div>
          <div class="col-md-3"><input type="number" step="0.01" name="adjustments" class="form-control" value="<?=$a['adjustments']?>"></div>
          <div class="col-md-3">
            <select name="status" class="form-select">
              <option value="draft" <?=($a['status']=='draft'?'selected':'')?>>Draft</option>
              <option value="finalized" <?=($a['status']=='finalized'?'selected':'')?>>Finalized</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary" name="edit_assessment">Update Assessment</button>
          <a href="?delete_assessment_id=<?=$a['id']?>" class="btn btn-danger" onclick="return confirm('Delete this assessment?')">Delete</a>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endwhile; ?>
