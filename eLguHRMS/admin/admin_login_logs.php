<?php
require_once __DIR__ . '/auth/session_check.php';
require_role(['admin']); // Only Admins can view logs
require_once __DIR__ . '/config/db.php';

// --- Filters ---
$search = $_GET['search'] ?? '';
$role = $_GET['role'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

$query = "SELECT l.*, u.username, u.role 
          FROM login_logs l 
          LEFT JOIN users u ON l.user_id = u.id 
          WHERE 1";

$params = [];
$types = "";

// Search by username
if (!empty($search)) {
    $query .= " AND u.username LIKE ?";
    $params[] = "%$search%";
    $types .= "s";
}

// Filter by role
if (!empty($role)) {
    $query .= " AND u.role = ?";
    $params[] = $role;
    $types .= "s";
}

// Filter by date range
if (!empty($date_from) && !empty($date_to)) {
    $query .= " AND DATE(l.login_time) BETWEEN ? AND ?";
    $params[] = $date_from;
    $params[] = $date_to;
    $types .= "ss";
}

$query .= " ORDER BY l.login_time DESC";

$stmt = $mysqli->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login Logs | RPTMS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { background-color: #f8f9fa; }
.card { border-radius: 1rem; box-shadow: 0 3px 10px rgba(0,0,0,0.1); }
.table th { background-color: var(--bs-success-bg-subtle); }
</style>
</head>
<body class="p-4">

<div class="container">
  <div class="card">
    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Login Logs</h5>
      <a href="admin_dashboard.php" class="btn btn-light btn-sm">
        <i class="bi bi-arrow-left"></i> Back
      </a>
    </div>
    <div class="card-body">

      <!-- Filter Form -->
      <form class="row g-2 mb-3" method="GET">
        <div class="col-md-3">
          <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="Search Username...">
        </div>
        <div class="col-md-3">
          <select name="role" class="form-select">
            <option value="">All Roles</option>
            <?php
              $roles = ['admin','assessor','assessment_clerk','treasurer','cashier','viewer'];
              foreach ($roles as $r) {
                $sel = $r == $role ? 'selected' : '';
                echo "<option value='$r' $sel>" . ucfirst(str_replace('_',' ',$r)) . "</option>";
              }
            ?>
          </select>
        </div>
        <div class="col-md-2">
          <input type="date" name="date_from" value="<?= htmlspecialchars($date_from) ?>" class="form-control">
        </div>
        <div class="col-md-2">
          <input type="date" name="date_to" value="<?= htmlspecialchars($date_to) ?>" class="form-control">
        </div>
        <div class="col-md-2">
          <button class="btn btn-success w-100"><i class="bi bi-funnel"></i> Filter</button>
        </div>
      </form>

      <div class="table-responsive">
        <table class="table table-bordered align-middle text-center">
          <thead>
            <tr>
              <th>#</th>
              <th>Username</th>
              <th>Role</th>
              <th>IP Address</th>
              <th>Status</th>
              <th>Login Time</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if ($result->num_rows > 0) {
                $i = 1;
                while ($row = $result->fetch_assoc()) {
                    $statusColor = $row['status'] === 'Success' ? 'text-success' : 'text-danger';
                    echo "<tr>
                            <td>{$i}</td>
                            <td>{$row['username']}</td>
                            <td>" . ucfirst(str_replace('_',' ',$row['role'])) . "</td>
                            <td>{$row['ip_address']}</td>
                            <td class='$statusColor fw-semibold'>{$row['status']}</td>
                            <td>{$row['login_time']}</td>
                          </tr>";
                    $i++;
                }
            } else {
                echo "<tr><td colspan='6' class='text-muted'>No login logs found.</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>

      <div class="text-end mt-2">
        <a href="export_logs.php" class="btn btn-outline-secondary btn-sm">
          <i class="bi bi-download"></i> Export CSV
        </a>
      </div>

    </div>
  </div>
</div>

</body>
</html>
