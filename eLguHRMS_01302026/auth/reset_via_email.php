<?php
// auth/reset_via_email.php
session_start();
require_once __DIR__ . '/../config/db.php';

$token = $_GET['token'] ?? '';
$message = '';

if (!$token) {
    die("❌ Invalid reset link.");
}

// ✅ Check token validity
$stmt = $mysqli->prepare("SELECT username, reset_expires FROM users WHERE reset_token=?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("❌ Invalid or expired token.");
}

$user = $result->fetch_assoc();
if (strtotime($user['reset_expires']) < time()) {
    die("⏰ This reset link has expired.");
}

// ✅ If form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new = trim($_POST['new_password']);
    $confirm = trim($_POST['confirm_password']);

    if ($new && $confirm && $new === $confirm) {
        $hashed = password_hash($new, PASSWORD_BCRYPT);

        $stmt = $mysqli->prepare("UPDATE users SET password=?, reset_token=NULL, reset_expires=NULL WHERE reset_token=?");
        $stmt->bind_param("ss", $hashed, $token);
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>✅ Password has been reset! You can now <a href='../login.php'>login</a>.</div>";
        } else {
            $message = "<div class='alert alert-danger'>❌ Error updating password.</div>";
        }
    } else {
        $message = "<div class='alert alert-warning'>⚠️ Passwords must match and not be empty.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reset Password - RPTMS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow border-0 mx-auto" style="max-width: 500px;">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">🔑 Set New Password</h5>
        </div>
        <div class="card-body">
            <?= $message ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <input type="password" name="new_password" class="form-control" required minlength="6">
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" required minlength="6">
                </div>
                <button type="submit" class="btn btn-success w-100">💾 Reset Password</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
