<!-- ADD CTC MODAL -->
<div class="modal fade" id="addCTCModal" tabindex="-1" aria-labelledby="addCTCModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <form method="POST" id="addCTCForm">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">Add New Community Tax Certificate</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-3">
              <label>CTC No.</label>
              <input type="text" name="ctc_no" class="form-control" required>
            </div>
            <div class="col-md-2">
              <label>Year</label>
              <input type="number" name="year" class="form-control" value="<?= date('Y') ?>" required>
            </div>
            <div class="col-md-3">
              <label>Date Issued</label>
              <input type="date" id="add_date_issued" name="date_issued" class="form-control tax-input" required>
            </div>
            <div class="col-md-4">
              <label>Place of Issue</label>
              <input type="text" name="place_of_issue" class="form-control" value="ARGAO, CEBU" required>
            </div>

            <div class="col-md-3">
              <label>Surname</label>
              <input type="text" name="surname" class="form-control" required>
            </div>
            <div class="col-md-3">
              <label>First Name</label>
              <input type="text" name="firstname" class="form-control" required>
            </div>
            <div class="col-md-3">
              <label>Middle Name</label>
              <input type="text" name="middlename" class="form-control">
            </div>
            <div class="col-md-3">
              <label>Sex</label>
              <select name="sex" class="form-select" required>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
              </select>
            </div>

            <div class="col-md-3">
              <label>Date of Birth</label>
              <input type="date" name="date_of_birth" class="form-control" value="">
            </div>
            <div class="col-md-2">
              <label>Weight (kg)</label>
              <input type="number" step="0.1" name="weight" class="form-control" value="">
            </div>
            <div class="col-md-2">
              <label>Height (cm)</label>
              <input type="number" step="0.1" name="height" class="form-control" value="">
            </div>
            <div class="col-md-5">
              <label>Profession</label>
              <input type="text" name="profession" class="form-control" value="">
            </div>

            <!-- Address & Other fields -->
            <div class="col-md-6">
              <label>Address</label>
              <input type="text" name="address" class="form-control" required>
            </div>
            <div class="col-md-3">
              <label>Citizenship</label>
              <input type="text" name="citizenship" class="form-control" required>
            </div>
            <div class="col-md-3">
              <label>Place of Birth</label>
              <input type="text" name="place_of_birth" class="form-control">
            </div>
            <div class="col-md-3">
              <label>Civil Status</label>
              <select name="civil_status" class="form-select">
                <option value="">-- Select --</option>
                <option>Single</option>
                <option>Married</option>
                <option>Widowed</option>
                <option>Separated</option>
              </select>
            </div>

            <!-- Income & Tax -->
            <div class="col-12"><hr><h6 class="text-primary">Income Details</h6></div>
            <div class="col-md-3"><label>Gross Receipts</label><input type="number" step="0.01" id="add_gross" name="gross_receipts" class="form-control tax-input"></div>
            <div class="col-md-3"><label>Salaries</label><input type="number" step="0.01" id="add_sal" name="salaries" class="form-control tax-input"></div>
            <div class="col-md-3"><label>Real Property Income</label><input type="number" step="0.01" id="add_rpt" name="real_property_income" class="form-control tax-input"></div>

            <div class="col-12"><hr><h6 class="text-primary">Tax Computation</h6></div>
            <div class="col-md-3"><label>Gross Receipts Tax</label><input type="text" id="add_gr_tax" name="gr_tax_due" class="form-control" readonly></div>
            <div class="col-md-3"><label>Salaries Tax</label><input type="text" id="add_sal_tax" name="sal_tax_due" class="form-control" readonly></div>
            <div class="col-md-3"><label>Real Property Tax</label><input type="text" id="add_rpt_tax" name="rpt_tax_due" class="form-control" readonly></div>
            <div class="col-md-3"><label>Surcharge</label><input type="text" id="add_surcharge" name="surcharge" class="form-control" readonly></div>

            <div class="col-md-3"><label>Basic Tax</label><input type="number" step="0.01" id="add_basic" name="basic_tax" class="form-control tax-input" value="5.00"></div>
            <div class="col-md-3"><label>Additional Tax</label><input type="number" step="0.01" id="add_additional" name="additional_tax" class="form-control tax-input" value="0.00"></div>
            <div class="col-md-3"><label>Total Due</label><input type="text" id="add_total_due" name="total_due" class="form-control" readonly></div>
            <div class="col-md-6"><label>Amount in Words</label><input type="text" id="add_amount_words" class="form-control" readonly></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="add_ctc" class="btn btn-success">Save CTC</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>




<!-- EDIT CTC MODALS -->
<?php foreach ($ctc_records as $r): ?>
<div class="modal fade" id="editModal<?= $r['id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $r['id'] ?>" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="ctc_id" value="<?= $r['id'] ?>">
        <div class="modal-header bg-warning text-dark">
          <h5 class="modal-title">Edit CTC #<?= htmlspecialchars($r['ctc_no']) ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-3">
              <label>CTC No.</label>
              <input type="text" name="ctc_no" class="form-control" value="<?= htmlspecialchars($r['ctc_no']) ?>" required>
            </div>
            <div class="col-md-2">
              <label>Year</label>
              <input type="number" name="year" class="form-control" value="<?= htmlspecialchars($r['year']) ?>" required>
            </div>
            <div class="col-md-3">
              <label>Date Issued</label>
              <input type="date" id="edit_date_issued_<?= $r['id'] ?>" name="date_issued" class="form-control tax-input-edit" value="<?= htmlspecialchars($r['date_issued']) ?>" required>
            </div>
            <div class="col-md-4">
              <label>Place of Issue</label>
              <input type="text" name="place_of_issue" class="form-control" value="<?= htmlspecialchars($r['place_of_issue']) ?>" required>
            </div>

            <div class="col-md-3">
              <label>Surname</label>
              <input type="text" name="surname" class="form-control" value="<?= htmlspecialchars($r['surname']) ?>" required>
            </div>
            <div class="col-md-3">
              <label>First Name</label>
              <input type="text" name="firstname" class="form-control" value="<?= htmlspecialchars($r['firstname']) ?>" required>
            </div>
            <div class="col-md-3">
              <label>Middle Name</label>
              <input type="text" name="middlename" class="form-control" value="<?= htmlspecialchars($r['middlename']) ?>">
            </div>
            <div class="col-md-3">
              <label>Sex</label>
              <select name="sex" class="form-select" required>
                <option value="Male" <?= $r['sex'] == 'Male' ? 'selected' : '' ?>>Male</option>
                <option value="Female" <?= $r['sex'] == 'Female' ? 'selected' : '' ?>>Female</option>
              </select>
            </div>

            <div class="col-md-3">
  <label class="form-label">Date of Birth</label>
  <input type="date" name="date_of_birth" class="form-control" value="<?= htmlspecialchars($r['date_of_birth'] ?? '') ?>">
</div>
<div class="col-md-2">
  <label class="form-label">Weight (kg)</label>
  <input type="number" step="0.1" name="weight" class="form-control" value="<?= htmlspecialchars($r['weight'] ?? '') ?>">
</div>
<div class="col-md-2">
  <label class="form-label">Height (cm)</label>
  <input type="number" step="0.1" name="height" class="form-control" value="<?= htmlspecialchars($r['height'] ?? '') ?>">
</div>
<div class="col-md-5">
  <label class="form-label">Profession</label>
  <input type="text" name="profession" class="form-control" value="<?= htmlspecialchars($r['profession'] ?? '') ?>">
</div>


            <div class="col-md-6">
              <label>Address</label>
              <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($r['address']) ?>" required>
            </div>
            <div class="col-md-3">
              <label>Citizenship</label>
              <input type="text" name="citizenship" class="form-control" value="<?= htmlspecialchars($r['citizenship']) ?>" required>
            </div>
            <div class="col-md-3">
              <label>Place of Birth</label>
              <input type="text" name="place_of_birth" class="form-control" value="<?= htmlspecialchars($r['place_of_birth']) ?>">
            </div>
            <div class="col-md-3">
              <label>Civil Status</label>
              <select name="civil_status" class="form-select">
                <option value="Single" <?= $r['civil_status'] == 'Single' ? 'selected' : '' ?>>Single</option>
                <option value="Married" <?= $r['civil_status'] == 'Married' ? 'selected' : '' ?>>Married</option>
                <option value="Widowed" <?= $r['civil_status'] == 'Widowed' ? 'selected' : '' ?>>Widowed</option>
                <option value="Separated" <?= $r['civil_status'] == 'Separated' ? 'selected' : '' ?>>Separated</option>
              </select>
            </div>

            <div class="col-12"><hr><h6 class="text-primary">Income Details</h6></div>

            <div class="col-md-3">
              <label>Gross Receipts</label>
              <input type="number" step="0.01" id="edit_gross_<?= $r['id'] ?>" name="gross_receipts" class="form-control tax-input-edit" value="<?= htmlspecialchars($r['gross_receipts']) ?>">
            </div>
            <div class="col-md-3">
              <label>Salaries</label>
              <input type="number" step="0.01" id="edit_sal_<?= $r['id'] ?>" name="salaries" class="form-control tax-input-edit" value="<?= htmlspecialchars($r['salaries']) ?>">
            </div>
            <div class="col-md-3">
              <label>Real Property Income</label>
              <input type="number" step="0.01" id="edit_rpt_<?= $r['id'] ?>" name="real_property_income" class="form-control tax-input-edit" value="<?= htmlspecialchars($r['real_property_income']) ?>">
            </div>

            <div class="col-12"><hr><h6 class="text-primary">Tax Computation</h6></div>

            <div class="col-md-3">
              <label>Gross Receipts Tax</label>
              <input type="text" id="edit_gr_tax_<?= $r['id'] ?>" name="gr_tax_due" class="form-control" value="<?= htmlspecialchars($r['gr_tax_due']) ?>" readonly>
            </div>
            <div class="col-md-3">
              <label>Salaries Tax</label>
              <input type="text" id="edit_sal_tax_<?= $r['id'] ?>" name="sal_tax_due" class="form-control" value="<?= htmlspecialchars($r['sal_tax_due']) ?>" readonly>
            </div>
            <div class="col-md-3">
              <label>Real Property Tax</label>
              <input type="text" id="edit_rpt_tax_<?= $r['id'] ?>" name="rpt_tax_due" class="form-control" value="<?= htmlspecialchars($r['rpt_tax_due']) ?>" readonly>
            </div>
            <div class="col-md-3">
              <label>Surcharge</label>
              <input type="text" id="edit_surcharge_<?= $r['id'] ?>" name="surcharge" class="form-control" value="<?= htmlspecialchars($r['surcharge']) ?>" readonly>
            </div>

            <div class="col-md-3">
              <label>Basic Tax</label>
              <input type="number" step="0.01" id="edit_basic_<?= $r['id'] ?>" name="basic_tax" class="form-control tax-input-edit" value="<?= htmlspecialchars($r['basic_tax']) ?>">
            </div>
            <div class="col-md-3">
              <label>Additional Tax</label>
              <input type="number" step="0.01" id="edit_additional_<?= $r['id'] ?>" name="additional_tax" class="form-control tax-input-edit" value="<?= htmlspecialchars($r['additional_tax']) ?>">
            </div>
            <div class="col-md-3">
              <label>Total Due</label>
              <input type="text" id="edit_total_due_<?= $r['id'] ?>" name="total_due" class="form-control" value="<?= htmlspecialchars($r['total_due']) ?>" readonly>
            </div>
            <div class="col-md-6">
              <label>Amount in Words</label>
              <input type="text" id="edit_amount_words_<?= $r['id'] ?>" class="form-control" readonly>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" name="edit_ctc" class="btn btn-warning text-dark">Update</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endforeach; ?>

<script>
// ===== Number to Words Conversion (Fixed & Consistent) =====
function numberToWords(num) {
  const ones = ["", "One", "Two", "Three", "Four", "Five", "Six", "Seven", "Eight", "Nine"];
  const teens = ["Ten", "Eleven", "Twelve", "Thirteen", "Fourteen", "Fifteen", "Sixteen", "Seventeen", "Eighteen", "Nineteen"];
  const tens = ["", "", "Twenty", "Thirty", "Forty", "Fifty", "Sixty", "Seventy", "Eighty", "Ninety"];

  function convert(n) {
    if (n === 0) return "";
    if (n < 10) return ones[n];
    if (n < 20) return teens[n - 10];
    if (n < 100) return tens[Math.floor(n / 10)] + (n % 10 ? " " + ones[n % 10] : "");
    if (n < 1000) return ones[Math.floor(n / 100)] + " Hundred" + (n % 100 ? " " + convert(n % 100) : "");
    if (n < 1000000) return convert(Math.floor(n / 1000)) + " Thousand" + (n % 1000 ? " " + convert(n % 1000) : "");
    return convert(Math.floor(n / 1000000)) + " Million" + (n % 1000000 ? " " + convert(n % 1000000) : "");
  }

  const pesos = Math.floor(num);
  const centavos = Math.round((num - pesos) * 100);

  if (pesos === 0 && centavos === 0) return "Zero Pesos Only";
  if (centavos > 0) {
    return convert(pesos).trim() + " Pesos and " + convert(centavos).trim() + " Centavos Only";
  }
  return convert(pesos).trim() + " Pesos Only";
}

// ===== Wrapper Function for Consistency =====
function numberToWordsWithCents(value) {
  return numberToWords(value);
}

// ===== Core Computation for Add =====
function computeAddCTC() {
  const gross = parseFloat(document.getElementById("add_gross").value) || 0;
  const sal = parseFloat(document.getElementById("add_sal").value) || 0;
  const rpt = parseFloat(document.getElementById("add_rpt").value) || 0;
  const add = parseFloat(document.getElementById("add_additional").value) || 0;
  const basic = parseFloat(document.getElementById("add_basic").value) || 0;

  const gr_tax = gross * 0.001;
  const sal_tax = sal * 0.001;
  const rpt_tax = rpt * 0.001;

  document.getElementById("add_gr_tax").value = gr_tax.toFixed(2);
  document.getElementById("add_sal_tax").value = sal_tax.toFixed(2);
  document.getElementById("add_rpt_tax").value = rpt_tax.toFixed(2);

  const dateValue = document.getElementById("add_date_issued").value;
  const dateIssued = dateValue ? new Date(dateValue + "T00:00:00") : null;

  let surchargeRate = 0;
  if (dateIssued instanceof Date && !isNaN(dateIssued)) {
    const month = dateIssued.getMonth() + 1;
    if (month >= 3) surchargeRate = Math.min(((month - 2) * 2) + 4, 24);
  }

  const subtotal = gr_tax + sal_tax + rpt_tax + basic + add;
  const surcharge = subtotal * (surchargeRate / 100);
  const total = subtotal + surcharge;

  document.getElementById("add_surcharge").value = surcharge.toFixed(2);
  document.getElementById("add_total_due").value = total.toFixed(2);
  document.getElementById("add_amount_words").value = numberToWordsWithCents(total);
}

// ===== Core Computation for Edit =====
function computeEditCTC(id) {
  const gross = parseFloat(document.getElementById(`edit_gross_${id}`).value) || 0;
  const sal = parseFloat(document.getElementById(`edit_sal_${id}`).value) || 0;
  const rpt = parseFloat(document.getElementById(`edit_rpt_${id}`).value) || 0;
  const add = parseFloat(document.getElementById(`edit_additional_${id}`).value) || 0;
  const basic = parseFloat(document.getElementById(`edit_basic_${id}`).value) || 0;

  const gr_tax = gross * 0.001;
  const sal_tax = sal * 0.001;
  const rpt_tax = rpt * 0.001;

  document.getElementById(`edit_gr_tax_${id}`).value = gr_tax.toFixed(2);
  document.getElementById(`edit_sal_tax_${id}`).value = sal_tax.toFixed(2);
  document.getElementById(`edit_rpt_tax_${id}`).value = rpt_tax.toFixed(2);

  const dateValue = document.getElementById(`edit_date_issued_${id}`).value;
  const dateIssued = dateValue ? new Date(dateValue + "T00:00:00") : null;

  let surchargeRate = 0;
  if (dateIssued instanceof Date && !isNaN(dateIssued)) {
    const month = dateIssued.getMonth() + 1;
    if (month >= 3) surchargeRate = Math.min(((month - 2) * 2) + 4, 24);
  }

  const subtotal = gr_tax + sal_tax + rpt_tax + basic + add;
  const surcharge = subtotal * (surchargeRate / 100);
  const total = subtotal + surcharge;

  document.getElementById(`edit_surcharge_${id}`).value = surcharge.toFixed(2);
  document.getElementById(`edit_total_due_${id}`).value = total.toFixed(2);
  document.getElementById(`edit_amount_words_${id}`).value = numberToWordsWithCents(total);
}

// ===== Bootstrap Modal Event Bindings =====
document.addEventListener("DOMContentLoaded", () => {
  // ADD modal events
  const addModalEl = document.getElementById("addCTCModal");
  if (addModalEl) {
    addModalEl.addEventListener("shown.bs.modal", () => {
      document.querySelectorAll("#addCTCModal input, #addCTCModal select").forEach(el => {
        ["input", "change"].forEach(evt => el.addEventListener(evt, computeAddCTC));
      });
      computeAddCTC();
    });
  }

  // EDIT modal events
  document.querySelectorAll("[id^='editModal']").forEach(modal => {
    const id = modal.id.match(/\d+$/)[0];
    modal.addEventListener("shown.bs.modal", () => {
      document.querySelectorAll(`#editModal${id} input, #editModal${id} select`).forEach(el => {
        ["input", "change"].forEach(evt => el.addEventListener(evt, () => computeEditCTC(id)));
      });
      computeEditCTC(id);
    });
  });
});
</script>
