<?php
require_once '../db.php';
require_once '../auth/session_check.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>RPTMS Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="../includes/global.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="../includes/global.js"></script>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar d-flex flex-column justify-content-between">
  <div>
    <div class="text-center py-4 border-bottom border-light">
      <img src="../uploads/municipal_logo.png" alt="Logo" class="logo-circle">
      <h6 class="mt-2 mb-0 text-light"><?= htmlspecialchars($municipality_name ?? 'Municipality'); ?></h6>
      <small class="text-secondary">Real Property Tax System</small>
    </div>
    <ul class="mt-3">
      <li><a href="dashboard.php" class="active"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
      <li><a href="properties.php"><i class="bi bi-building me-2"></i> Properties</a></li>
      <li><a href="owners.php"><i class="bi bi-people me-2"></i> Owners</a></li>
      <li><a href="assessments.php"><i class="bi bi-file-earmark-text me-2"></i> Assessments</a></li>
      <li><a href="tax_billsall.php"><i class="bi bi-cash-stack me-2"></i> Tax Bills</a></li>
      <li><a href="payments_list.php"><i class="bi bi-receipt me-2"></i> Payments</a></li>
    </ul>
  </div>
  <div class="p-3 border-top border-secondary">
    <a href="../logout.php" class="text-danger text-decoration-none d-block">
      <i class="bi bi-box-arrow-right me-2"></i> Logout
    </a>
  </div>
</div>

<!-- Main Content -->
<div class="main-content">
  <!-- Titlebar -->
  <div class="titlebar d-flex justify-content-between align-items-center">
    <h5><i class="bi bi-speedometer2"></i> Dashboard Overview</h5>
    <button class="btn btn-outline-primary btn-sm" id="menu-toggle">
      <i class="bi bi-list"></i>
    </button>
  </div>

  <!-- Dashboard Cards -->
  <div class="container-fluid mt-4">
    <div class="row g-3">
      <div class="col-md-3">
        <div class="card text-center shadow-sm">
          <h6>Total Properties</h6>
          <?php
          $count_props = $mysqli->query("SELECT COUNT(*) AS total FROM properties")->fetch_assoc()['total'] ?? 0;
          ?>
          <h2 class="text-primary fw-bold"><?= number_format($count_props) ?></h2>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card text-center shadow-sm">
          <h6>Total Owners</h6>
          <?php
          $count_owners = $mysqli->query("SELECT COUNT(*) AS total FROM owners")->fetch_assoc()['total'] ?? 0;
          ?>
          <h2 class="text-success fw-bold"><?= number_format($count_owners) ?></h2>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card text-center shadow-sm">
          <h6>Unpaid Tax Bills</h6>
          <?php
          $count_unpaid = $mysqli->query("SELECT COUNT(*) AS total FROM tax_bills WHERE status IS NULL OR status != 'Paid'")->fetch_assoc()['total'] ?? 0;
          ?>
          <h2 class="text-danger fw-bold"><?= number_format($count_unpaid) ?></h2>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card text-center shadow-sm">
          <h6>Total Collected</h6>
          <?php
          $sum_payments = $mysqli->query("SELECT SUM(total_amount_paid) AS total FROM payments_list")->fetch_assoc()['total'] ?? 0;
          ?>
          <h2 class="text-info fw-bold">₱<?= number_format($sum_payments, 2) ?></h2>
        </div>
      </div>
    </div>

    <!-- Chart Section (optional for later) -->
    <div class="card mt-4">
      <h6>Monthly Collection Overview</h6>
      <canvas id="collectionChart" height="90"></canvas>
    </div>
  </div>
</div>

<!-- Footer -->
<div class="footer">
  &copy; <?= date('Y') ?> Real Property Tax Management System (RPTMS V12) — All Rights Reserved.
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function () {
  // Example chart (placeholder data)
  const ctx = document.getElementById('collectionChart').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
      datasets: [{
        label: 'Monthly Collection (₱)',
        data: [12000, 15000, 18000, 22000, 20000, 25000, 27000, 29000, 31000, 33000, 35000, 37000],
        backgroundColor: 'rgba(0, 86, 179, 0.6)',
        borderRadius: 8
      }]
    },
    options: {
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true } }
    }
  });
});
</script>

</body>
</html>
