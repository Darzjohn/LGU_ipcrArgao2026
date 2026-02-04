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

<!-- ✅ AUTO COMPUTATION SCRIPT -->
<script>
document.addEventListener('input', e => {
  const fields = ['gross_receipts', 'salaries', 'real_property_income', 'date_issued'];
  if (fields.includes(e.target.name)) computeCTCAdd();
});

function computeCTCAdd() {
  const gross = parseFloat(document.querySelector('[name="gross_receipts"]').value) || 0;
  const sal = parseFloat(document.querySelector('[name="salaries"]').value) || 0;
  const real = parseFloat(document.querySelector('[name="real_property_income"]').value) || 0;
  const basic = parseFloat(document.querySelector('#basic_tax_add').value) || 5;
  const issuedDate = new Date(document.querySelector('#date_issued_add').value);
  const currentDate = new Date();

  // Tax computation: PHP1.00 for every P1,000 excess of P1,000 per category
  const gr_tax_due = Math.floor(Math.max(0, gross - 1000) / 1000);
  const sal_tax_due = Math.floor(Math.max(0, sal - 1000) / 1000);
  const rpt_tax_due = Math.floor(Math.max(0, real - 1000) / 1000);

  // Display separate tax dues
  document.getElementById('gr_tax_due').textContent = gr_tax_due.toFixed(2);
  document.getElementById('sal_tax_due').textContent = sal_tax_due.toFixed(2);
  document.getElementById('rpt_tax_due').textContent = rpt_tax_due.toFixed(2);

  const additional_tax = gr_tax_due + sal_tax_due + rpt_tax_due;

  // Surcharge logic
  let surcharge = 0;
  const month = issuedDate.getMonth() + 1; // 1-based month
  if (month > 2) {
    // Start surcharge from March (6% + 2% for each succeeding month)
    surcharge = (0.06 + 0.02 * (month - 3)) * (basic + additional_tax);
  }

  const total_due = basic + additional_tax + surcharge;

  document.querySelector('#additional_tax_add').value = additional_tax.toFixed(2);
  document.querySelector('#surcharge_add').value = surcharge.toFixed(2);
  document.querySelector('#total_due_add').value = total_due.toFixed(2);
}
</script>
