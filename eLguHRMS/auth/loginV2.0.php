<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: ../dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $mysqli->prepare("SELECT id, username, password, name, role FROM users WHERE username=? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            // ✅ Set all necessary session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            header("Location: ../dashboard.php");
            exit;
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "User not found.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>RPTMS Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">
  <div class="card shadow-lg" style="width: 380px;">
    <div class="card-header text-center bg-success text-white">
      <img src="../uploads/logo.png" alt="Logo" width="70" class="rounded-circle mb-2">
      <h4 class="fw-bold">RPTMS Login</h4>
    </div>
    <div class="card-body">
      <?php if ($error): ?>
        <div class="alert alert-danger text-center py-2"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" autocomplete="off">
        <div class="mb-3">
          <label for="username" class="form-label fw-semibold">Username</label>
          <input type="text" name="username" id="username" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
          <label for="password" class="form-label fw-semibold">Password</label>
          <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success w-100">Login</button>
      </form>
    </div>
    <div class="card-footer text-center small text-muted">
      © <?= date('Y') ?> RPTMS | All Rights Reserved
    </div>
  </div>
</body>
</html>
