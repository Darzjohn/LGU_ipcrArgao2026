<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<h2 class="mb-4"><i class="bi bi-file-earmark-text"></i> Assessment Clerk and Staff Dashboard</h2>
<p>Welcome back, <strong><?= htmlspecialchars($_SESSION['name'] ?? $_SESSION['username']) ?></strong>!</p>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
