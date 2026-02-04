<?php
require_once 'db.php';
require_once 'auth/session_check.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Payments & Tax Bills</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body { background-color: #f8f9fa; }
    .container-box {
      margin: 30px auto;
      max-width: 1300px;
      background: #fff;
      border-radius: 10px;
      padding: 25px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body>

<div class="container-box">
  <ul class="nav nav-tabs mb-4" id="paymentTabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="unpaid-tab" data-bs-toggle="tab" data-bs-target="#unpaid" type="button" role="tab">
        <i class="bi bi-cash-coin"></i> Unpaid Bills
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab">
        <i class="bi bi-receipt"></i> Payment History
      </button>
    </li>
  </ul>

  <div class="tab-content" id="paymentTabsContent">
    <!-- Unpaid Bills -->
    <div class="tab-pane fade show active" id="unpaid" role="tabpanel">
      <div class="d-flex justify-content-between mb-3">
        <h5 class="fw-bold text-primary">Unpaid Tax Bills</h5>
        <button id="pay-selected" class="btn btn-success btn-sm">
          <i class="bi bi-cash-stack"></i> Pay Selected
        </button>
      </div>

      <table id="billsTable" class="table table-striped table-bordered">
        <thead class="table-dark">
          <tr>
            <th><input type="checkbox" id="select-all"></th>
            <th>Tax Year</th>
            <th>TD No</th>
            <th>Owner</th>
            <th>Barangay</th>
            <th>Classification</th>
            <th>Basic Tax</th>
            <th>SEF Tax</th>
            <th>Adjustments</th>
            <th>Total Due</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $query = "
            SELECT 
              tb.id, tb.tax_year, tb.status,
              a.basic_tax, a.sef_tax, a.adjustments,
              p.td_no, p.location, p.barangay, p.classification,
              o.name AS owner_name
            FROM tax_bills tb
            JOIN assessments a ON a.id = tb.assessment_id
            JOIN properties p ON p.id = a.property_id
            LEFT JOIN owners o ON o.id = p.owner_id
            WHERE tb.status != 'Paid' OR tb.status IS NULL
            ORDER BY tb.tax_year DESC
          ";
          $result = $mysqli->query($query);
          while ($row = $result->fetch_assoc()):
            $total_due = $row['basic_tax'] + $row['sef_tax'] + $row['adjustments'];
          ?>
          <tr>
            <td><input type="checkbox" class="select-row" value="<?= $row['id'] ?>"></td>
            <td><?= htmlspecialchars($row['tax_year']) ?></td>
            <td><?= htmlspecialchars($row['td_no']) ?></td>
            <td><?= htmlspecialchars($row['owner_name']) ?></td>
            <td><?= htmlspecialchars($row['barangay']) ?></td>
            <td><?= htmlspecialchars($row['classification']) ?></td>
            <td><?= number_format($row['basic_tax'], 2) ?></td>
            <td><?= number_format($row['sef_tax'], 2) ?></td>
            <td><?= number_format($row['adjustments'], 2) ?></td>
            <td><strong><?= number_format($total_due, 2) ?></strong></td>
            <td><span class="badge bg-warning text-dark"><?= htmlspecialchars($row['status'] ?? 'Unpaid') ?></span></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>

    <!-- Payment History -->
    <div class="tab-pane fade" id="history" role="tabpanel">
      <h5 class="fw-bold text-success mb-3">Payment History</h5>
      <table id="historyTable" class="table table-striped table-bordered">
        <thead class="table-dark">
          <tr>
            <th>OR No</th>
            <th>Tax Year</th>
            <th>Owner</th>
            <th>Barangay</th>
            <th>Total Amount Paid</th>
            <th>Payment Date</th>
            <th>Payor</th>
            <th>Processed By</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $query2 = "
            SELECT or_no, tax_year, owner_name, barangay, total_amount_paid, payment_date, payor_name, processed_by
            FROM payments_list
            ORDER BY payment_date DESC
          ";
          $result2 = $mysqli->query($query2);
          while ($row = $result2->fetch_assoc()):
          ?>
          <tr>
            <td><strong><?= htmlspecialchars($row['or_no']) ?></strong></td>
            <td><?= htmlspecialchars($row['tax_year']) ?></td>
            <td><?= htmlspecialchars($row['owner_name']) ?></td>
            <td><?= htmlspecialchars($row['barangay']) ?></td>
            <td><strong><?= number_format($row['total_amount_paid'], 2) ?></strong></td>
            <td><?= date('Y-m-d H:i', strtotime($row['payment_date'])) ?></td>
            <td><?= htmlspecialchars($row['payor_name']) ?></td>
            <td><?= htmlspecialchars($row['processed_by']) ?></td>
            <td class="text-center">
              <a href="report_or.php?or_no=<?= urlencode($row['or_no']) ?>" 
                 target="_blank" class="btn btn-outline-primary btn-sm">
                 <i class="bi bi-printer"></i> Print OR
              </a>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
  $('#billsTable, #historyTable').DataTable();

  $('#select-all').on('click', function() {
    $('.select-row').prop('checked', this.checked);
  });

  $('#pay-selected').on('click', function() {
    const selectedIds = $('.select-row:checked').map(function() {
      return $(this).val();
    }).get();

    if (selectedIds.length === 0) {
      Swal.fire('No Selection', 'Please select at least one tax bill to pay.', 'warning');
      return;
    }

    Swal.fire({
      title: "Enter Payment Details",
      html: `
        <input id="payor_name" class="swal2-input" placeholder="Payor Name">
        <input id="processed_by" class="swal2-input" placeholder="Processed By" value="<?= $_SESSION['username'] ?? 'Admin' ?>">
        <input id="payment_date" type="datetime-local" class="swal2-input">
      `,
      confirmButtonText: "Confirm Payment",
      showCancelButton: true,
      focusConfirm: false,
      preConfirm: () => {
        return {
          payor_name: document.getElementById('payor_name').value.trim(),
          processed_by: document.getElementById('processed_by').value.trim(),
          payment_date: document.getElementById('payment_date').value
        };
      }
    }).then(result => {
      if (result.isConfirmed) {
        const { payor_name, processed_by, payment_date } = result.value;
        if (!payor_name || !processed_by || !payment_date) {
          Swal.fire('Incomplete Info', 'Please fill out all fields.', 'error');
          return;
        }

        $.ajax({
          url: 'api/pay_selected.php',
          method: 'POST',
          data: {
            selected_ids: selectedIds,
            payor_name,
            processed_by,
            payment_date
          },
          dataType: 'json',
          success: function(response) {
            Swal.fire(response.success ? 'Success' : 'Error', response.message, response.success ? 'success' : 'error')
              .then(() => {
                if (response.success) location.reload();
              });
          },
          error: function() {
            Swal.fire('Error', 'Server error occurred.', 'error');
          }
        });
      }
    });
  });
});
</script>

</body>
</html>
