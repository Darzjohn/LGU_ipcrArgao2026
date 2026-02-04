<?php
// ✅ Fetch NGAS items for dropdowns
$ngas_items = [];
$ngas_res = $mysqli->query("SELECT id, ngas_code, nature_of_collection, set_fix_amount 
                            FROM ngas_settings 
                            WHERE status='enable' 
                            ORDER BY ngas_code ASC");
while ($row = $ngas_res->fetch_assoc()) {
    $ngas_items[] = $row;
}
?>

<!-- Add Form51 Modal -->
<div class="modal fade" id="addForm51Modal" tabindex="-1" aria-labelledby="addForm51Label" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form method="POST" class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="addForm51Label">➕ New Form 51</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Basic Info -->
        <div class="row mb-2">
          <div class="col-md-4">
            <label>OR No</label>
            <input type="text" name="or_no" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label>Date Issued</label>
            <input type="date" name="date_issued" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label>Payor Name</label>
            <input type="text" name="payor_name" class="form-control" required>
          </div>
        </div>
        <div class="row mb-2">
          <div class="col-md-12">
            <label>Address</label>
            <input type="text" name="address" class="form-control">
          </div>
        </div>

        <!-- Multi-Row Payment Table -->
        <table class="table table-bordered" id="form51ItemsTable">
          <thead class="table-dark">
            <tr>
              <th>Collection Item</th>
              <th>NGAS Code</th>
              <th>Amount</th>
              <th><button type="button" class="btn btn-sm btn-success" id="addRowBtn">➕</button></th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>
                <select name="ngas_id[]" class="form-select ngas-select" required>
                  <option value="">--Select Item--</option>
                  <?php foreach($ngas_items as $n): ?>
                    <option value="<?= $n['id'] ?>" data-fixed="<?= $n['set_fix_amount'] ?>" data-code="<?= $n['ngas_code'] ?>">
                      <?= htmlspecialchars($n['nature_of_collection']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </td>
              <td class="ngas-code"></td>
              <td><input type="number" name="amount[]" class="form-control amount-input" step="0.01" required></td>
              <td><button type="button" class="btn btn-sm btn-danger removeRowBtn">🗑️</button></td>
            </tr>
          </tbody>
        </table>

      </div>
      <div class="modal-footer">
        <button type="submit" name="add_form51" class="btn btn-success">💾 Save Form 51</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Form51 Modal -->
<?php foreach($form51_records as $r): 
    // ✅ Fetch form51_items for this record
    $items = [];
    $stmt_items = $mysqli->prepare("SELECT fi.id, fi.ngas_id, fi.amount, ns.nature_of_collection, ns.ngas_code, ns.set_fix_amount 
                                    FROM form51_items fi
                                    LEFT JOIN ngas_settings ns ON fi.ngas_id = ns.id
                                    WHERE fi.form51_id=?");
    $stmt_items->bind_param('i', $r['id']);
    $stmt_items->execute();
    $res_items = $stmt_items->get_result();
    while ($row_item = $res_items->fetch_assoc()) $items[] = $row_item;
?>
<div class="modal fade" id="editModal<?= $r['id'] ?>" tabindex="-1" aria-labelledby="editForm51Label<?= $r['id'] ?>" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form method="POST" class="modal-content">
      <input type="hidden" name="form51_id" value="<?= $r['id'] ?>">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title" id="editForm51Label<?= $r['id'] ?>">✏️ Edit Form 51</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Basic Info -->
        <div class="row mb-2">
          <div class="col-md-4">
            <label>OR No</label>
            <input type="text" name="or_no" class="form-control" value="<?= htmlspecialchars($r['or_no']) ?>" required>
          </div>
          <div class="col-md-4">
            <label>Date Issued</label>
            <input type="date" name="date_issued" class="form-control" value="<?= $r['date_issued'] ?>" required>
          </div>
          <div class="col-md-4">
            <label>Payor Name</label>
            <input type="text" name="payor_name" class="form-control" value="<?= htmlspecialchars($r['payor_name']) ?>" required>
          </div>
        </div>
        <div class="row mb-2">
          <div class="col-md-12">
            <label>Address</label>
            <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($r['address']) ?>">
          </div>
        </div>

        <!-- Multi-Row Payment Table -->
        <table class="table table-bordered editForm51ItemsTable">
          <thead class="table-dark">
            <tr>
              <th>Collection Item</th>
              <th>NGAS Code</th>
              <th>Amount</th>
              <th><button type="button" class="btn btn-sm btn-success addEditRowBtn">➕</button></th>
            </tr>
          </thead>
          <tbody>
            <?php if(!empty($items)): foreach($items as $item): ?>
            <tr>
              <td>
                <select name="ngas_id[]" class="form-select ngas-select" required>
                  <option value="">--Select Item--</option>
                  <?php foreach($ngas_items as $n): ?>
                    <option value="<?= $n['id'] ?>" data-fixed="<?= $n['set_fix_amount'] ?>" data-code="<?= $n['ngas_code'] ?>" <?= $item['ngas_id']==$n['id']?'selected':'' ?>>
                      <?= htmlspecialchars($n['nature_of_collection']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </td>
              <td class="ngas-code"><?= htmlspecialchars($item['ngas_code']) ?></td>
              <td>
                <input type="number" name="amount[]" class="form-control amount-input" step="0.01" value="<?= $item['amount'] ?>" <?= $item['set_fix_amount']>0?'readonly':'' ?> required>
              </td>
              <td><button type="button" class="btn btn-sm btn-danger removeRowBtn">🗑️</button></td>
            </tr>
            <?php endforeach; else: ?>
            <tr>
              <td>
                <select name="ngas_id[]" class="form-select ngas-select" required>
                  <option value="">--Select Item--</option>
                  <?php foreach($ngas_items as $n): ?>
                    <option value="<?= $n['id'] ?>" data-fixed="<?= $n['set_fix_amount'] ?>" data-code="<?= $n['ngas_code'] ?>">
                      <?= htmlspecialchars($n['nature_of_collection']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </td>
              <td class="ngas-code"></td>
              <td><input type="number" name="amount[]" class="form-control amount-input" step="0.01" required></td>
              <td><button type="button" class="btn btn-sm btn-danger removeRowBtn">🗑️</button></td>
            </tr>
            <?php endif; ?>
          </tbody>
        </table>

      </div>
      <div class="modal-footer">
        <button type="submit" name="edit_form51" class="btn btn-warning">💾 Update Form 51</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </form>
  </div>
</div>
<?php endforeach; ?>

<!-- JS for Add/Edit dynamic rows -->
<script>
document.addEventListener('DOMContentLoaded', function() {

    // Common function to handle row actions
    function setupTable(table) {
        // Add row
        table.querySelectorAll('.addEditRowBtn, #addRowBtn').forEach(btn => {
            btn.addEventListener('click', () => {
                const tbody = table.querySelector('tbody');
                const newRow = tbody.rows[0].cloneNode(true);
                newRow.querySelectorAll('input, select').forEach(el => {
                    if(el.tagName==='INPUT') el.value = '';
                    if(el.tagName==='SELECT') el.selectedIndex = 0;
                });
                newRow.querySelector('.ngas-code').textContent = '';
                tbody.appendChild(newRow);
                setupTable(table); // Re-attach events
            });
        });

        // Remove row
        table.querySelector('tbody').addEventListener('click', function(e){
            if(e.target.classList.contains('removeRowBtn')){
                if(table.querySelectorAll('tbody tr').length > 1){
                    e.target.closest('tr').remove();
                }
            }
        });

        // On select change
        table.querySelector('tbody').addEventListener('change', function(e){
            if(e.target.classList.contains('ngas-select')){
                const selected = e.target.selectedOptions[0];
                const row = e.target.closest('tr');
                const codeCell = row.querySelector('.ngas-code');
                const amountInput = row.querySelector('.amount-input');

                codeCell.textContent = selected.dataset.code || '';
                if(parseFloat(selected.dataset.fixed) > 0){
                    amountInput.value = parseFloat(selected.dataset.fixed).toFixed(2);
                    amountInput.readOnly = true;
                } else {
                    amountInput.value = '';
                    amountInput.readOnly = false;
                }
            }
        });
    }

    // Setup all tables
    setupTable(document.getElementById('form51ItemsTable'));
    document.querySelectorAll('.editForm51ItemsTable').forEach(table => setupTable(table));

});
</script>
