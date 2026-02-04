<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars(SYSTEM_NAME ?? 'RPTMS') ?></title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  
  <style>
    body {
      background-color: #f8f9fa;
    }
    .navbar {
      background: linear-gradient(90deg, #198754, #145c32);
    }
    .navbar-brand, .nav-link, .navbar-text {
      color: #fff !important;
    }
    .dropdown-menu a:hover {
      background-color: #f1f1f1;
    }
  </style>
</head>

<body>
<nav class="navbar navbar-expand-lg shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="#">
      <i class="bi bi-building-check me-2"></i> <?= htmlspecialchars(SYSTEM_NAME ?? 'RPTMS') ?>
    </a>
    
    <div class="ms-auto d-flex align-items-center">
      <span class="navbar-text me-3">
        <i class="bi bi-person-circle me-1"></i>
        <?= htmlspecialchars($_SESSION['name'] ?? $_SESSION['username'] ?? 'User') ?>
        <small class="text-light opacity-75">(
          <?= htmlspecialchars(ucwords(str_replace('_', ' ', $_SESSION['role'] ?? ''))) ?>
        )</small>
      </span>

      <div class="dropdown">
        <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          Menu
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="../index.php"><i class="bi bi-house-door me-2"></i>Home</a></li>
          <li><a class="dropdown-item" href="../auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
        </ul>
      </div>
    </div>
  </div>
</nav>

<div class="container-fluid mt-4">
<div class="row">
