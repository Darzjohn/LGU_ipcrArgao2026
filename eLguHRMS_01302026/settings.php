<?php
session_start();
require 'db.php';

// Redirect if not logged in
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$messages = [];
$errors = [];

// --- Handle Update ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Fetch current user
    $stmt = $pdo->prepare("SELECT * FROM owners WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // --- Validate username uniqueness ---
    $check = $pdo->prepare("SELECT id FROM owners WHERE username = ? AND id != ?");
    $check->execute([$username, $userId]);
    if ($check->fetch()) {
        $errors[] = "That username is already taken.";
    }

    // --- Validate email uniqueness ---
    if ($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format.";
        } else {
            $check = $pdo->prepare("SELECT id FROM owners WHERE email = ? AND id != ?");
            $check->execute([$email, $userId]);
            if ($check->fetch()) {
                $errors[] = "That email is already in use.";
            }
        }
    }

    // --- Password checks ---
    if ($newPassword || $confirmPassword) {
        if (!password_verify($currentPassword, $user['password'])) {
            $errors[] = "Your current password is incorrect.";
        } elseif ($newPassword !== $confirmPassword) {
            $errors[] = "New password and confirmation do not match.";
        } elseif (strlen($newPassword) < 6) {
            $errors[] = "New password must be at least 6 characters.";
        }
    }

    // --- If no errors, update ---
    if (empty($errors)) {
        if ($newPassword) {
            $hashed = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE owners SET username = ?, email = ?, password = ? WHERE id = ?");
            $stmt->execute([$username, $email, $hashed, $userId]);
        } else {
            $stmt = $pdo->prepare("UPDATE owners SET username = ?, email = ? WHERE id = ?");
            $stmt->execute([$username, $email, $userId]);
        }

        $_SESSION['user_username'] = $username;
        $_SESSION['user_email'] = $email;
        $messages[] = "Settings updated successfully!";
    }
}

// --- Fetch updated user info ---
$stmt = $pdo->prepare("SELECT * FROM owners WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<?php include 'header.php'; ?>
<?php include 'navbar.php'; ?>

<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card shadow">
        <div class="card-header bg-secondary text-white">Account Settings</div>
        <div class="card-body">

          <?php foreach ($messages as $msg): ?>
            <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
          <?php endforeach; ?>

          <?php foreach ($errors as $err): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
          <?php endforeach; ?>

          <form method="post">
            <div class="mb-3">
              <label class="form-label">Username</label>
              <input type="text" name="username" class="form-control"
                     value="<?= htmlspecialchars($user['username'] ?? '') ?>" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control"
                     value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
            </div>

            <hr>
            <h6 class="text-muted">Change Password</h6>

            <div class="mb-3">
              <label class="form-label">Current Password</label>
              <input type="password" name="current_password" class="form-control">
            </div>

            <div class="mb-3">
              <label class="form-label">New Password</label>
              <input type="password" name="new_password" class="form-control">
            </div>

            <div class="mb-3">
              <label class="form-label">Confirm New Password</label>
              <input type="password" name="confirm_password" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary">Update Settings</button>
          </form>

        </div>
      </div>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
