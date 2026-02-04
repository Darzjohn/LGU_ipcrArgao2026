<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/system_settings.php';

// ✅ Admin-only access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Handle manual unlock
if (isset($_POST['unlock_id'])) {
    $id = intval($_POST['unlock_id']);
    $stmt = $mysqli->prepare("UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    $_SESSION['msg'] = "✅ User account unlocked successfully.";
    header("Location: unlock_accounts.php");
    exit;
}

// Handle filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_role = isset($_GET['role']) ? trim($_GET['role']) : '';

// Prepare SQL with filters
$sql = "
    SELECT id, name, username, role, failed_attempts, locked_until
    FROM users
    WHERE locked_until IS NOT NULL AND locked_until > NOW()
";

if ($search !== '') {
    $searchLike = "%$search%";
    $sql .= " AND (username LIKE ? OR name LIKE ?)";
}
if ($filter_role !== '') {
    $sql .= " AND role = ?";
}
$sql .= " ORDER BY locked_until DESC";

$stmt = $mysqli->prepare($sql);

if ($search !== '' && $filter_role !== '') {
    $stmt->bind_param("sss", $searchLike, $searchLike, $filter_role);
} elseif ($search !== '') {
    $stmt->bind_param("ss", $searchLike, $searchLike);
} elseif ($filter_role !== '') {
    $stmt->bind_param("s", $filter_role);
}

$stmt->execute();
$locked_users = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Unlock Accounts - <?= SYSTEM_NAME ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background-color: #f8f9fa; }
.container { max-width: 1100px; margin-top: 40px; }
.table th { background-color: #198754; color: white; }
.btn-unlock { background-color: #198754; color: #fff; border: none; }
.btn-unlock:hover { background-color: #146c43; }
.alert { font-size: 0.9rem; }
.search-bar {
  display: flex;
  gap: 10px;
  align-items: center;
  margin-bottom: 1.2rem;
}
.search-bar input, .search-bar select {
  height: 40px;
}
</style>
</head>
<body>
<div class="container">
    <h2 class="mb-4">🔓 Locked User Accounts</h2>

    <?php if (isset($_SESSION['msg'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['msg']); ?></div>
        <?php unset($_SESSION['msg']); ?>
    <?php endif; ?>

    <!-- 🔍 Search and Filter Form -->
    <form method="GET" class="search-bar">
        <input type="text" name="search" class="form-control" placeholder="Search name or username..."
               value="<?= htmlspecialchars($search) ?>">
        <select name="role" class="form-select">
            <option value="">All Roles</option>
            <?php
            $roles = ['admin', 'assessor', 'assessment_clerk', 'treasurer', 'cashier', 'viewer'];
            foreach ($roles as $role) {
                $selected = ($filter_role === $role) ? 'selected' : '';
                echo "<option value=\"$role\" $selected>" . ucfirst(str_replace('_', ' ', $role)) . "</option>";
            }
            ?>
        </select>
        <button type="submit" class="btn btn-success">Filter</button>
        <a href="unlock_accounts.php" class="btn btn-secondary">Reset</a>
    </form>

    <?php if ($locked_users->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Failed Attempts</th>
                        <th>Locked Until</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $count = 1; while ($row = $locked_users->fetch_assoc()): ?>
                    <tr>
                        <td><?= $count++ ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $row['role']))) ?></td>
                        <td><?= (int)$row['failed_attempts'] ?></td>
                        <td><?= htmlspecialchars(date('M d, Y h:i A', strtotime($row['locked_until']))) ?></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Unlock this account?');">
                                <input type="hidden" name="unlock_id" value="<?= $row['id'] ?>">
                                <button type="submit" class="btn btn-unlock btn-sm">Unlock</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">✅ No locked accounts found.</div>
    <?php endif; ?>

    <div class="mt-4">
        <a href="../dashboard.php" class="btn btn-secondary">⬅ Back to Dashboard</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
