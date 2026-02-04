<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">RPTMS</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="properties.php">Properties</a></li>
        <li class="nav-item"><a class="nav-link" href="owners.php">Owners</a></li>
        <li class="nav-item"><a class="nav-link" href="assessments.php">Assessments</a></li>
      </ul>

      <?php if (!empty($_SESSION['user_id'])): ?>
      <ul class="navbar-nav ms-auto">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <img src="uploads/<?= htmlspecialchars($_SESSION['user_photo'] ?? 'default.png') ?>"
                 alt="profile" class="rounded-circle me-2" width="32" height="32">
            <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person-circle me-2"></i> Profile</a></li>
            <li><a class="dropdown-item" href="settings.php"><i class="bi bi-gear me-2"></i> Settings</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
          </ul>
        </li>
      </ul>
      <?php endif; ?>
    </div>
  </div>
</nav>
