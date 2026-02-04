<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/system_settings.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username']);

  $stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $token = bin2hex(random_bytes(32));
    $expiry = date('Y-m-d H:i:s', time() + 3600); // valid 1 hour

    $stmt2 = $mysqli->prepare("UPDATE users SET reset_token=?, reset_expires=? WHERE id=?");
    $stmt2->bind_param("ssi", $token, $expiry, $user['id']);
    $stmt2->execute();

    $link = "reset_password.php?token=" . $token;
    $message = "✅ Password reset link generated!<br>For demo: <a href='$link'>Click here to reset password</a>";
  } else {
    $message = "⚠️ No user found with that username.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Forgot Password - RPTMS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/css/rptms-theme.css" rel="stylesheet">
  <style>
    body {
      background: url('../uploads/background.jpg') no-repeat center center fixed;
      background-size: cover;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      backdrop-filter: blur(3px);
    }
    .forgot-card {
      width: 380px;
      background: rgba(255, 255, 255, 0.95);
      border-radius: 1rem;
      box-shadow: 0 4px 20px rgba(0,0,0,0.2);
      text-align: center;
      padding: 2rem;
    }
  </style>
</head>
<body>

<div class="forgot-card">
  <img src="../uploads/logo.png" alt="Logo" width="80" class="mb-3">
  <h4 class="fw-bold mb-3">Forgot Password</h4>

  <?php if ($message): ?>
    <div class="alert alert-info"><?= $message ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="mb-3">
      <label class="form-label fw-semibold">Enter your username</label>
      <input type="text" name="username" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-success w-100">Generate Reset Link</button>
  </form>

  <a href="login.php" class="d-block mt-3">← Back to Login</a>
</div>

</body>
</html>
