<?php
$role = $_SESSION['role'] ?? '';

$menu = [
    'admin' => [
        'Dashboard' => '/rptms/admin/dashboard.php',
        'User Management' => '/rptms/admin/admin_users.php',
        'System Logs' => '/rptms/admin/logs.php',
    ],
    'assessor' => [
        'Dashboard' => '/rptms/assessor/dashboard.php',
        'Property Assessment' => '/rptms/assessor/assessments.php',
        'Reports' => '/rptms/assessor/reports.php',
    ],
    'assessment_clerk' => [
        'Dashboard' => '/rptms/assessment_clerk/dashboard.php',
        'Encode Data' => '/rptms/assessment_clerk/encode.php',
        'Reports' => '/rptms/assessment_clerk/reports.php',
    ],
    'treasurer' => [
        'Dashboard' => '/rptms/treasurer/dashboard.php',
        'Collections' => '/rptms/treasurer/collections.php',
        'Disbursements' => '/rptms/treasurer/disbursements.php',
    ],
    'cashier' => [
        'Dashboard' => '/rptms/cashier/dashboard.php',
        'Payment Transactions' => '/rptms/cashier/payments.php',
        'Receipts' => '/rptms/cashier/receipts.php',
    ],
    'viewer' => [
        'Dashboard' => '/rptms/viewer/dashboard.php',
        'View Records' => '/rptms/viewer/view.php',
    ]
];

?>
<div class="col-md-2 bg-white shadow-sm vh-100 p-3">
  <h5 class="text-primary mb-3"><i class="bi bi-menu-button-wide"></i> Menu</h5>
  <ul class="nav flex-column">
    <?php foreach ($menu[$role] ?? [] as $name => $link): ?>
      <li class="nav-item mb-2">
        <a href="<?= htmlspecialchars($link) ?>" class="nav-link"><?= htmlspecialchars($name) ?></a>
      </li>
    <?php endforeach; ?>
  </ul>
</div>
<div class="col-md-10 p-4">
