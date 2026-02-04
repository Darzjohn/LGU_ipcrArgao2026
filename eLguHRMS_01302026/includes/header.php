<?php
if (!isset($settings)) {
  require_once __DIR__ . '/../config/settings.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($settings['system_name']) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    body {
      background: url('<?= $settings['background'] ?>') no-repeat center center fixed;
      background-size: cover;
      min-height: 100vh;
    }
    .logo {
      width: 60px;
      height: 60px;
      object-fit: contain;
    }
    header {
      background-color: rgba(255,255,255,0.9);
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      padding: 10px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .system-name {
      font-weight: 600;
      font-size: 1.2rem;
    }
  </style>
</head>
<body>

<header>
  <div class="d-flex align-items-center">
    <img src="<?= htmlspecialchars($settings['logo']) ?>" alt="Municipal Logo" class="logo me-2">
    <span class="system-name"><?= htmlspecialchars($settings['system_name']) ?></span>
  </div>
  <div>
    <span class="text-muted small"><?= htmlspecialchars($settings['municipality']) ?></span>
  </div>
</header>

<div class="container py-4">
