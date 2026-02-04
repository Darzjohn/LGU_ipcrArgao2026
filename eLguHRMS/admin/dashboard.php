<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="content-area p-4">
  <div class="container-fluid">
    <div class="glass-card">
      <h2 class="mb-3"><i class="bi bi-speedometer2"></i> Admin Dashboard</h2>
      <p>Welcome back, <strong><?= htmlspecialchars($_SESSION['name'] ?? $_SESSION['username']) ?></strong>!</p>
      <p>Use the sidebar to navigate modules efficiently. The dashboard provides an overview of your LGU operations.</p>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
