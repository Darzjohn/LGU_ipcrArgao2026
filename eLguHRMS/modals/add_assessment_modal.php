<!-- Add Assessment Modal -->
<div class="modal fade" id="addAssessmentModal<?=$row['id']?>" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="post">
        <input type="hidden" name="property_id" value="<?=$row['id']?>">
        <div class="modal-header">
          <h5 class="modal-title">Add Assessment for <?=$row['td_no']?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body row g-3">
          <div class="col-md-3"><input type="number" name="tax_year" class="form-control" placeholder="Tax Year" required></div>
          <div class="col-md-3"><input type="number" step="0.01" name="assessed_value" class="form-control" placeholder="Assessed Value" required></div>
          <div class="col-md-3"><input type="text" name="barangay" class="form-control" placeholder="Barangay"></div>
          <div class="col-md-3"><input type="text" name="location" class="form-control" placeholder="Location"></div>
          <div class="col-md-3"><input type="number" step="0.01" name="basic_tax" class="form-control" placeholder="Basic Tax"></div>
          <div class="col-md-3"><input type="number" step="0.01" name="sef_tax" class="form-control" placeholder="SEF Tax"></div>
          <div class="col-md-3"><input type="number" step="0.01" name="adjustments" class="form-control" placeholder="Adjustments"></div>
          <div class="col-md-3">
            <select name="status" class="form-select">
              <option value="draft">Draft</option>
              <option value="finalized">Finalized</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-success" name="add_assessment">Add Assessment</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>
