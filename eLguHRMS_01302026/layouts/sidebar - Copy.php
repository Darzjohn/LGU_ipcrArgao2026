<?php
// detect current role for sidebar menu options
$role = $_SESSION['role'] ?? 'viewer';
?>
<div class="col-md-3 col-lg-2 mb-4">
  <div class="list-group shadow-sm">
    <a href="dashboard.php" class="list-group-item list-group-item-action active">
      <i class="bi bi-speedometer2 me-2"></i> Dashboard
    </a>

    <?php if ($role === 'admin'): ?>
      <a href="users.php" class="list-group-item list-group-item-action">
        <i class="bi bi-people me-2"></i> Manage Users
      </a>
      <a href="signatories.php" class="list-group-item list-group-item-action">
        <i class="bi bi-gear me-2"></i> Signatories Settings
      </a>
      <a href="system_settings.php" class="list-group-item list-group-item-action">
        <i class="bi bi-gear me-2"></i> System Settings
      </a>
        <a href="ngas_settings.php" class="list-group-item list-group-item-action">
        <i class="bi bi-gear me-2"></i> NGAS Settings
      </a>
       <a href="owners.php" class="list-group-item list-group-item-action">
        <i class="bi bi-house-door me-2"></i> Owners
      </a>
      </a>
      <a href="properties.php" class="list-group-item list-group-item-action">
        <i class="bi bi-house-door me-2"></i> Property Records
      </a>
      <a href="assessments.php" class="list-group-item list-group-item-action">
        <i class="bi bi-clipboard-check me-2"></i> Assessments
      </a>
      </a>
      <a href="tax_billsall.php" class="list-group-item list-group-item-action">
        <i class="bi bi-clipboard-check me-2"></i> Tax Bills / Print NATB
      </a>

      <a href="payments_list.php" class="list-group-item list-group-item-action">
        <i class="bi bi-clipboard-check me-2"></i> For Payments
      </a>
      <a href="collection.php" class="list-group-item list-group-item-action">
        <i class="bi bi-cash-coin me-2"></i> Collections
      </a>

    <?php elseif ($role === 'assessor' || $role === 'assessment_clerk'): ?>
      <a href="owners.php" class="list-group-item list-group-item-action">
        <i class="bi bi-house-door me-2"></i> Owners
      </a>
      <a href="properties.php" class="list-group-item list-group-item-action">
        <i class="bi bi-house-door me-2"></i> Property Records
      </a>
      <a href="assessments.php" class="list-group-item list-group-item-action">
        <i class="bi bi-clipboard-check me-2"></i> Assessments
      </a>
      </a>
      <a href="tax_billsall.php" class="list-group-item list-group-item-action">
        <i class="bi bi-clipboard-check me-2"></i> Tax Bills / Print NATB
      </a>
      <a href="ctc_list.php" class="list-group-item list-group-item-action">
        <i class="bi bi-house-door me-2"></i> CTC Individual
      </a>
    <a href="ctccorp_list.php" class="list-group-item list-group-item-action">
        <i class="bi bi-house-door me-2"></i> CTC Corporation
      </a>
      <a href="form51_list.php" class="list-group-item list-group-item-action">
        <i class="bi bi-clipboard-check me-2"></i> Form 51 - General Collection 
      </a>
      <a href="form58_list.php" class="list-group-item list-group-item-action">
        <i class="bi bi-clipboard-check me-2"></i> Form 58 - Burial Permit 
      </a>

        <a href="payments_list.php" class="list-group-item list-group-item-action">
        <i class="bi bi-clipboard-check me-2"></i> Form 56 - RPT Payments
      </a>
      <a href="collection.php" class="list-group-item list-group-item-action">
        <i class="bi bi-cash-coin me-2"></i> Collections
      </a>

    <?php elseif ($role === 'treasurer' || $role === 'cashier'): ?>
      <a href="ctc_list.php" class="list-group-item list-group-item-action">
        <i class="bi bi-house-door me-2"></i> CTC Individual
      </a>
    <a href="ctccorp_list.php" class="list-group-item list-group-item-action">
        <i class="bi bi-house-door me-2"></i> CTC Corporation
      </a>
      <a href="form51_list.php" class="list-group-item list-group-item-action">
        <i class="bi bi-clipboard-check me-2"></i> Form 51 - General Collection 
      </a>
      <a href="form58_list.php" class="list-group-item list-group-item-action">
        <i class="bi bi-clipboard-check me-2"></i> Form 58 - Burial Permit 
      </a>
      <!--   <a href="assessments.php" class="list-group-item list-group-item-action">
        <i class="bi bi-clipboard-check me-2"></i> Assessments
      </a>
      </a>
      <a href="tax_billsall.php" class="list-group-item list-group-item-action">
        <i class="bi bi-clipboard-check me-2"></i> Tax Bills / Print NATB
      </a> -->

      <a href="payments_list.php" class="list-group-item list-group-item-action">
        <i class="bi bi-clipboard-check me-2"></i> Form 56 - RPT Payments
      </a>
      <a href="collection.php" class="list-group-item list-group-item-action">
        <i class="bi bi-cash-coin me-2"></i> RPT Collections
      </a>
      </a>
      <a href="remittance_list.php" class="list-group-item list-group-item-action">
        <i class="bi bi-clipboard-check me-2"></i> Remittances (CTC's,Form51,Form58)
      </a>
      <a href="reports.php" class="list-group-item list-group-item-action">
        <i class="bi bi-file-earmark-bar-graph me-2"></i> Reports
      </a>
      <a href="ngas_settings.php" class="list-group-item list-group-item-action">
        <i class="bi bi-gear me-2"></i> NGAS Settings
      </a>
      <a href="bank_transactions.php" class="list-group-item list-group-item-action">
        <i class="bi bi-clipboard-check me-2"></i> Bank Trasanctions
      </a>
      <a href="bank_reconciliation.php" class="list-group-item list-group-item-action">
        <i class="bi bi-clipboard-check me-2"></i> Bank Reconciliation
      </a>
      <a href="checks_issued.php" class="list-group-item list-group-item-action">
        <i class="bi bi-clipboard-check me-2"></i> Records on Check Issued
      </a>
      <a href="accounts.php" class="list-group-item list-group-item-action">
        <i class="bi bi-gear me-2"></i> Bank Accounts Setup
      </a>
      <a href="fundsource.php" class="list-group-item list-group-item-action">
        <i class="bi bi-gear me-2"></i> Fund Source Setup
      </a>

    <?php elseif ($role === 'viewer'): ?>
      <a href="view_data.php" class="list-group-item list-group-item-action">
        <i class="bi bi-eye me-2"></i> View Data
      </a>
    <?php endif; ?>
  </div>
</div>

<div class="col-md-9 col-lg-10">
