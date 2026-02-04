<?php
// Existing buildFilterWhere() and paginate() ...

function renderFilterForm($current = []) {
    $search    = htmlspecialchars($current['search']    ?? '');
    $barangay  = htmlspecialchars($current['barangay']  ?? '');
    $location  = htmlspecialchars($current['location']  ?? '');
    $owner     = htmlspecialchars($current['owner']     ?? '');
    $td_no     = htmlspecialchars($current['td_no']     ?? '');
    $status    = htmlspecialchars($current['status']    ?? '');
    ?>
    <form method="get" class="card card-body mb-3">
      <div class="row g-2">
        <div class="col-md-2">
          <input type="text" name="search" value="<?=$search?>" class="form-control" placeholder="🔎 Global search">
        </div>
        <div class="col-md-2">
          <input type="text" name="barangay" value="<?=$barangay?>" class="form-control" placeholder="Barangay">
        </div>
        <div class="col-md-2">
          <input type="text" name="location" value="<?=$location?>" class="form-control" placeholder="Location">
        </div>
        <div class="col-md-2">
          <input type="text" name="owner" value="<?=$owner?>" class="form-control" placeholder="Owner">
        </div>
        <div class="col-md-2">
          <input type="text" name="td_no" value="<?=$td_no?>" class="form-control" placeholder="TD No">
        </div>
        <div class="col-md-2">
          <select name="status" class="form-select">
            <option value="">--Status--</option>
            <option value="unpaid" <?=$status==='unpaid'?'selected':''?>>Unpaid</option>
            <option value="paid" <?=$status==='paid'?'selected':''?>>Paid</option>
          </select>
        </div>
      </div>
      <div class="row mt-2">
        <div class="col-md-12 text-end">
          <button type="submit" class="btn btn-primary btn-sm">Apply Filters</button>
          <a href="<?=basename($_SERVER['PHP_SELF'])?>" class="btn btn-secondary btn-sm">Reset</a>
        </div>
      </div>
    </form>
    <?php
}
