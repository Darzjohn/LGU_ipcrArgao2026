<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - RPTMS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4 bg-light">
  <div class="container">
    <h1>Welcome, <?= htmlspecialchars($_SESSION['name']) ?>!</h1>
    <p>Role: <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $_SESSION['role']))); ?></p>
    <hr>
    <div class="mb-3">
      <a href="auth/logout.php" class="btn btn-danger">Logout</a>
    </div>
    <p>This is your RPTMS dashboard.</p>
  </div>
</body>
</html>
