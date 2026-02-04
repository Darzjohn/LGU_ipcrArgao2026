<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';

// ✅ Only Admin, Treasurer, and Cashier can access
if(!in_array($_SESSION['role'], ['admin','treasurer','cashier'])) {
    include __DIR__ . '/../unauthorized.php';
    exit;
}

// Pagination & filters
$limit = 50;
$page = isset($_GET['page']) ? max(1,intval($_GET['page'])) : 1;
$offset = ($page-1)*$limit;

$search = trim($_GET['search'] ?? '');
$whereSql = '';
if($search !== '') {
    $esc = $mysqli->real_escape_string($search);
    $whereSql = "WHERE owner_name LIKE '%$esc%' OR td_no LIKE '%$esc%' OR lot_no LIKE '%$esc%' OR rptsp_no LIKE '%$esc%'";
}

// Total rows
$totalRes = $mysqli->query("SELECT COUNT(*) AS cnt FROM payments_list $whereSql");
$total = $totalRes->fetch_assoc()['cnt'] ?? 0;
$total_pages = max(ceil($total/$limit),1);

// Fetch rows
$res = $mysqli->query("
    SELECT * FROM payments_list
    $whereSql
    ORDER BY id DESC
    LIMIT $limit OFFSET $offset
");
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-cash-stack"></i> Form 56 - Real Property Tax - For Payments</h4>
        <div class="d-flex gap-2">
            <button id="updateCalcBtn" class="btn btn-info" disabled>🔄 Update Calculation</button>
            <button id="proceedPaymentBtn" class="btn btn-warning" disabled data-bs-toggle="modal" data-bs-target="#finalPaymentModal">
                💰 Proceed to Payment
            </button>
            <button id="deleteDuplicateBtn" class="btn btn-danger" disabled>🗑️ Delete Selected Duplicate</button>
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
        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
            <span>Payments (editable)</span>
            <small class="text-muted">Select rows, then use “Update Calculation”, “Proceed to Payment”, or delete duplicates</small>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th><input type="checkbox" id="select_all"></th>
                        <th>ID</th><th>RPTSP</th><th>Owner</th><th>TD No</th><th>Lot No</th>
                        <th>Tax Year</th><th>Barangay</th><th>Location</th><th>Classification</th>
                        <th>Assessed Value</th><th>Basic Tax</th><th>SEF Tax</th><th>Adjustments</th>
                        <th>Discount</th><th>Penalty</th><th>Total Due</th><th>Status</th><th>Processed By</th><th>Assessed Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($res && $res->num_rows>0): while($r=$res->fetch_assoc()): ?>
                    <tr data-id="<?=$r['id']?>" class="<?=strtolower($r['status'])=='paid'?'table-success':''?>">
                        <td>
                            <input type="checkbox" class="row-checkbox" value="<?=$r['id']?>" <?=strtolower($r['status'])=='paid'?'disabled':''?>>
                        </td>
                        <td><?=$r['id']?></td>
                        <td><?=htmlspecialchars($r['rptsp_no'])?></td>
                        <td><?=htmlspecialchars($r['owner_name'])?></td>
                        <td><?=htmlspecialchars($r['td_no'])?></td>
                        <td><?=htmlspecialchars($r['lot_no'])?></td>
                        <td><?=htmlspecialchars($r['tax_year'])?></td>
                        <td contenteditable="true" data-field="barangay"><?=htmlspecialchars($r['barangay'])?></td>
                        <td contenteditable="true" data-field="location"><?=htmlspecialchars($r['location'])?></td>
                        <td contenteditable="true" data-field="classification"><?=htmlspecialchars($r['classification'])?></td>
                        <td contenteditable="true" data-field="assessed_value">₱<?=number_format($r['assessed_value'],2)?></td>
                        <td contenteditable="true" data-field="basic_tax">₱<?=number_format($r['basic_tax'],2)?></td>
                        <td contenteditable="true" data-field="sef_tax">₱<?=number_format($r['sef_tax'],2)?></td>
                        <td contenteditable="true" data-field="adjustments">₱<?=number_format($r['adjustments'],2)?></td>
                        <td contenteditable="true" data-field="discount">₱<?=number_format($r['discount'],2)?></td>
                        <td contenteditable="true" data-field="penalty">₱<?=number_format($r['penalty'],2)?></td>
                        <td contenteditable="true" data-field="total_due">₱<?=number_format($r['total_due'],2)?></td>
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

            <!-- Pagination -->
            <nav>
                <ul class="pagination justify-content-center">
                    <?php for($i=1;$i<=$total_pages;$i++): ?>
                        <li class="page-item <?=($i==$page)?'active':''?>">
                            <a class="page-link" href="?page=<?=$i?>&search=<?=urlencode($search)?>"><?=$i?></a>
                        </li>
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

                <div id="summaryContainer" class="mb-3" style="display:none;">
                    <h6 class="text-center">Selected Payment Details</h6>
                    <table class="table table-bordered table-sm" id="selectedSummaryTable">
                        <thead class="table-light">
                            <tr>
                                <th>TD No</th>
                                <th>Year</th>
                                <th>Discount</th>
                                <th>Penalty</th>
                                <th>Total Due</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr class="table-secondary">
                                <th colspan="4" class="text-end">Grand Total:</th>
                                <th id="grandTotalCell">₱0.00</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

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
                            <option value="Cash">Cash</option>
                            <option value="Check">Check</option>
                        </select>
                    </div>
                    <div class="col-md-4"><label>Bank Name</label><input type="text" name="bank_name" class="form-control"></div>
                    <div class="col-md-4"><label>Check Date</label><input type="date" name="check_date" class="form-control"></div>
                    <div class="col-md-4"><label>Check Amount</label><input type="number" step="0.01" name="check_amount" class="form-control"></div>

                    <div class="col-md-4"><label>Total Cash Amount</label><input type="number" step="0.01" name="total_cash_amount" class="form-control" value="0.00"></div>
                    <div class="col-md-4"><label>Check Number</label><input type="text" name="check_number" class="form-control"></div>

                    <div class="col-md-12">
                        <small class="text-muted">All selected rows will be moved to Collections and removed from payments_list upon successful finalize.</small>
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

<!-- ✅ JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(function(){
    // Toggle buttons
    function toggleButtons(){
        let hasSelection = $('.row-checkbox:checked').length > 0;
        $('#proceedPaymentBtn, #updateCalcBtn, #deleteDuplicateBtn').prop('disabled', !hasSelection);
    }

    $('#select_all').on('change', function(){
        $('.row-checkbox').prop('checked', $(this).is(':checked'));
        toggleButtons();
        highlightDuplicates();
    });
    $(document).on('change', '.row-checkbox', function(){
        $('#select_all').prop('checked', $('.row-checkbox:checked').length === $('.row-checkbox').length);
        toggleButtons();
        highlightDuplicates();
    });

    // Highlight duplicates & paid
    function highlightDuplicates() {
        let seen = {};
        $('tbody tr').removeClass('table-warning');
        $('tbody tr').each(function(){
            let td_no = $(this).find('td:nth-child(5)').text().trim();
            let year = $(this).find('td:nth-child(7)').text().trim();
            let key = td_no+'_'+year;
            if(seen[key]){
                $(this).addClass('table-warning');
                seen[key].addClass('table-warning');
            } else seen[key] = $(this);
        });
    }
    highlightDuplicates();

    // Update Calculation
    $('#updateCalcBtn').on('click', function(){
        let ids = $('.row-checkbox:checked').map(function(){ return $(this).val(); }).get();
        if(ids.length===0) return alert('Select at least one record.');
        $.post('update_calculation.php',{ids: ids.join(',')},function(resp){
            if(resp.success){ alert('✅ Calculation updated successfully!'); location.reload(); }
            else alert('Error: '+(resp.message||'Unable to update.'));
        },'json').fail(function(xhr){ alert('Server error:\n'+xhr.responseText); });
    });

    // Proceed to Payment
    $('#proceedPaymentBtn').on('click', function(){
        let ids = $('.row-checkbox:checked').map(function(){ return $(this).val(); }).get();
        $('#selected_ids_input').val(ids.join(','));
        let $tbody = $('#selectedSummaryTable tbody');
        let $grand = $('#grandTotalCell');
        $tbody.empty();
        if(ids.length===0){ $('#summaryContainer').hide(); $grand.text('₱0.00'); return; }
        $('#summaryContainer').show();
        let grandTotal=0;
        ids.forEach(function(id){
            let row=$('tr[data-id="'+id+'"]');
            let td_no=row.find('td:nth-child(5)').text().trim();
            let year=row.find('td:nth-child(7)').text().trim();
            let discount=parseFloat(row.find('[data-field="discount"]').text().replace(/[₱,]/g,'')||0);
            let penalty=parseFloat(row.find('[data-field="penalty"]').text().replace(/[₱,]/g,'')||0);
            let total_due=parseFloat(row.find('[data-field="total_due"]').text().replace(/[₱,]/g,'')||0);
            grandTotal+=total_due;
            let rowClass = penalty>0?'table-danger':'';
            let overdueBadge = penalty>0?`<span class="badge bg-danger ms-2" title="₱${penalty.toFixed(2)} penalty">Overdue</span>`:'';
            $tbody.append(`<tr class="${rowClass}"><td>${td_no}</td><td>${year} ${overdueBadge}</td><td>₱${discount.toFixed(2)}</td><td>₱${penalty.toFixed(2)}</td><td>₱${total_due.toFixed(2)}</td></tr>`);
        });
        $grand.text('₱'+grandTotal.toFixed(2));
        $('input[name="total_cash_amount"]').val(grandTotal.toFixed(2));
    });

    // Inline edit saving
    $(document).on('blur','[contenteditable="true"]', function(){
        let td=$(this), row=td.closest('tr'), id=row.data('id'), field=td.data('field'), value=td.text().trim();
        if(['assessed_value','basic_tax','sef_tax','adjustments','discount','penalty','total_due'].includes(field)) value=value.replace(/[₱,]/g,'')||'0';
        $.post('payments_list_update_ajax.php',{id:id,field:field,value:value},function(resp){ if(resp.success && resp.normalized!==undefined) td.text(resp.normalized); },'json');
    });

    // Final Payment submit
    $('#finalPaymentForm').on('submit', function(e){
        e.preventDefault();
        let $btn=$(this).find('button[type="submit"]'); $btn.prop('disabled',true).text('Processing...');
        let data=$(this).serializeArray(); data.push({name:'selected_ids',value:$('#selected_ids_input').val()});
        $.post('final_payment.php', data, function(resp){
            $btn.prop('disabled',false).text('Finalize & Print OR');
            if(resp.success){ window.open('report_or.php?or_no='+encodeURIComponent(resp.or_no),'_blank'); location.reload(); }
            else alert('Error: '+(resp.message||'Unknown'));
        },'json').fail(function(xhr){ $btn.prop('disabled',false).text('Finalize & Print OR'); alert('Server error:\n'+xhr.responseText); });
    });

    // Delete duplicates
    $('#deleteDuplicateBtn').on('click', function(){
        let selectedRows=$('.row-checkbox:checked').closest('tr');
        if(selectedRows.length===0) return alert('Select at least one record.');
        let cannotDelete=[], deletableIds=[], duplicatesToDelete=[];
        selectedRows.each(function(){
            let td_no=$(this).find('td:nth-child(5)').text().trim();
            let year=$(this).find('td:nth-child(7)').text().trim();
            let status=$(this).find('td:nth-child(18)').text().trim().toLowerCase();
            if($(this).hasClass('table-warning')){
                if(status==='paid') cannotDelete.push(td_no+' - '+year);
                else { deletableIds.push($(this).data('id')); duplicatesToDelete.push(td_no+' - '+year); }
            }
        });
        if(cannotDelete.length>0) alert('Cannot delete paid duplicates:\n'+cannotDelete.join('\n'));
        if(deletableIds.length===0){ alert('No eligible duplicates selected for deletion.'); return; }
        if(!confirm('Are you sure you want to delete the following duplicates?\n'+duplicatesToDelete.join('\n'))) return;
        $.post('delete_duplicate.php',{ids: deletableIds.join(',')}, function(resp){
            if(resp.success){ alert('Selected duplicate(s) deleted successfully.'); location.reload(); }
            else alert('Error: '+(resp.message||'Failed')); 
        },'json');
    });
});
</script>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
