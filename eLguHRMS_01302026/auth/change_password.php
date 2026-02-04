<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/header.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $user_id = $_SESSION['user']['id'];

    // Fetch user
    $stmt = $mysqli->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user || !password_verify($current, $user['password'])) {
        $message = "<div class='alert alert-danger'>❌ Current password is incorrect.</div>";
    } elseif ($new !== $confirm) {
        $message = "<div class='alert alert-warning'>⚠️ New passwords do not match.</div>";
    } else {
        // Update password
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $update = $mysqli->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update->bind_param('si', $hashed, $user_id);
        $update->execute();
        $message = "<div class='alert alert-success'>✅ Password changed successfully!</div>";
    }
}
?>

<div class="container mt-5">
    <div class="card shadow col-md-6 mx-auto">
        <div class="card-body">
            <h3 class="card-title text-center mb-4">🔒 Change My Password</h3>
            <?= $message ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Current Password</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <input type="password" name="new_password" class="form-control" required minlength="6">
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" required minlength="6">
                </div>
                <button type="submit" class="btn btn-primary w-100">Update Password</button>
                <a href="../index.php" class="btn btn-secondary w-100 mt-2">Back to Dashboard</a>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
