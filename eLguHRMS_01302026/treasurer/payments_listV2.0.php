<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';

if(!in_array($_SESSION['role'], ['admin','assessor','treasurer','cashier'])) {
    header("Location: ../index.php");
    exit;
}

// Pagination & filters (kept similar to your last version)
$limit = 50;
$page = isset($_GET['page']) ? max(1,intval($_GET['page'])) : 1;
$offset = ($page-1)*$limit;

$globalFilter = $_GET['search'] ?? '';
$whereParts = [];
if($globalFilter!=='') {
    $esc = $mysqli->real_escape_string($globalFilter);
    $whereParts[] = "(owner_name LIKE '%$esc%' OR td_no LIKE '%$esc%' OR lot_no LIKE '%$esc%' OR rptsp_no LIKE '%$esc%')";
}
$whereSql = $whereParts ? "WHERE ".implode(" AND ", $whereParts) : "";

$totalRes = $mysqli->query("SELECT COUNT(*) AS cnt FROM payments_list $whereSql");
$total = $totalRes->fetch_assoc()['cnt'] ?? 0;
$total_pages = $total>0 ? ceil($total/$limit) : 1;

$res = $mysqli->query("
    SELECT *
    FROM payments_list
    $whereSql
    ORDER BY id DESC
    LIMIT $limit OFFSET $offset
");
?>

<div class="container-fluid mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-cash-stack"></i> Payments List</h4>
    <div class="d-flex gap-2">
      <button id="proceedPaymentBtn" class="btn btn-warning" disabled data-bs-toggle="modal" data-bs-target="#finalPaymentModal">💰 Proceed to Payment</button>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <form method="get" class="row g-2 align-items-center">
        <div class="col-md-4"><input class="form-control" name="search" placeholder="Search owner/td/lot/rptsp" value="<?=htmlspecialchars($globalFilter)?>"></div>
        <div class="col-md-1"><button class="btn btn-primary">Filter</button></div>
        <div class="col-md-1"><a href="payments_list.php?page=1" class="btn btn-secondary">Clear</a></div>
    </div>

      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
      <span>Payments (editable)</span>
      <small class="text-muted">Select rows, then "Proceed to Payment"</small>
    </div>

    <div class="card-body table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-dark">
          <tr>
            <th><input type="checkbox" id="select_all"></th>
            <th>ID</th><th>RPTSP</th><th>Owner</th><th>TD No</th><th>Lot No</th><th>Tax Year</th>
            <th>Barangay</th><th>Location</th><th>Classification</th>
            <th>Assessed Value</th><th>Basic Tax</th><th>SEF Tax</th><th>Adjustments</th>
            <th>Discount</th><th>Penalty</th><th>Total Due</th><th>Status</th><th>Processed By</th><th>Assessed Date</th>
          </tr>
        </thead>
        <tbody>
          <?php if($res && $res->num_rows>0): while($r=$res->fetch_assoc()): ?>
          <tr data-id="<?=$r['id']?>">
            <td><input type="checkbox" class="row-checkbox" value="<?=$r['id']?>"></td>
            <td><?=$r['id']?></td>
            <td><?=htmlspecialchars($r['rptsp_no'])?></td>
            <td><?=htmlspecialchars($r['owner_name'])?></td>
            <td><?=htmlspecialchars($r['td_no'])?></td>
            <td><?=htmlspecialchars($r['lot_no'])?></td>
            <td><?=htmlspecialchars($r['tax_year'])?></td>
            <td contenteditable="true" data-field="barangay"><?=htmlspecialchars($r['barangay'])?></td>
            <td contenteditable="true" data-field="location"><?=htmlspecialchars($r['location'])?></td>
            <td contenteditable="true" data-field="classification"><?=htmlspecialchars($r['classification'])?></td>
            <td contenteditable="true" data-field="assessed_value">₱<?=number_format((float)$r['assessed_value'],2)?></td>
            <td contenteditable="true" data-field="basic_tax">₱<?=number_format((float)$r['basic_tax'],2)?></td>
            <td contenteditable="true" data-field="sef_tax">₱<?=number_format((float)$r['sef_tax'],2)?></td>
            <td contenteditable="true" data-field="adjustments">₱<?=number_format((float)$r['adjustments'],2)?></td>
            <td contenteditable="true" data-field="discount">₱<?=number_format((float)$r['discount'],2)?></td>
            <td contenteditable="true" data-field="penalty">₱<?=number_format((float)$r['penalty'],2)?></td>
            <td contenteditable="true" data-field="total_due">₱<?=number_format((float)$r['total_due'],2)?></td>
            <td>
              <?php if(strtolower($r['status'])=='paid'): ?>
                <span class="badge bg-success">Paid</span>
              <?php else: ?>
                <span class="badge bg-danger">Unpaid</span>
              <?php endif; ?>
            </td>
            <td contenteditable="true" data-field="processed_by"><?=htmlspecialchars($r['processed_by'])?></td>
            <td><?=htmlspecialchars($r['assessed_date'])?></td>
          </tr>
          <?php endwhile; else: ?>
          <tr><td colspan="20" class="text-center text-muted">No payments found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>

      <nav>
        <ul class="pagination justify-content-center">
          <?php for($i=1;$i<=$total_pages;$i++): ?>
          <li class="page-item <?=($i==$page)?'active':''?>"><a class="page-link" href="?page=<?=$i?>"><?= $i ?></a></li>
          <?php endfor; ?>
        </ul>
      </nav>
    </div>
  </div>
</div>

<!-- Final Payment Modal -->
<div class="modal fade" id="finalPaymentModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form id="finalPaymentForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Finalize Payment (Official Receipt)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="selected_ids" id="selected_ids_input">
        <div class="row g-2">
          <div class="col-md-4"><label>OR No</label><input type="text" name="or_no" class="form-control" required></div>
          <div class="col-md-4"><label>Payor Name</label><input type="text" name="payor_name" class="form-control" required></div>
          <div class="col-md-4"><label>Payment Date</label><input type="datetime-local" name="payment_date" class="form-control" value="<?=date('Y-m-d\TH:i')?>"></div>

          <div class="col-md-4"><label>Previous OR No</label><input type="text" name="previous_or_no" class="form-control"></div>
          <div class="col-md-4"><label>Previous Date Paid</label><input type="datetime-local" name="previous_date_paid" class="form-control"></div>
          <div class="col-md-4"><label>Previous Year</label><input type="number" name="previous_year" class="form-control"></div>

          <div class="col-md-4">
            <label>Payment Method</label>
            <select name="payment_method" class="form-select">
              <option value="cash">Cash</option>
              <option value="check">Check</option>
            </select>
          </div>
          <div class="col-md-4"><label>Bank Name</label><input type="text" name="bank_name" class="form-control"></div>
          <div class="col-md-4"><label>Check Date</label><input type="date" name="check_date" class="form-control"></div>
          <div class="col-md-4"><label>Check Amount</label><input type="number" step="0.01" name="check_amount" class="form-control"></div>

          <div class="col-md-12">
            <small class="text-muted">Note: All selected rows will be moved to Collections (collections table) and removed from payments_list upon successful finalize/print.</small>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Finalize & Print OR</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(function(){
  // checkbox logic
  $('#select_all').on('change', function(){
    $('.row-checkbox').prop('checked', $(this).is(':checked'));
    toggleProceed();
  });
  $(document).on('change', '.row-checkbox', function(){
    if(!$(this).is(':checked')) $('#select_all').prop('checked', false);
    else if($('.row-checkbox:checked').length === $('.row-checkbox').length) $('#select_all').prop('checked', true);
    toggleProceed();
  });

  function toggleProceed(){
    var any = $('.row-checkbox:checked').length > 0;
    $('#proceedPaymentBtn').prop('disabled', !any);
  }

  // When Proceed clicked, set hidden selected ids
  $('#proceedPaymentBtn').on('click', function(){
    var ids = $('.row-checkbox:checked').map(function(){ return $(this).val(); }).get();
    $('#selected_ids_input').val(ids.join(','));
    // modal will show via data-bs-target
  });

  // Inline edit save on blur: send small AJAX to save changes manually (keeps "editable list" behavior)
  $(document).on('blur','[contenteditable="true"]', function(){
    var td = $(this);
    var row = td.closest('tr');
    var id = row.data('id');
    var field = td.data('field');
    var raw = td.text().trim();

    // strip currency symbol if present and convert to plain number for numeric fields
    if(['assessed_value','basic_tax','sef_tax','adjustments','discount','penalty','total_due'].includes(field)){
      raw = raw.replace(/[₱,]/g,'').trim();
      raw = raw === '' ? '0' : raw;
    }

    $.post('payments_list_update_ajax.php', { id: id, field: field, value: raw }, function(resp){
      // optionally show small toast - omitted for brevity
      // update UI if server returns normalized value
      if(resp && resp.success && resp.normalized !== undefined){
        td.text(resp.normalized);
      }
    }, 'json');
  });

  // Submit final payment
  $('#finalPaymentForm').on('submit', function(e){
    e.preventDefault();
    var $btn = $(this).find('button[type="submit"]');
    $btn.prop('disabled', true).text('Processing...');
    var data = $(this).serializeArray();
    // include selected ids
    data.push({name:'selected_ids', value: $('#selected_ids_input').val()});
    $.post('final_payment.php', data, function(response){
      $btn.prop('disabled', false).text('Finalize & Print OR');
      if(response.success){
        // open OR preview(s)
        if(response.ids && response.ids.length){
          var url = 'report_or.php?ids=' + response.ids.join(',');
          window.open(url,'_blank');
        }
        location.reload();
      } else {
        alert('Error: ' + (response.message || 'Unknown error'));
      }
    }, 'json').fail(function(xhr){
      $btn.prop('disabled', false).text('Finalize & Print OR');
      alert('Server error:\n' + xhr.responseText);
    });
  });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
