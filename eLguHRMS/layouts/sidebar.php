<?php
// ===== sidebar.php =====
$role = $_SESSION['role'] ?? 'viewer';
?>
<div class="col-md-3 col-lg-2 mb-4">
  <div class="rptms-sidebar shadow-lg p-3 position-sticky top-0">
    <nav class="nav flex-column">
      <a href="dashboard.php" class="nav-link active"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>

      <?php if ($role === 'admin'): ?>
        <a href="users.php" class="nav-link"><i class="bi bi-people me-2"></i> Manage Users</a>
        <a href="signatories.php" class="nav-link"><i class="bi bi-gear me-2"></i> Signatories Settings</a>
        <a href="system_settings.php" class="nav-link"><i class="bi bi-gear me-2"></i> System Settings</a>
        <a href="employees.php" class="nav-link"><i class="bi bi-person-badge me-2"></i> Employees</a>
        <a href="service_records.php" class="nav-link"><i class="bi bi-person-badge me-2"></i> Service Records</a>
        <a href="departments.php" class="nav-link"><i class="bi bi-person-badge me-2"></i> Departments</a>
        <a href="positions.php" class="nav-link"><i class="bi bi-person-badge me-2"></i> Positions</a>
        <a href="employment_status.php" class="nav-link"><i class="bi bi-person-badge me-2"></i> Employment Status</a>

         <a href="ipcr_doris.php" class="nav-link"><i class="bi bi-person-badge me-2"></i> IPCR</a>


      <?php elseif ($role === 'hr' || $role === 'hr_staff'): ?>
        <a href="employees.php" class="nav-link"><i class="bi bi-person-badge me-2"></i> Employees</a>
        <a href="service_records.php" class="nav-link"><i class="bi bi-person-badge me-2"></i> Service Records</a>
        <a href="departments.php" class="nav-link"><i class="bi bi-person-badge me-2"></i> Departments</a>
        <a href="positions.php" class="nav-link"><i class="bi bi-person-badge me-2"></i> Positions</a>
        <a href="employment_status.php" class="nav-link"><i class="bi bi-person-badge me-2"></i> Employment Status</a>


       <?php elseif ($role === 'employee'): ?>
        <a href="employee_info.php" class="nav-link"><i class="bi bi-person-badge me-2"></i> Employee Information</a>
        <a href="users.php" class="nav-link"><i class="bi bi-people me-2"></i> Manage Users</a>
        <a href="signatories.php" class="nav-link"><i class="bi bi-gear me-2"></i> Signatories Settings</a>
        <a href="system_settings.php" class="nav-link"><i class="bi bi-gear me-2"></i> System Settings</a>

       

      <?php elseif ($role === 'assessor' || $role === 'assessment_clerk'): ?>
        <a href="owners.php" class="nav-link"><i class="bi bi-house-door me-2"></i> Owners</a>
        <a href="properties.php" class="nav-link"><i class="bi bi-house-door me-2"></i> Property Records</a>
        <a href="assessments.php" class="nav-link"><i class="bi bi-clipboard-check me-2"></i> Assessments</a>
        <a href="tax_billsall.php" class="nav-link"><i class="bi bi-clipboard-check me-2"></i> Tax Bills / Print NATB</a>
        <a href="ctc_list.php" class="nav-link"><i class="bi bi-house-door me-2"></i> CTC Individual</a>
        <a href="ctccorp_list.php" class="nav-link"><i class="bi bi-house-door me-2"></i> CTC Corporation</a>
        <a href="form51_list.php" class="nav-link"><i class="bi bi-clipboard-check me-2"></i> Form 51 - General Collection</a>
        <a href="form58_list.php" class="nav-link"><i class="bi bi-clipboard-check me-2"></i> Form 58 - Burial Permit</a>
        <a href="payments_list.php" class="nav-link"><i class="bi bi-clipboard-check me-2"></i> Form 56 - RPT Payments</a>
        <a href="collection.php" class="nav-link"><i class="bi bi-cash-coin me-2"></i> Collections</a>

      <?php elseif ($role === 'treasurer' || $role === 'cashier'): ?>
        <a href="ctc_list.php" class="nav-link"><i class="bi bi-house-door me-2"></i> CTC Individual</a>
        <a href="ctccorp_list.php" class="nav-link"><i class="bi bi-house-door me-2"></i> CTC Corporation</a>
        <a href="form51_list.php" class="nav-link"><i class="bi bi-clipboard-check me-2"></i> Form 51 - General Collection</a>
        <a href="form58_list.php" class="nav-link"><i class="bi bi-clipboard-check me-2"></i> Form 58 - Burial Permit</a>
        <a href="payments_list.php" class="nav-link"><i class="bi bi-clipboard-check me-2"></i> Form 56 - RPT Payments</a>
        <a href="collection.php" class="nav-link"><i class="bi bi-cash-coin me-2"></i> RPT Collections</a>
        <a href="remittance_list.php" class="nav-link"><i class="bi bi-clipboard-check me-2"></i> Remittances (CTC's, Form51, Form58)</a>
        <a href="reports.php" class="nav-link"><i class="bi bi-file-earmark-bar-graph me-2"></i> Reports</a>
        <a href="ngas_settings.php" class="nav-link"><i class="bi bi-gear me-2"></i> NGAS Settings</a>
        <a href="bank_transactions.php" class="nav-link"><i class="bi bi-clipboard-check me-2"></i> Bank Transactions</a>
        <a href="bank_reconciliation.php" class="nav-link"><i class="bi bi-clipboard-check me-2"></i> Bank Reconciliation</a>
        <a href="checks_issued.php" class="nav-link"><i class="bi bi-clipboard-check me-2"></i> Records on Check Issued</a>
        <a href="accounts.php" class="nav-link"><i class="bi bi-gear me-2"></i> Bank Accounts Setup</a>
        <a href="fundsource.php" class="nav-link"><i class="bi bi-gear me-2"></i> Fund Source Setup</a>

      <?php elseif ($role === 'viewer'): ?>
        <a href="view_data.php" class="nav-link"><i class="bi bi-eye me-2"></i> View Data</a>

      <?php endif; ?>
    </nav>
  </div>
</div>

<div class="col-md-9 col-lg-10">
