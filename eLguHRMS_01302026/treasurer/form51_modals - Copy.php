<?php
// ✅ Fetch NGAS items
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

<!-- JavaScript for Dynamic Rows -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('form51ItemsTable').getElementsByTagName('tbody')[0];

    // Add row
    document.getElementById('addRowBtn').addEventListener('click', () => {
        const newRow = table.rows[0].cloneNode(true);
        newRow.querySelectorAll('input, select').forEach(el => {
            if(el.tagName==='INPUT') el.value = '';
            if(el.tagName==='SELECT') el.selectedIndex = 0;
        });
        newRow.querySelector('.ngas-code').textContent = '';
        table.appendChild(newRow);
    });

    // Remove row
    table.addEventListener('click', function(e) {
        if(e.target.classList.contains('removeRowBtn')) {
            if(table.rows.length > 1) e.target.closest('tr').remove();
        }
    });

    // On select change
    table.addEventListener('change', function(e) {
        if(e.target.classList.contains('ngas-select')) {
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
});
</script>
