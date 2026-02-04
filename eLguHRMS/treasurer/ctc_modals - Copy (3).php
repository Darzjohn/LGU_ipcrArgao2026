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
            <input name="date_issued" type="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
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

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Address</label>
            <input name="address" type="text" class="form-control">
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label">Citizenship</label>
            <input name="citizenship" type="text" class="form-control">
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label">Place of Birth</label>
            <input name="place_of_birth" type="text" class="form-control">
          </div>
        </div>

        <div class="row">
          <div class="col-md-4 mb-3">
            <label class="form-label">Civil Status</label>
            <select name="civil_status" class="form-select">
              <option>Single</option>
              <option>Married</option>
              <option>Widow/Widower</option>
              <option>Divorced</option>
            </select>
          </div>
        </div>

        <div class="row">
          <div class="col-md-3 mb-3">
            <label class="form-label">Gross Receipts</label>
            <input name="gross_receipts" type="number" step="0.01" class="form-control" value="0.00">
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label">GR Tax Due</label>
            <input id="gr_tax_due_add" name="gr_tax_due" type="number" step="0.01" class="form-control" value="0.00" readonly>
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label">Salaries</label>
            <input name="salaries" type="number" step="0.01" class="form-control" value="0.00">
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label">SAL Tax Due</label>
            <input id="sal_tax_due_add" name="sal_tax_due" type="number" step="0.01" class="form-control" value="0.00" readonly>
          </div>
        </div>

        <div class="row">
          <div class="col-md-3 mb-3">
            <label class="form-label">Real Property Income</label>
            <input name="real_property_income" type="number" step="0.01" class="form-control" value="0.00">
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label">RPT Tax Due</label>
            <input id="rpt_tax_due_add" name="rpt_tax_due" type="number" step="0.01" class="form-control" value="0.00" readonly>
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label">Basic Tax</label>
            <input id="basic_tax_add" name="basic_tax" type="number" step="0.01" class="form-control" value="5.00" readonly>
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label">Additional Tax</label>
            <input id="additional_tax_add" name="additional_tax" type="number" step="0.01" class="form-control" value="0.00" readonly>
          </div>
        </div>

        <div class="row">
          <div class="col-md-4 mb-3">
            <label class="form-label">Surcharge</label>
            <input id="surcharge_add" name="surcharge" type="number" step="0.01" class="form-control" value="0.00" readonly>
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label">Total Due</label>
            <input id="total_due_add" name="total_due" type="number" step="0.01" class="form-control" value="5.00" readonly>
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label">Place of Issue</label>
            <input name="place_of_issue" type="text" class="form-control" value="Municipal Treasurer’s Office">
          </div>
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
function computeCTCAdd() {
  const gross = parseFloat(document.querySelector('[name="gross_receipts"]').value) || 0;
  const sal = parseFloat(document.querySelector('[name="salaries"]').value) || 0;
  const real = parseFloat(document.querySelector('[name="real_property_income"]').value) || 0;
  const basic = parseFloat(document.querySelector('#basic_tax_add').value) || 5;
  const issueDate = new Date(document.querySelector('[name="date_issued"]').value);

  // ✅ Separate tax computations
  const gr_tax = gross * 0.01;
  const sal_tax = sal * 0.01;
  const rpt_tax = real * 0.01;

  const additional_tax = gr_tax + sal_tax + rpt_tax;

  // ✅ Compute surcharge
  let surcharge = 0;
  const month = issueDate.getMonth() + 1; // JS months are 0-based
  if (month > 2) {
    const months_late = month - 2;
    surcharge = additional_tax * (0.06 + (months_late - 1) * 0.02);
  }

  const total_due = basic + additional_tax + surcharge;

  document.querySelector('#gr_tax_due_add').value = gr_tax.toFixed(2);
  document.querySelector('#sal_tax_due_add').value = sal_tax.toFixed(2);
  document.querySelector('#rpt_tax_due_add').value = rpt_tax.toFixed(2);
  document.querySelector('#additional_tax_add').value = additional_tax.toFixed(2);
  document.querySelector('#surcharge_add').value = surcharge.toFixed(2);
  document.querySelector('#total_due_add').value = total_due.toFixed(2);
}

// Attach real-time computation
document.addEventListener('input', e => {
  if (['gross_receipts','salaries','real_property_income','date_issued'].includes(e.target.name))
    computeCTCAdd();
});
</script>
