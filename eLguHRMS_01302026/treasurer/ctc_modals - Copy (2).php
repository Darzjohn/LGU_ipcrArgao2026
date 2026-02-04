<!-- ✅ ADD CTC MODAL -->
<div class="modal fade" id="addCTCModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <form method="post" class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">Add New CTC</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="row">
          <div class="col-md-4 mb-3">
            <label class="form-label">CTC No</label>
            <input name="ctc_no" type="text" class="form-control" required>
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label">Year</label>
            <input name="year" type="number" class="form-control" value="<?= date('Y') ?>" required>
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label">Date Issued</label>
            <input id="date_issued_add" name="date_issued" type="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
          </div>
        </div>

        <div class="row">
          <div class="col-md-4 mb-3">
            <label class="form-label">Surname</label>
            <input name="surname" type="text" class="form-control" required>
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label">Firstname</label>
            <input name="firstname" type="text" class="form-control">
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label">Middlename</label>
            <input name="middlename" type="text" class="form-control">
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Address</label>
          <textarea name="address" class="form-control"></textarea>
        </div>

        <div class="row">
          <div class="col-md-4 mb-3">
            <label class="form-label">Citizenship</label>
            <input name="citizenship" type="text" class="form-control">
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label">Place of Birth</label>
            <input name="place_of_birth" type="text" class="form-control">
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label">Civil Status</label>
            <select name="civil_status" class="form-select">
              <option value="Single">Single</option>
              <option value="Married">Married</option>
              <option value="Widow/Widower">Widow/Widower</option>
              <option value="Divorced">Divorced</option>
            </select>
          </div>
        </div>

        <div class="row">
          <div class="col-md-4 mb-3">
            <label class="form-label">Gross Receipts</label>
            <input name="gross_receipts" type="number" step="0.01" class="form-control" value="0.00">
            <small class="text-muted">GR Tax Due: <span id="gr_tax_due">0.00</span></small>
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label">Salaries</label>
            <input name="salaries" type="number" step="0.01" class="form-control" value="0.00">
            <small class="text-muted">SAL Tax Due: <span id="sal_tax_due">0.00</span></small>
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label">Real Property Income</label>
            <input name="real_property_income" type="number" step="0.01" class="form-control" value="0.00">
            <small class="text-muted">RPT Tax Due: <span id="rpt_tax_due">0.00</span></small>
          </div>
        </div>

        <div class="row">
          <div class="col-md-3 mb-3">
            <label class="form-label">Basic Tax</label>
            <input id="basic_tax_add" name="basic_tax" type="number" step="0.01" class="form-control" value="5.00" readonly>
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label">Additional Tax</label>
            <input id="additional_tax_add" name="additional_tax" type="number" step="0.01" class="form-control" value="0.00" readonly>
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label">Surcharge</label>
            <input id="surcharge_add" name="surcharge" type="number" step="0.01" class="form-control" value="0.00" readonly>
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label">Total Due</label>
            <input id="total_due_add" name="total_due" type="number" step="0.01" class="form-control" value="5.00" readonly>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Place of Issue</label>
          <input name="place_of_issue" type="text" class="form-control" value="Municipal Treasurer’s Office">
        </div>
      </div>

      <div class="modal-footer">
        <button type="submit" name="add_ctc" class="btn btn-success">Add CTC</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- ✅ EDIT CTC MODAL -->
<?php foreach ($ctc_records as $r): ?>
<div class="modal fade" id="editModal<?= $r['id'] ?>" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <form method="post" class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title">Edit CTC (<?= htmlspecialchars($r['ctc_no']) ?>)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" name="id" value="<?= $r['id'] ?>">

        <div class="row">
          <div class="col-md-4 mb-3">
            <label class="form-label">CTC No</label>
            <input name="ctc_no" type="text" class="form-control" value="<?= htmlspecialchars($r['ctc_no']) ?>" required>
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label">Year</label>
            <input name="year" type="number" class="form-control" value="<?= htmlspecialchars($r['year']) ?>" required>
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label">Date Issued</label>
            <input id="date_issued_<?= $r['id'] ?>" name="date_issued" type="date" class="form-control" value="<?= htmlspecialchars($r['date_issued']) ?>" required>
          </div>
        </div>

        <div class="row">
          <div class="col-md-4 mb-3"><label>Surname</label>
            <input name="surname" type="text" class="form-control" value="<?= htmlspecialchars($r['surname']) ?>" required>
          </div>
          <div class="col-md-4 mb-3"><label>Firstname</label>
            <input name="firstname" type="text" class="form-control" value="<?= htmlspecialchars($r['firstname']) ?>">
          </div>
          <div class="col-md-4 mb-3"><label>Middlename</label>
            <input name="middlename" type="text" class="form-control" value="<?= htmlspecialchars($r['middlename']) ?>">
          </div>
        </div>

        <div class="mb-3">
          <label>Address</label>
          <textarea name="address" class="form-control"><?= htmlspecialchars($r['address']) ?></textarea>
        </div>

        <div class="row">
          <div class="col-md-4 mb-3"><label>Citizenship</label>
            <input name="citizenship" type="text" class="form-control" value="<?= htmlspecialchars($r['citizenship']) ?>">
          </div>
          <div class="col-md-4 mb-3"><label>Place of Birth</label>
            <input name="place_of_birth" type="text" class="form-control" value="<?= htmlspecialchars($r['place_of_birth']) ?>">
          </div>
          <div class="col-md-4 mb-3"><label>Civil Status</label>
            <select name="civil_status" class="form-select">
              <?php foreach (['Single','Married','Widow/Widower','Divorced'] as $opt): ?>
                <option value="<?= $opt ?>" <?= $r['civil_status'] == $opt ? 'selected' : '' ?>><?= $opt ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="row">
          <div class="col-md-4 mb-3">
            <label>Gross Receipts</label>
            <input id="gross_<?= $r['id'] ?>" name="gross_receipts" type="number" step="0.01" class="form-control" value="<?= htmlspecialchars($r['gross_receipts']) ?>">
          </div>
          <div class="col-md-4 mb-3">
            <label>Salaries</label>
            <input id="sal_<?= $r['id'] ?>" name="salaries" type="number" step="0.01" class="form-control" value="<?= htmlspecialchars($r['salaries']) ?>">
          </div>
          <div class="col-md-4 mb-3">
            <label>Real Property Income</label>
            <input id="rpt_<?= $r['id'] ?>" name="real_property_income" type="number" step="0.01" class="form-control" value="<?= htmlspecialchars($r['real_property_income']) ?>">
          </div>
        </div>

        <div class="row">
          <div class="col-md-3 mb-3"><label>Basic Tax</label>
            <input id="basic_<?= $r['id'] ?>" name="basic_tax" type="number" class="form-control" value="<?= htmlspecialchars($r['basic_tax']) ?>" readonly>
          </div>
          <div class="col-md-3 mb-3"><label>Additional Tax</label>
            <input id="add_<?= $r['id'] ?>" name="additional_tax" type="number" class="form-control" value="<?= htmlspecialchars($r['additional_tax']) ?>" readonly>
          </div>
          <div class="col-md-3 mb-3"><label>Surcharge</label>
            <input id="sur_<?= $r['id'] ?>" name="surcharge" type="number" class="form-control" value="<?= htmlspecialchars($r['surcharge']) ?>" readonly>
          </div>
          <div class="col-md-3 mb-3"><label>Total Due</label>
            <input id="total_<?= $r['id'] ?>" name="total_due" type="number" class="form-control" value="<?= htmlspecialchars($r['total_due']) ?>" readonly>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="submit" name="update_ctc" class="btn btn-warning">Update</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('input', e => {
  if (e.target.closest('[id^="editModal"]')) {
    const modal = e.target.closest('.modal');
    const id = modal.id.replace('editModal', '');
    computeEditCTC(id);
  }
});

function computeEditCTC(id) {
  const gross = parseFloat(document.querySelector(`#gross_${id}`).value) || 0;
  const sal = parseFloat(document.querySelector(`#sal_${id}`).value) || 0;
  const real = parseFloat(document.querySelector(`#rpt_${id}`).value) || 0;
  const basic = parseFloat(document.querySelector(`#basic_${id}`).value) || 5;
  const issuedDate = new Date(document.querySelector(`#date_issued_${id}`).value);

  const gr_tax = Math.floor(Math.max(0, gross - 1000) / 1000);
  const sal_tax = Math.floor(Math.max(0, sal - 1000) / 1000);
  const rpt_tax = Math.floor(Math.max(0, real - 1000) / 1000);
  const add_tax = gr_tax + sal_tax + rpt_tax;

  let surcharge = 0;
  const month = issuedDate.getMonth() + 1;
  if (month > 2) {
    surcharge = (0.06 + 0.02 * (month - 3)) * (basic + add_tax);
  }

  const total = basic + add_tax + surcharge;

  document.querySelector(`#add_${id}`).value = add_tax.toFixed(2);
  document.querySelector(`#sur_${id}`).value = surcharge.toFixed(2);
  document.querySelector(`#total_${id}`).value = total.toFixed(2);
}
</script>
<?php endforeach; ?>
