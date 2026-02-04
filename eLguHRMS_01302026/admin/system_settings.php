<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';

// Only admin can access this page
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Fetch existing settings
$result = $mysqli->query("SELECT * FROM system_settings LIMIT 1");
$settings = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $system_name = trim($_POST['system_name']);
    $municipality = trim($_POST['municipality']);

    $logo = $settings['logo'] ?? null;
    $background = $settings['background'] ?? null;

    $upload_dir = __DIR__ . '/../uploads/';

    // Ensure upload folder exists
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Handle logo upload
    if (!empty($_FILES['logo']['name'])) {
        $logo_name = 'logo_' . time() . '.' . pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        move_uploaded_file($_FILES['logo']['tmp_name'], $upload_dir . $logo_name);
        $logo = $logo_name;
    }

    // Handle background upload
    if (!empty($_FILES['background']['name'])) {
        $bg_name = 'background_' . time() . '.' . pathinfo($_FILES['background']['name'], PATHINFO_EXTENSION);
        move_uploaded_file($_FILES['background']['tmp_name'], $upload_dir . $bg_name);
        $background = $bg_name;
    }

    // Update or Insert
    if ($settings) {
        $stmt = $mysqli->prepare("UPDATE system_settings SET system_name=?, municipality=?, logo=?, background=? WHERE id=?");
        $stmt->bind_param("ssssi", $system_name, $municipality, $logo, $background, $settings['id']);
    } else {
        $stmt = $mysqli->prepare("INSERT INTO system_settings (system_name, municipality, logo, background) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $system_name, $municipality, $logo, $background);
    }
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('✅ System settings updated successfully!'); window.location.href='system_settings.php';</script>";
    exit;
}
?>

<div class="container-fluid mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="bi bi-gear-fill"></i> System Settings</h4>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">System Name</label>
                    <input type="text" class="form-control" name="system_name" value="<?= htmlspecialchars($settings['system_name'] ?? '') ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Municipality</label>
                    <input type="text" class="form-control" name="municipality" value="<?= htmlspecialchars($settings['municipality'] ?? '') ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Logo</label>
                    <input type="file" class="form-control" name="logo" accept="image/*">
                    <?php if (!empty($settings['logo'])): ?>
                        <div class="mt-2">
                            <img src="../uploads/<?= htmlspecialchars($settings['logo']) ?>" alt="Logo" height="60" class="border rounded shadow-sm">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Background Image</label>
                    <input type="file" class="form-control" name="background" accept="image/*">
                    <?php if (!empty($settings['background'])): ?>
                        <div class="mt-2">
                            <img src="../uploads/<?= htmlspecialchars($settings['background']) ?>" alt="Background" height="60" class="border rounded shadow-sm">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-12 text-end mt-4">
                    <button type="submit" class="btn btn-success px-4">
                        <i class="bi bi-check-circle"></i> Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
