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
      <a href="settings.php" class="list-group-item list-group-item-action">
        <i class="bi bi-gear me-2"></i> System Settings
      </a>

    <?php elseif ($role === 'assessor' || $role === 'assessment_clerk'): ?>
      <a href="properties.php" class="list-group-item list-group-item-action">
        <i class="bi bi-house-door me-2"></i> Property Records
      </a>
      <a href="assessments.php" class="list-group-item list-group-item-action">
        <i class="bi bi-clipboard-check me-2"></i> Assessments
      </a>

    <?php elseif ($role === 'treasurer' || $role === 'cashier'): ?>
      <a href="collections.php" class="list-group-item list-group-item-action">
        <i class="bi bi-cash-coin me-2"></i> Collections
      </a>
      <a href="reports.php" class="list-group-item list-group-item-action">
        <i class="bi bi-file-earmark-bar-graph me-2"></i> Reports
      </a>

    <?php elseif ($role === 'viewer'): ?>
      <a href="view_data.php" class="list-group-item list-group-item-action">
        <i class="bi bi-eye me-2"></i> View Data
      </a>
    <?php endif; ?>
  </div>
</div>

<div class="col-md-9 col-lg-10">
