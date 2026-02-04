<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars(SYSTEM_NAME ?? 'RPTMS') ?></title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    body {
      background: linear-gradient(135deg, #0f1624, #1b2735);
      color: #e5e5e5;
      overflow-x: hidden;
      font-family: 'Inter', sans-serif;
    }

    /* Header */
    .rptms-header {
      backdrop-filter: blur(18px);
      background: rgba(255,255,255,0.08);
      border-bottom: 1px solid rgba(255,255,255,0.15);
      box-shadow: 0 4px 20px rgba(0,0,0,0.4);
      z-index: 2000;
      position: fixed;
      width: 100%;
      top: 0;
      left: 0;
    }
    .rptms-header .title {
      font-size: 1.4rem;
      font-weight: 700;
      text-shadow: 0 0 10px rgba(255,255,255,0.5);
    }

    /* Header user & dropdown */
    .rptms-header .user-dropdown {
      position: relative;
      z-index: 3000; /* ensures dropdown menu appears above sidebar/content */
    }
    .rptms-header .dropdown-menu {
      z-index: 3000;
      min-width: 150px;
      background: rgba(255,255,255,0.08);
      backdrop-filter: blur(12px);
      border: 1px solid rgba(255,255,255,0.2);
      box-shadow: 0 4px 15px rgba(0,0,0,0.4);
    }
    .rptms-header .dropdown-item {
      color: #e5e5e5;
    }
    .rptms-header .dropdown-item:hover {
      background: rgba(0,123,255,0.6);
      color: #fff;
    }

    /* Sidebar */
    .rptms-sidebar {
      height: calc(100vh - 70px);
      background: rgba(255,255,255,0.06);
      backdrop-filter: blur(20px);
      border-right: 1px solid rgba(255,255,255,0.15);
      box-shadow: 4px 0 25px rgba(0,0,0,0.3);
      padding-top: 20px;
      position: sticky;
      top: 70px;
      overflow-y: auto;
    }
    .rptms-sidebar .nav-link {
      color: #d0d0d0;
      padding: 12px 18px;
      font-weight: 500;
      border-radius: 12px;
      margin-bottom: 6px;
      transition: 0.3s;
    }
    .rptms-sidebar .nav-link:hover {
      background: rgba(255,255,255,0.1);
      color: #ffffff;
      transform: translateX(3px);
    }
    .rptms-sidebar .nav-link.active {
      background: linear-gradient(135deg, #0077ff, #00c6ff);
      color: #fff !important;
      box-shadow: 0 4px 12px rgba(0,128,255,0.4);
    }

    /* Footer */
    .rptms-footer {
      background: rgba(255,255,255,0.08);
      backdrop-filter: blur(12px);
      border-top: 1px solid rgba(255,255,255,0.15);
      color: #d8d8d8;
      font-size: 0.9rem;
      box-shadow: 0 -4px 20px rgba(0,0,0,0.4);
      margin-top: 40px;
      text-align: center;
      padding: 12px 0;
    }

    /* Main content offset for fixed header */
    .main-content {
      padding-top: 70px;
    }
  </style>
</head>
<body>

<header class="rptms-header py-3 px-4 d-flex justify-content-between align-items-center">
  <div class="title"><?= htmlspecialchars(SYSTEM_NAME ?? 'RPTMS') ?></div>
  <div class="user-dropdown dropdown">
    <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
      <i class="bi bi-person-circle me-1"></i>
      <?= htmlspecialchars($_SESSION['name'] ?? $_SESSION['username'] ?? 'User') ?>
    </button>
    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
      <li><a class="dropdown-item py-2" href="../index.php"><i class="bi bi-house-door me-2"></i> Home</a></li>
      <li><a class="dropdown-item py-2 text-danger" href="../auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
    </ul>
  </div>
</header>

<div class="container-fluid main-content">
  <div class="row g-0">
    <!-- Sidebar and content go here -->
