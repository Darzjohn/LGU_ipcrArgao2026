<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/system_settings.php';

$token = $_GET['token'] ?? '';
$message = '';
$showForm = false;

if ($token) {
  $stmt = $mysqli->prepare("SELECT id, reset_expires FROM users WHERE reset_token=? LIMIT 1");
  $stmt->bind_param("s", $token);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    if (strtotime($user['reset_expires']) > time()) {
      $showForm = true;
      $userId = $user['id'];
    } else {
      $message = "⚠️ Reset link expired.";
    }
  } else {
    $message = "⚠️ Invalid reset token.";
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $userId = $_POST['user_id'];
  $newPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

  $stmt = $mysqli->prepare("UPDATE users SET password=?, reset_token=NULL, reset_expires=NULL WHERE id=?");
  $stmt->bind_param("si", $newPassword, $userId);
  $stmt->execute();

  $message = "✅ Password successfully updated! You can now <a href='login.php'>log in</a>.";
  $showForm = false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password - RPTMS</title>
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
    .reset-card {
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

<div class="reset-card">
  <img src="../uploads/logo.png" alt="Logo" width="80" class="mb-3">
  <h4 class="fw-bold mb-3">Reset Password</h4>

  <?php if ($message): ?>
    <div class="alert alert-info"><?= $message ?></div>
  <?php endif; ?>

  <?php if ($showForm): ?>
    <form method="POST">
      <input type="hidden" name="user_id" value="<?= $userId ?>">
      <div class="mb-3">
        <label class="form-label fw-semibold">New Password</label>
        <input type="password" name="password" class="form-control" required minlength="6">
      </div>
      <button type="submit" class="btn btn-success w-100">Update Password</button>
    </form>
  <?php endif; ?>

</div>

</body>
</html>
