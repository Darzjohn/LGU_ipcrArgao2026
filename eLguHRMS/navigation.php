<?php
// navigation.php
// Dynamic Back + Home buttons

// Detect current page
$currentPage = basename($_SERVER['PHP_SELF']);

// Decide the correct Home link (default: index.php)
switch ($currentPage) {
    case 'owners.php':
        $homeLink = 'owners.php';
        $title = 'Owners';
        break;
    case 'properties.php':
        $homeLink = 'properties.php';
        $title = 'Properties';
        break;
    case 'assessments.php':
        $homeLink = 'assessments.php';
        $title = 'Assessments';
        break;
    default:
        $homeLink = 'index.php';
        $title = 'Dashboard';
        break;
}
?>

<div class="card mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span class="fw-bold"><?= htmlspecialchars($title) ?></span>
    <div class="d-flex gap-2">
      <!-- Back Button -->
      <a href="#"
         onclick="if (window.history.length > 1) { history.back(); } else { window.location.href='<?= $homeLink ?>'; }"
         class="btn btn-outline-secondary btn-sm d-flex align-items-center">
         <i class="bi bi-arrow-left me-1"></i> Back
      </a>

      <!-- Home Button -->
      <a href="<?= $homeLink ?>" class="btn btn-outline-primary btn-sm d-flex align-items-center">
        <i class="bi bi-house-door me-1"></i> Home
      </a>
    </div>
  </div>
</div>
