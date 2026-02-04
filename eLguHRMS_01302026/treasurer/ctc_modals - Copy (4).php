<?php
// Prevent undefined variable warning
if (!isset($ctc_records)) {
    $ctc_records = [];
}
?>

<!-- ✅ ADD CTC MODAL -->
<div class="modal fade" id="addCTCModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">➕ Add Community Tax Certificate (Individual)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="post">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-4">
              <label>CTC No.</label>
              <input type="text" name="ctc_no" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label>Year</label>
              <input type="number" name="year" class="form-control" value="<?= date('Y') ?>" required>
            </div>
            <div class="col-md-4">
              <label>Date Issued</label>
              <input type="date" name="date_issued" id="add_date_issued" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>

            <div class="col-md-6">
              <label>Place of Issue</label>
              <input type="text" name="place_of_issue" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label>Address</label>
              <input type="text" name="address" class="form-control" required>
            </div>

            <div class="col-md-4">
              <label>Surname</label>
              <input type="text" name="surname" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label>Firstname</label>
              <input type="text" name="firstname" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label>Middlename</label>
              <input type="text" name="middlename" class="form-control">
            </div>

            <div class="col-md-4">
              <label>Citizenship</label>
              <input type="text" name="citizenship" class="form-control">
            </div>
            <div class="col-md-4">
              <label>Place of Birth</label>
              <input type="text" name="place_of_birth" class="form-control">
            </div>
            <div class="col-md-4">
              <label>Civil Status</label>
              <select name="civil_status" class="form-select">
                <option>Single</option>
                <option>Married</option>
                <option>Widowed</option>
                <option>Separated</option>
              </select>
            </div>

            <div class="col-12 mt-3">
              <h6 class="fw-bold text-primary">💰 Tax Computation</h6>
            </div>

            <div class="col-md-3">
              <label>Gross Receipts</label>
              <input type="number" step="0.01" name="gross_receipts" id="add_gross" class="form-control tax-input" value="0">
            </div>
            <div class="col-md-3">
              <label>Salaries</label>
              <input type="number" step="0.01" name="salaries" id="add_sal" class="form-control tax-input" value="0">
            </div>
            <div class="col-md-3">
              <label>Real Property Income</label>
              <input type="number" step="0.01" name="real_property_income" id="add_rpt" class="form-control tax-input" value="0">
            </div>
            <div class="col-md-3">
              <label>Basic Tax</label>
              <input type="number" step="0.01" name="basic_tax" id="add_basic" class="form-control tax-input" value="5.00" readonly>
            </div>

            <div class="col-md-3">
              <label>GR Tax Due</label>
              <input type="number" step="0.01" name="gr_tax_due" id="add_gr_tax" class="form-control" readonly>
            </div>
            <div class="col-md-3">
              <label>SAL Tax Due</label>
              <input type="number" step="0.01" name="sal_tax_due" id="add_sal_tax" class="form-control" readonly>
            </div>
            <div class="col-md-3">
              <label>RPT Tax Due</label>
              <input type="number" step="0.01" name="rpt_tax_due" id="add_rpt_tax" class="form-control" readonly>
            </div>
            <div class="col-md-3">
              <label>Surcharge</label>
              <input type="number" step="0.01" name="surcharge" id="add_surcharge" class="form-control" readonly>
            </div>

            <div class="col-md-3">
              <label>Additional Tax</label>
              <input type="number" step="0.01" name="additional_tax" id="add_additional" class="form-control" value="0">
            </div>
            <div class="col-md-3">
              <label>Total Due</label>
              <input type="number" step="0.01" name="total_due" id="add_total_due" class="form-control" readonly>
            </div>

            <div class="col-md-6">
              <label>Amount in Words</label>
              <input type="text" id="add_amount_words" class="form-control" readonly>
            </div>

          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" name="add_ctc" class="btn btn-success">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ✅ Edit Modals -->
<?php foreach ($ctc_records as $r): ?>
<div class="modal fade" id="editModal<?= $r['id'] ?>" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title">✏️ Edit CTC #<?= htmlspecialchars($r['ctc_no']) ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="post">
        <div class="modal-body">
          <input type="hidden" name="ctc_id" value="<?= $r['id'] ?>">
          <!-- You can duplicate same fields here, identical to Add Modal -->
        </div>
        <div class="modal-footer">
          <button type="submit" name="edit_ctc" class="btn btn-warning">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endforeach; ?>

<script>
function numberToWords(num) {
  const ones = ["","One","Two","Three","Four","Five","Six","Seven","Eight","Nine"];
  const teens = ["Ten","Eleven","Twelve","Thirteen","Fourteen","Fifteen","Sixteen","Seventeen","Eighteen","Nineteen"];
  const tens = ["","","Twenty","Thirty","Forty","Fifty","Sixty","Seventy","Eighty","Ninety"];
  if (num === 0) return "Zero";
  function convert(n){
    if(n < 10) return ones[n];
    if(n < 20) return teens[n-10];
    if(n < 100) return tens[Math.floor(n/10)] + (n%10 ? " " + ones[n%10] : "");
    if(n < 1000) return ones[Math.floor(n/100)] + " Hundred " + convert(n%100);
    if(n < 1000000) return convert(Math.floor(n/1000)) + " Thousand " + convert(n%1000);
    return convert(Math.floor(n/1000000)) + " Million " + convert(n%1000000);
  }
  return convert(Math.floor(num)) + " Pesos Only";
}

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

  // ✅ Determine surcharge by date
  const dateIssued = new Date(document.getElementById("add_date_issued").value);
  const month = dateIssued.getMonth() + 1; // 1 = Jan, 12 = Dec
  let surchargeRate = 0;

  if (month >= 3) {
    surchargeRate = Math.min(((month - 2) * 2) + 4, 24); // 6% March, 8% April...
  }

  const subtotal = gr_tax + sal_tax + rpt_tax + basic + add;
  const surcharge = subtotal * (surchargeRate / 100);
  const total = subtotal + surcharge;

  document.getElementById("add_surcharge").value = surcharge.toFixed(2);
  document.getElementById("add_total_due").value = total.toFixed(2);
  document.getElementById("add_amount_words").value = numberToWords(Math.round(total));
}

document.querySelectorAll(".tax-input, #add_date_issued, #add_additional").forEach(el => {
  el.addEventListener("input", computeAddCTC);
});
</script>
