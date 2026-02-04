<!-- ========================= ADD CORPORATION CTC MODAL ========================= -->
<div class="modal fade" id="addCTCCorpModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <form method="POST" id="addCTCCorpForm">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title"><i class="bi bi-building"></i> Add Corporation CTC</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">CTC Number</label>
              <input type="text" name="ctccorp_no" class="form-control" required>
            </div>
            <div class="col-md-2">
              <label class="form-label">Year</label>
              <input type="number" name="year" value="<?= date('Y') ?>" class="form-control" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">Place of Issue</label>
              <input type="text" name="place_of_issue" class="form-control" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">Date Issued</label>
              <input type="date" name="date_issued" value="<?= date('Y-m-d') ?>" class="form-control" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">Company Name</label>
              <input type="text" name="company_fullname" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Business Address</label>
              <input type="text" name="business_address" class="form-control" required>
            </div>

            <div class="col-md-4">
              <label class="form-label">Kind of Organization</label>
              <select name="kind_of_org" class="form-select" required>
                <option value="">-- Select --</option>
                <option value="Corporation">Corporation</option>
                <option value="Association">Association</option>
                <option value="Partnership">Partnership</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">Nature of Business</label>
              <input type="text" name="nature_of_business" class="form-control" required>
            </div>

            <div class="col-md-4">
              <label class="form-label">Incorporation Address</label>
              <input type="text" name="incorporation_address" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label">Date of Registration</label>
              <input type="date" name="date_reg" class="form-control">
            </div>

            <hr class="mt-3">

            <div class="col-md-3">
              <label class="form-label">Assessed Value (RPT)</label>
              <input type="number" step="0.01" name="rpt_assessedvalue" class="form-control compute" placeholder="0.00">
            </div>
            <div class="col-md-3">
              <label class="form-label">Gross Receipts</label>
              <input type="number" step="0.01" name="gross_receipts" class="form-control compute" placeholder="0.00">
            </div>
            <div class="col-md-3">
              <label class="form-label">Basic Tax</label>
              <input type="number" step="0.01" name="basic_tax" class="form-control compute" value="500.00">
            </div>
            <div class="col-md-3">
              <label class="form-label">Additional Tax</label>
              <input type="number" step="0.01" name="additional_tax" class="form-control compute" value="0.00">
            </div>

            <div class="col-md-3">
              <label class="form-label">RPT Tax Due</label>
              <input type="number" step="0.01" name="rpt_tax_due" class="form-control" readonly>
            </div>
            <div class="col-md-3">
              <label class="form-label">GR Tax Due</label>
              <input type="number" step="0.01" name="gr_tax_due" class="form-control" readonly>
            </div>
            <div class="col-md-3">
              <label class="form-label">Surcharge</label>
              <input type="number" step="0.01" name="surcharge" class="form-control compute" placeholder="0.00">
            </div>
            <div class="col-md-3">
              <label class="form-label">Total Due</label>
              <input type="number" step="0.01" name="total_due" class="form-control" readonly>
            </div>

            <div class="col-md-12">
              <label class="form-label">Amount in Words</label>
              <input type="text" id="amount_words_add" class="form-control" readonly>
            </div>

            <div class="col-md-6">
              <label class="form-label">Authorized Signatory Position</label>
              <input type="text" name="position_authorizedsig" class="form-control">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="add_ctccorp" class="btn btn-success">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ========================= EDIT MODALS ========================= -->
<?php foreach ($ctccorp_records as $r): ?>
<div class="modal fade" id="editCTCCorpModal<?= $r['id'] ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="ctccorp_id" value="<?= $r['id'] ?>">
        <div class="modal-header bg-warning">
          <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Edit Corporation CTC</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-4"><label>CTC Number</label>
              <input type="text" name="ctccorp_no" class="form-control" value="<?= htmlspecialchars($r['ctccorp_no']) ?>">
            </div>
            <div class="col-md-2"><label>Year</label>
              <input type="number" name="year" class="form-control" value="<?= htmlspecialchars($r['year']) ?>">
            </div>
            <div class="col-md-3"><label>Place of Issue</label>
              <input type="text" name="place_of_issue" class="form-control" value="<?= htmlspecialchars($r['place_of_issue']) ?>">
            </div>
            <div class="col-md-3"><label>Date Issued</label>
              <input type="date" name="date_issued" class="form-control" value="<?= htmlspecialchars($r['date_issued']) ?>">
            </div>

            <div class="col-md-6"><label>Company Name</label>
              <input type="text" name="company_fullname" class="form-control" value="<?= htmlspecialchars($r['company_fullname']) ?>">
            </div>
            <div class="col-md-6"><label>Business Address</label>
              <input type="text" name="business_address" class="form-control" value="<?= htmlspecialchars($r['business_address']) ?>">
            </div>

            <div class="col-md-4">
              <label>Kind of Organization</label>
              <select name="kind_of_org" class="form-select">
                <option <?= $r['kind_of_org']=='Corporation'?'selected':'' ?>>Corporation</option>
                <option <?= $r['kind_of_org']=='Association'?'selected':'' ?>>Association</option>
                <option <?= $r['kind_of_org']=='Partnership'?'selected':'' ?>>Partnership</option>
              </select>
            </div>

            <div class="col-md-6">
              <label>Nature of Business</label>
              <input type="text" name="nature_of_business" value="<?= htmlspecialchars($r['nature_of_business']) ?>" class="form-control" required>
            </div>
          
            <div class="col-md-4"><label>Incorporation Address</label>
              <input type="text" name="incorporation_address" class="form-control" value="<?= htmlspecialchars($r['incorporation_address']) ?>">
            </div>
            <div class="col-md-4"><label>Date of Registration</label>
              <input type="date" name="date_reg" class="form-control" value="<?= htmlspecialchars($r['date_reg']) ?>">
            </div>

            <hr class="mt-3">

            <div class="col-md-3"><label>Assessed Value (RPT)</label>
              <input type="number" step="0.01" name="rpt_assessedvalue" class="form-control compute-edit" value="<?= $r['rpt_assessedvalue'] ?>">
            </div>
            <div class="col-md-3"><label>Gross Receipts</label>
              <input type="number" step="0.01" name="gross_receipts" class="form-control compute-edit" value="<?= $r['gross_receipts'] ?>">
            </div>
            <div class="col-md-3"><label>Basic Tax</label>
              <input type="number" step="0.01" name="basic_tax" class="form-control compute-edit" value="<?= $r['basic_tax'] ?>">
            </div>
            <div class="col-md-3"><label>Additional Tax</label>
              <input type="number" step="0.01" name="additional_tax" class="form-control compute-edit" value="<?= $r['additional_tax'] ?>">
            </div>

            <div class="col-md-3"><label>RPT Tax Due</label>
              <input type="number" step="0.01" name="rpt_tax_due" class="form-control" value="<?= $r['rpt_tax_due'] ?>" readonly>
            </div>
            <div class="col-md-3"><label>GR Tax Due</label>
              <input type="number" step="0.01" name="gr_tax_due" class="form-control" value="<?= $r['gr_tax_due'] ?>" readonly>
            </div>
            <div class="col-md-3"><label>Surcharge</label>
              <input type="number" step="0.01" name="surcharge" class="form-control compute-edit" value="<?= $r['surcharge'] ?>">
            </div>
            <div class="col-md-3"><label>Total Due</label>
              <input type="number" step="0.01" name="total_due" class="form-control" value="<?= $r['total_due'] ?>" readonly>
            </div>

            <div class="col-md-12">
              <label class="form-label">Amount in Words</label>
              <input type="text" id="amount_words_edit_<?= $r['id'] ?>" class="form-control" readonly>
            </div>

            <div class="col-md-6">
              <label>Authorized Signatory Position</label>
              <input type="text" name="position_authorizedsig" class="form-control" value="<?= htmlspecialchars($r['position_authorizedsig']) ?>">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="edit_ctccorp" class="btn btn-warning">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endforeach; ?>

<script>
// ======== FIXED AUTO COMPUTATION SCRIPT ======== //
function computeCTC(parent, isEdit = false) {
  const getVal = name => parseFloat(parent.querySelector(`[name="${name}"]`)?.value) || 0;

  const rptVal = getVal('rpt_assessedvalue');
  const gross = getVal('gross_receipts');
  const basic = getVal('basic_tax');
  const add = getVal('additional_tax');

  // ===== Compute RPT and GR tax first =====
  const rptDue = Math.round((rptVal / 5000 * 2) * 100) / 100;
  const grDue = Math.round((gross / 5000 * 2) * 100) / 100;

  // ===== Compute surcharge based on date issued =====
  const dateField = parent.querySelector('[name="date_issued"]');
  const surchargeInput = parent.querySelector('[name="surcharge"]');
  let surcharge = 0;
  if (dateField && dateField.value) {
    const dateIssued = new Date(dateField.value + "T00:00:00");
    if (!isNaN(dateIssued)) {
      const month = dateIssued.getMonth() + 1;
      // Surcharge formula: 4% starting March + 2% per month after, capped at 24%
      let surchargeRate = 0;
      if (month >= 3) surchargeRate = Math.min(((month - 2) * 2) + 4, 24);
      // Apply surcharge to sum of basic + additional + rptDue + grDue
      surcharge = Math.round(((basic + add + rptDue + grDue) * surchargeRate / 100) * 100) / 100;
    }
  }

  // Update surcharge input
  if (surchargeInput) surchargeInput.value = surcharge.toFixed(2);

  // ===== Compute total =====
  const total = Math.round((basic + add + rptDue + grDue + surcharge) * 100) / 100;

  // Update computed fields
  parent.querySelector('[name="rpt_tax_due"]').value = rptDue.toFixed(2);
  parent.querySelector('[name="gr_tax_due"]').value = grDue.toFixed(2);
  parent.querySelector('[name="total_due"]').value = total.toFixed(2);

  // ===== Amount in Words =====
  setTimeout(() => {
    const fixedTotal = parseFloat(parent.querySelector('[name="total_due"]').value) || 0;
    const words = numberToWordsWithCents(fixedTotal);
    const wordField = parent.querySelector(isEdit ? '[id^="amount_words_edit_"]' : '#amount_words_add');
    if (wordField) wordField.value = words;
  }, 0);
}

// ===== Helper Functions =====
function numberToWordsWithCents(value) {
  value = Math.round((value + Number.EPSILON) * 100) / 100;
  const pesos = Math.floor(value);
  const centavos = Math.round((value - pesos) * 100);
  if (pesos === 0 && centavos === 0) return "Zero Pesos Only";
  let words = numberToWords(pesos) + " Peso" + (pesos !== 1 ? "s" : "");
  if (centavos > 0) words += " and " + numberToWords(centavos) + " Centavo" + (centavos !== 1 ? "s" : "");
  return words + " Only";
}

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
  return convert(Math.floor(num)).trim();
}

// ===== Event Listeners =====
document.querySelectorAll('.compute').forEach(el => {
  el.addEventListener('input', e => computeCTC(e.target.closest('form')));
});
document.querySelectorAll('.compute-edit').forEach(el => {
  el.addEventListener('input', e => computeCTC(e.target.closest('form'), true));
});
document.getElementById('addCTCCorpModal').addEventListener('shown.bs.modal', () => {
  const form = document.querySelector('#addCTCCorpForm');
  if (form) computeCTC(form);
});
document.querySelectorAll('[id^="editCTCCorpModal"]').forEach(modal => {
  modal.addEventListener('shown.bs.modal', () => {
    const form = modal.querySelector('form');
    if (form) computeCTC(form, true);
  });
});
</script>

