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

// --- Handle Update ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);

    // Handle photo upload
    $photoFile = $_FILES['photo'] ?? null;
    $photoName = null;

    if ($photoFile && $photoFile['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($photoFile['name'], PATHINFO_EXTENSION);
        $photoName = "user_" . $userId . "_" . time() . "." . strtolower($ext);
        move_uploaded_file($photoFile['tmp_name'], "uploads/" . $photoName);
    }

    // Update DB
    if ($photoName) {
        $stmt = $pdo->prepare("UPDATE owners SET name = ?, photo = ? WHERE id = ?");
        $stmt->execute([$name, $photoName, $userId]);
        $_SESSION['user_photo'] = $photoName;
    } else {
        $stmt = $pdo->prepare("UPDATE owners SET name = ? WHERE id = ?");
        $stmt->execute([$name, $userId]);
    }

    $_SESSION['user_name'] = $name;
    $messages[] = "Profile updated successfully!";
}

// --- Fetch User Info ---
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
        <div class="card-header bg-primary text-white">My Profile</div>
        <div class="card-body">
          <?php foreach ($messages as $msg): ?>
            <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
          <?php endforeach; ?>

          <form method="post" enctype="multipart/form-data">
            <div class="mb-3 text-center">
              <img src="uploads/<?= htmlspecialchars($user['photo'] ?? 'default.png') ?>" 
                   alt="Profile Photo" class="rounded-circle mb-3" width="120" height="120">
            </div>

            <div class="mb-3">
              <label class="form-label">Name</label>
              <input type="text" name="name" class="form-control" 
                     value="<?= htmlspecialchars($user['name']) ?>" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Change Profile Photo</label>
              <input type="file" name="photo" class="form-control" accept="image/*">
            </div>

            <button type="submit" class="btn btn-success">Update Profile</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
