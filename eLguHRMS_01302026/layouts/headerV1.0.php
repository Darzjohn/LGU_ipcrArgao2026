<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= SYSTEM_NAME ?> - <?= ucfirst($_SESSION['role']) ?> Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary px-3">
  <a class="navbar-brand" href="#">RPTMS</a>
  <div class="ms-auto d-flex align-items-center text-white">
    <span class="me-3"><i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['name']) ?> (<?= ucfirst($_SESSION['role']) ?>)</span>
    <a href="/rptms/auth/logout.php" class="btn btn-sm btn-light">Logout</a>
  </div>
</nav>
<div class="container-fluid">
  <div class="row">
