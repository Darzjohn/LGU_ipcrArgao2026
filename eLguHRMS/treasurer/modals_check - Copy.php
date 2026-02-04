<?php
// Fetch bank accounts and fund sources for dropdowns
$bank_accounts_res = $mysqli->query("SELECT * FROM bank_accounts ORDER BY account_name ASC");
$bank_accounts = [];
if($bank_accounts_res){
    while($b = $bank_accounts_res->fetch_assoc()) $bank_accounts[] = $b;
}

$fund_sources_res = $mysqli->query("SELECT * FROM fund_source WHERE status=1 ORDER BY name ASC");
$fund_sources = [];
if($fund_sources_res){
    while($f = $fund_sources_res->fetch_assoc()) $fund_sources[] = $f;
}
?>

<!-- Add New RCI Modal -->
<div class="modal fade" id="addRCIModal" tabindex="-1" aria-labelledby="addRCIModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <form method="post" id="addRCIForm">
        <div class="modal-header">
          <h5 class="modal-title" id="addRCIModalLabel">➕ Add New RCI</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">

          <div class="row g-2 mb-2">
            <div class="col-md-6">
              <label class="form-label">Bank Account <span class="text-danger">*</span></label>
              <select name="account_id" class="form-control" required>
                <option value="">-- Select Bank Account --</option>
                <?php foreach($bank_accounts as $b): ?>
                  <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['account_name'] . ' (' . $b['account_number'] . ')') ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Fund Source</label>
              <select name="fund_source_id" class="form-control">
                <option value="">-- Select Fund Source --</option>
                <?php foreach($fund_sources as $f): ?>
                  <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="row g-2 mb-2">
            <div class="col-md-4"><label class="form-label">Serial No</label><input type="text" name="serial_no" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">DV/Payroll No</label><input type="text" name="dv_payroll_no" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">CAFOA No</label><input type="text" name="cafoa_no" class="form-control"></div>
          </div>

          <div class="row g-2 mb-2">
            <div class="col-md-4"><label class="form-label">Sub-Allotment</label><input type="text" name="sub_allotment" class="form-control"></div>
            <div class="col-md-8"><label class="form-label">Nature of Payment</label><textarea name="nature_of_payment" class="form-control" rows="2"></textarea></div>
          </div>

          <div class="row g-2 mb-2">
            <div class="col-md-4"><label class="form-label">Gross Amount</label><input type="number" step="0.01" name="gross_amount" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Check No <span class="text-danger">*</span></label><input type="text" name="check_no" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">Payee <span class="text-danger">*</span></label><input type="text" name="payee" class="form-control" required></div>
          </div>

          <div class="row g-2 mb-2">
            <div class="col-md-4"><label class="form-label">Issue Date <span class="text-danger">*</span></label><input type="date" name="issue_date" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">Amount <span class="text-danger">*</span></label><input type="number" step="0.01" name="amount" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">Cleared</label>
              <select name="cleared" class="form-control">
                <option value="no" selected>No</option>
                <option value="yes">Yes</option>
              </select>
            </div>
          </div>

          <div class="mb-2"><label class="form-label">Remarks</label><textarea name="remarks" class="form-control" rows="2"></textarea></div>

        </div>
        <div class="modal-footer">
          <button type="submit" name="add_rci" class="btn btn-success">💾 Save RCI</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php if(!empty($rci_records)): ?>
  <?php foreach($rci_records as $r): ?>
    <!-- Edit RCI Modal -->
    <div class="modal fade" id="editRCIModal<?= $r['id'] ?>" tabindex="-1" aria-labelledby="editRCIModalLabel<?= $r['id'] ?>" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
          <form method="post" id="editRCIForm<?= $r['id'] ?>">
            <input type="hidden" name="rci_id" value="<?= $r['id'] ?>">
            <div class="modal-header">
              <h5 class="modal-title" id="editRCIModalLabel<?= $r['id'] ?>">✏️ Edit RCI</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

              <div class="row g-2 mb-2">
                <div class="col-md-6">
                  <label class="form-label">Bank Account <span class="text-danger">*</span></label>
                  <select name="account_id" class="form-control" required>
                    <option value="">-- Select Bank Account --</option>
                    <?php foreach($bank_accounts as $b): ?>
                      <option value="<?= $b['id'] ?>" <?= ($r['account_id']==$b['id']?'selected':'') ?>>
                        <?= htmlspecialchars($b['account_name'] . ' (' . $b['account_number'] . ')') ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Fund Source</label>
                  <select name="fund_source_id" class="form-control">
                    <option value="">-- Select Fund Source --</option>
                    <?php foreach($fund_sources as $f): ?>
                      <option value="<?= $f['id'] ?>" <?= ($r['fund_source_id']==$f['id']?'selected':'') ?>><?= htmlspecialchars($f['name']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <div class="row g-2 mb-2">
                <div class="col-md-4"><label class="form-label">Serial No</label><input type="text" name="serial_no" class="form-control" value="<?= htmlspecialchars($r['serial_no']) ?>"></div>
                <div class="col-md-4"><label class="form-label">DV/Payroll No</label><input type="text" name="dv_payroll_no" class="form-control" value="<?= htmlspecialchars($r['dv_payroll_no']) ?>"></div>
                <div class="col-md-4"><label class="form-label">CAFOA No</label><input type="text" name="cafoa_no" class="form-control" value="<?= htmlspecialchars($r['cafoa_no']) ?>"></div>
              </div>

              <div class="row g-2 mb-2">
                <div class="col-md-4"><label class="form-label">Sub-Allotment</label><input type="text" name="sub_allotment" class="form-control" value="<?= htmlspecialchars($r['sub_allotment']) ?>"></div>
                <div class="col-md-8"><label class="form-label">Nature of Payment</label><textarea name="nature_of_payment" class="form-control" rows="2"><?= htmlspecialchars($r['nature_of_payment']) ?></textarea></div>
              </div>

              <div class="row g-2 mb-2">
                <div class="col-md-4"><label class="form-label">Gross Amount</label><input type="number" step="0.01" name="gross_amount" class="form-control" value="<?= $r['gross_amount'] ?>"></div>
                <div class="col-md-4"><label class="form-label">Check No <span class="text-danger">*</span></label><input type="text" name="check_no" class="form-control" value="<?= htmlspecialchars($r['check_no']) ?>" required></div>
                <div class="col-md-4"><label class="form-label">Payee <span class="text-danger">*</span></label><input type="text" name="payee" class="form-control" value="<?= htmlspecialchars($r['payee']) ?>" required></div>
              </div>

              <div class="row g-2 mb-2">
                <div class="col-md-4"><label class="form-label">Issue Date <span class="text-danger">*</span></label><input type="date" name="issue_date" class="form-control" value="<?= htmlspecialchars($r['issue_date']) ?>" required></div>
                <div class="col-md-4"><label class="form-label">Amount <span class="text-danger">*</span></label><input type="number" step="0.01" name="amount" class="form-control" value="<?= $r['amount'] ?>" required></div>
                <div class="col-md-4"><label class="form-label">Cleared</label>
                  <select name="cleared" class="form-control">
                    <option value="no" <?= ($r['cleared']=='no'?'selected':'') ?>>No</option>
                    <option value="yes" <?= ($r['cleared']=='yes'?'selected':'') ?>>Yes</option>
                  </select>
                </div>
              </div>

              <div class="mb-2"><label class="form-label">Remarks</label><textarea name="remarks" class="form-control" rows="2"><?= htmlspecialchars($r['remarks']) ?></textarea></div>

            </div>
            <div class="modal-footer">
              <button type="submit" name="edit_rci" class="btn btn-success">💾 Save Changes</button>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
