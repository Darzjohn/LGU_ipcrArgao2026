<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';

// ✅ Only Admin, Treasurer, and Cashier can access
if(!in_array(strtolower($_SESSION['role']), ['admin','treasurer','cashier'])) {
    include __DIR__ . '/../unauthorized.php';
    exit;
}

$limit = 50;
$page = isset($_GET['page']) ? max(1,intval($_GET['page'])) : 1;
$offset = ($page-1)*$limit;

$search = trim($_GET['search'] ?? '');
$whereSql = '';
if($search !== '') {
    $esc = $mysqli->real_escape_string($search);
    $whereSql = "WHERE owner_name LIKE '%$esc%' OR td_no LIKE '%$esc%' OR lot_no LIKE '%$esc%' OR rptsp_no LIKE '%$esc%'";
}

$totalRes = $mysqli->query("SELECT COUNT(*) AS cnt FROM payments_list $whereSql");
$total = $totalRes->fetch_assoc()['cnt'] ?? 0;
$total_pages = max(ceil($total/$limit),1);

$res = $mysqli->query("
    SELECT * FROM payments_list
    $whereSql
    ORDER BY id DESC
    LIMIT $limit OFFSET $offset
");
?>

<div class="container-fluid mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-cash-stack"></i> Payments List</h4>
    <div class="d-flex gap-2">
      <button id="updateCalcBtn" class="btn btn-info" disabled>🔄 Update Calculation</button>
      <button id="proceedPaymentBtn" class="btn btn-warning" disabled>💰 Proceed to Payment</button>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <form method="get" class="row g-2 align-items-center">
        <div class="col-md-4">
          <input class="form-control" name="search" placeholder="Search owner/td/lot/rptsp" value="<?=htmlspecialchars($search)?>">
        </div>
        <div class="col-md-1"><button class="btn btn-primary">Filter</button></div>
        <div class="col-md-0"><a href="payments_list.php?page=1" class="btn btn-secondary">Clear</a></div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-body table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-dark">
          <tr>
            <th><input type="checkbox" id="select_all"></th>
            <th>ID</th><th>RPTSP</th><th>Owner</th><th>TD No</th><th>Lot No</th>
            <th>Tax Year</th><th>Barangay</th><th>Location</th><th>Classification</th>
            <th>Assessed Value</th><th>Basic Tax</th><th>SEF Tax</th><th>Adjustments</th>
            <th>Discount</th><th>Penalty</th><th>Total Due</th><th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if($res && $res->num_rows>0): while($r=$res->fetch_assoc()): ?>
          <tr data-id="<?=$r['id']?>">
            <td><input type="checkbox" class="row-checkbox" value="<?=$r['id']?>" data-amount="<?=$r['total_due']?>"></td>
            <td><?=$r['id']?></td>
            <td><?=htmlspecialchars($r['rptsp_no'])?></td>
            <td><?=htmlspecialchars($r['owner_name'])?></td>
            <td><?=htmlspecialchars($r['td_no'])?></td>
            <td><?=htmlspecialchars($r['lot_no'])?></td>
            <td><?=htmlspecialchars($r['tax_year'])?></td>
            <td><?=htmlspecialchars($r['barangay'])?></td>
            <td><?=htmlspecialchars($r['location'])?></td>
            <td><?=htmlspecialchars($r['classification'])?></td>
            <td>₱<?=number_format($r['assessed_value'],2)?></td>
            <td>₱<?=number_format($r['basic_tax'],2)?></td>
            <td>₱<?=number_format($r['sef_tax'],2)?></td>
            <td>₱<?=number_format($r['adjustments'],2)?></td>
            <td>₱<?=number_format($r['discount'],2)?></td>
            <td>₱<?=number_format($r['penalty'],2)?></td>
            <td>₱<?=number_format($r['total_due'],2)?></td>
            <td>
              <?php if(strtolower($r['status'])=='paid'): ?>
                <span class="badge bg-success">Paid</span>
              <?php else: ?>
                <span class="badge bg-danger">Unpaid</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endwhile; else: ?>
          <tr><td colspan="18" class="text-center text-muted">No payments found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Final Payment Modal -->
<div class="modal fade" id="finalPaymentModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form id="finalPaymentForm" class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Finalize Payment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="selected_ids" id="selected_ids">

        <div class="mb-3">
          <label>Total Cash Amount</label>
          <input type="number" name="total_cash_amount" step="0.01" class="form-control" placeholder="₱0.00">
        </div>
        <div class="mb-3">
          <label>Check Number</label>
          <input type="text" name="check_number" class="form-control" placeholder="Check No.">
        </div>
        <div class="mb-3">
          <label>OR Number</label>
          <input type="text" name="or_no" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Payor Name</label>
          <input type="text" name="payor_name" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">✅ Confirm Payment</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(function(){
  // Select all toggle
  $('#select_all').on('change', function(){
    $('.row-checkbox').prop('checked', this.checked);
    toggleButtons();
  });
  $(document).on('change','.row-checkbox', toggleButtons);
  function toggleButtons(){
    let count = $('.row-checkbox:checked').length;
    $('#proceedPaymentBtn, #updateCalcBtn').prop('disabled', count===0);
  }

  // ✅ Update Calculation
  $('#updateCalcBtn').on('click', function(){
    let ids = $('.row-checkbox:checked').map(function(){ return $(this).val(); }).get();
    if(ids.length===0){ alert('No rows selected.'); return; }
    $.post('update_calculation.php',{ids:ids.join(',')},function(resp){
      if(resp.success){ alert('✅ Calculation updated.'); location.reload(); }
      else alert('⚠️ '+resp.message);
    },'json');
  });

  // ✅ Proceed to Payment
  $('#proceedPaymentBtn').on('click', function(){
    let ids = $('.row-checkbox:checked').map(function(){ return $(this).val(); }).get();
    if(ids.length===0){ alert('⚠️ No payments selected.'); return; }
    $('#selected_ids').val(ids.join(','));
    new bootstrap.Modal(document.getElementById('finalPaymentModal')).show();
  });

  // ✅ Finalize Payment
  $('#finalPaymentForm').on('submit', function(e){
    e.preventDefault();
    let formData = $(this).serialize();
    $.post('final_payment.php', formData, function(resp){
      if(resp.success){
        alert('✅ '+resp.message);
        location.reload();
      } else {
        alert('⚠️ '+resp.message);
      }
    }, 'json').fail(function(xhr){
      alert('Server error:\n'+xhr.responseText);
    });
  });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
