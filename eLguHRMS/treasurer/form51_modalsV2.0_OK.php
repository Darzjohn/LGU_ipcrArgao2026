<?php
// NGAS items are already fetched in form51_list.php ($ngas_items)
// form51_records are also available for edit modals
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

        <!-- Payment Mode -->
        <div class="row mb-2">
          <div class="col-md-3">
            <label>Payment Mode</label>
            <select name="payment_mode" class="form-select" id="addPaymentMode" required>
              <option value="cash" selected>Cash</option>
              <option value="check">Check</option>
            </select>
          </div>
          <div class="col-md-3">
            <label>Total Cash Paid</label>
            <input type="number" name="total_cash_paid" class="form-control" id="addTotalCashPaid" step="0.01" value="0.00" required>
          </div>
          <div class="col-md-3">
            <label>Check Number</label>
            <input type="text" name="check_number" class="form-control" id="addCheckNumber" disabled>
          </div>
          <div class="col-md-3">
            <label>Bank Name</label>
            <input type="text" name="bank_name" class="form-control" id="addBankName" disabled>
          </div>
        </div>
        <div class="row mb-2">
          <div class="col-md-3">
            <label>Check Date</label>
            <input type="date" name="check_date" class="form-control" id="addCheckDate" disabled>
          </div>
          <div class="col-md-3">
            <label>Check Amount</label>
            <input type="number" name="check_amount" class="form-control" id="addCheckAmount" step="0.01" disabled>
          </div>
        </div>

        <!-- Multi-Row Payment Items -->
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

        <!-- Grand Total -->
        <div class="row mb-2">
          <div class="col-md-3">
            <label>Grand Total</label>
            <input type="number" name="grand_total" class="form-control" id="addGrandTotal" step="0.01" value="0.00" readonly>
          </div>
        </div>

      </div>
      <div class="modal-footer">
        <button type="submit" name="add_form51" class="btn btn-success">💾 Save Form 51</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Form51 Modals -->
<?php foreach($form51_records as $r):
    $items = [];
    $stmt_items = $mysqli->prepare("SELECT fi.id, fi.ngas_id, fi.amount, ns.nature_of_collection, ns.ngas_code, ns.set_fix_amount 
                                    FROM form51_items fi
                                    LEFT JOIN ngas_settings ns ON fi.ngas_id = ns.id
                                    WHERE fi.form51_id=?");
    $stmt_items->bind_param('i', $r['id']);
    $stmt_items->execute();
    $res_items = $stmt_items->get_result();
    while($row_item = $res_items->fetch_assoc()) $items[] = $row_item;
?>
<div class="modal fade" id="editModal<?= $r['id'] ?>" tabindex="-1" aria-labelledby="editForm51Label<?= $r['id'] ?>" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form method="POST" class="modal-content">
      <input type="hidden" name="form51_id" value="<?= $r['id'] ?>">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title" id="editForm51Label<?= $r['id'] ?>">✏️ Edit Form 51</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">

        <!-- Basic Info -->
        <div class="row mb-2">
          <div class="col-md-4"><label>OR No</label><input type="text" name="or_no" class="form-control" value="<?= htmlspecialchars($r['or_no']) ?>" required></div>
          <div class="col-md-4"><label>Date Issued</label><input type="date" name="date_issued" class="form-control" value="<?= $r['date_issued'] ?>" required></div>
          <div class="col-md-4"><label>Payor Name</label><input type="text" name="payor_name" class="form-control" value="<?= htmlspecialchars($r['payor_name']) ?>" required></div>
        </div>
        <div class="row mb-2"><div class="col-md-12"><label>Address</label><input type="text" name="address" class="form-control" value="<?= htmlspecialchars($r['address']) ?>"></div></div>

        <!-- Payment Mode -->
        <div class="row mb-2">
          <div class="col-md-3">
            <label>Payment Mode</label>
            <select name="payment_mode" class="form-select editPaymentMode" required>
              <option value="cash" <?= $r['payment_mode']=='cash'?'selected':''?>>Cash</option>
              <option value="check" <?= $r['payment_mode']=='check'?'selected':''?>>Check</option>
            </select>
          </div>
          <div class="col-md-3">
            <label>Total Cash Paid</label>
            <input type="number" name="total_cash_paid" class="form-control editTotalCashPaid" step="0.01" value="<?= $r['total_cash_paid'] ?>">
          </div>
          <div class="col-md-3">
            <label>Check Number</label>
            <input type="text" name="check_number" class="form-control editCheckNumber" value="<?= $r['check_number'] ?>">
          </div>
          <div class="col-md-3">
            <label>Bank Name</label>
            <input type="text" name="bank_name" class="form-control editBankName" value="<?= $r['bank_name'] ?>">
          </div>
        </div>
        <div class="row mb-2">
          <div class="col-md-3"><label>Check Date</label><input type="date" name="check_date" class="form-control editCheckDate" value="<?= $r['check_date'] ?>"></div>
          <div class="col-md-3"><label>Check Amount</label><input type="number" name="check_amount" class="form-control editCheckAmount" step="0.01" value="<?= $r['check_amount'] ?>"></div>
        </div>

        <!-- Multi-row payment items -->
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
                    <option value="<?= $n['id'] ?>" data-fixed="<?= $n['set_fix_amount'] ?>" data-code="<?= $n['ngas_code'] ?>" <?= $item['ngas_id']==$n['id']?'selected':''?>>
                      <?= htmlspecialchars($n['nature_of_collection']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </td>
              <td class="ngas-code"><?= htmlspecialchars($item['ngas_code']) ?></td>
              <td><input type="number" name="amount[]" class="form-control amount-input" step="0.01" value="<?= $item['amount'] ?>" <?= $item['set_fix_amount']>0?'readonly':'' ?> required></td>
              <td><button type="button" class="btn btn-sm btn-danger removeRowBtn">🗑️</button></td>
            </tr>
            <?php endforeach; else: ?>
            <tr>
              <td><select name="ngas_id[]" class="form-select ngas-select" required>
                <option value="">--Select Item--</option>
                <?php foreach($ngas_items as $n): ?>
                  <option value="<?= $n['id'] ?>" data-fixed="<?= $n['set_fix_amount'] ?>" data-code="<?= $n['ngas_code'] ?>"><?= htmlspecialchars($n['nature_of_collection']) ?></option>
                <?php endforeach; ?>
              </select></td>
              <td class="ngas-code"></td>
              <td><input type="number" name="amount[]" class="form-control amount-input" step="0.01" required></td>
              <td><button type="button" class="btn btn-sm btn-danger removeRowBtn">🗑️</button></td>
            </tr>
            <?php endif; ?>
          </tbody>
        </table>

        <!-- Grand Total -->
        <div class="row mb-2">
          <div class="col-md-3">
            <label>Grand Total</label>
            <input type="number" name="grand_total" class="form-control editGrandTotal" step="0.01" value="<?= $r['grand_total'] ?>" readonly>
          </div>
        </div>

      </div>
      <div class="modal-footer">
        <button type="submit" name="edit_form51" class="btn btn-warning">💾 Update Form 51</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </form>
  </div>
</div>
<?php endforeach; ?>

<!-- JS for dynamic rows and grand total calculation -->
<script>
document.addEventListener('DOMContentLoaded', function(){
  function setupTable(table){
    table.querySelectorAll('.addEditRowBtn, #addRowBtn').forEach(btn=>{
      btn.addEventListener('click', ()=>{
        const tbody = table.querySelector('tbody');
        const newRow = tbody.rows[0].cloneNode(true);
        newRow.querySelectorAll('input, select').forEach(el=>{
          if(el.tagName==='INPUT') el.value='';
          if(el.tagName==='SELECT') el.selectedIndex=0;
        });
        newRow.querySelector('.ngas-code').textContent='';
        tbody.appendChild(newRow);
        setupTable(table);
        updateGrandTotal();
      });
    });

    table.querySelector('tbody').addEventListener('click', function(e){
      if(e.target.classList.contains('removeRowBtn')){
        if(table.querySelectorAll('tbody tr').length>1) e.target.closest('tr').remove();
        updateGrandTotal();
      }
    });

    table.querySelector('tbody').addEventListener('change', function(e){
      if(e.target.classList.contains('ngas-select')){
        const selected = e.target.selectedOptions[0];
        const row = e.target.closest('tr');
        const codeCell = row.querySelector('.ngas-code');
        const amountInput = row.querySelector('.amount-input');
        codeCell.textContent = selected.dataset.code || '';
        if(parseFloat(selected.dataset.fixed)>0){
          amountInput.value=parseFloat(selected.dataset.fixed).toFixed(2);
          amountInput.readOnly=true;
        } else {
          amountInput.value='';
          amountInput.readOnly=false;
        }
        updateGrandTotal();
      }
    });

    table.querySelector('tbody').addEventListener('input', updateGrandTotal);
  }

  function updateGrandTotal(){
    document.querySelectorAll('#form51ItemsTable, .editForm51ItemsTable').forEach(tbl=>{
      let total=0;
      tbl.querySelectorAll('input.amount-input').forEach(inp=> total += parseFloat(inp.value)||0 );
      const grandInput = tbl.closest('form').querySelector('input[name="grand_total"]');
      if(grandInput) grandInput.value = total.toFixed(2);
      // update cash/check based on payment mode
      const form = tbl.closest('form');
      const mode = form.querySelector('select[name="payment_mode"]').value;
      if(mode==='cash'){
        form.querySelector('input[name="total_cash_paid"]').value=total.toFixed(2);
        form.querySelector('input[name="check_number"]').value='';
        form.querySelector('input[name="bank_name"]').value='';
        form.querySelector('input[name="check_date"]').value='';
        form.querySelector('input[name="check_amount"]').value='';
      } else {
        form.querySelector('input[name="check_amount"]').value=total.toFixed(2);
      }
    });
  }

  setupTable(document.getElementById('form51ItemsTable'));
  document.querySelectorAll('.editForm51ItemsTable').forEach(tbl=>setupTable(tbl));

  // Payment mode change
  document.querySelectorAll('select[name="payment_mode"]').forEach(sel=>{
    sel.addEventListener('change', function(){
      const form = this.closest('form');
      const mode=this.value;
      if(mode==='cash'){
        form.querySelector('input[name="total_cash_paid"]').disabled=false;
        ['check_number','bank_name','check_date','check_amount'].forEach(id=>form.querySelector('input[name="'+id+'"]').disabled=true);
      } else {
        form.querySelector('input[name="total_cash_paid"]').disabled=true;
        ['check_number','bank_name','check_date','check_amount'].forEach(id=>form.querySelector('input[name="'+id+'"]').disabled=false);
      }
    });
    sel.dispatchEvent(new Event('change')); // initialize state
  });
});
</script>
