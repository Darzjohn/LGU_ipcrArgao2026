<?php
require 'db.php';
session_start();

// Optional: restrict access only to admin users
// if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
//     die("Access denied.");
// }

// Filters
$whereClauses = [];
$params = [];
$types = "";

// Filter by user_id
if (!empty($_GET['user_id'])) {
    $whereClauses[] = "l.user_id = ?";
    $params[] = (int)$_GET['user_id'];
    $types .= "i";
}

// Filter by RPTSP No
if (!empty($_GET['rptsp_no'])) {
    $whereClauses[] = "l.rptsp_no LIKE ?";
    $params[] = "%" . $_GET['rptsp_no'] . "%";
    $types .= "s";
}

// Filter by date range
if (!empty($_GET['date_from']) && !empty($_GET['date_to'])) {
    $whereClauses[] = "DATE(l.created_at) BETWEEN ? AND ?";
    $params[] = $_GET['date_from'];
    $params[] = $_GET['date_to'];
    $types .= "ss";
}

$whereSQL = "";
if (count($whereClauses) > 0) {
    $whereSQL = "WHERE " . implode(" AND ", $whereClauses);
}

// Query logs with joins (if you have users table)
$sql = "SELECT l.id, l.assessment_id, l.tax_bill_id, l.rptsp_no, l.action, l.created_at, 
               u.username AS user_name
        FROM logs l
        LEFT JOIN users u ON l.user_id = u.id
        $whereSQL
        ORDER BY l.created_at DESC";

$stmt = $mysqli->prepare($sql);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Audit Logs</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-4">
    <h2 class="mb-4">📜 Audit Logs</h2>

    <!-- Filter Form -->
    <form method="get" class="row g-2 mb-4">
        <div class="col-md-2">
            <input type="text" name="user_id" class="form-control" placeholder="User ID" value="<?= htmlspecialchars($_GET['user_id'] ?? '') ?>">
        </div>
        <div class="col-md-3">
            <input type="text" name="rptsp_no" class="form-control" placeholder="RPTSP No" value="<?= htmlspecialchars($_GET['rptsp_no'] ?? '') ?>">
        </div>
        <div class="col-md-2">
            <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">
        </div>
        <div class="col-md-2">
            <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
        <div class="col-md-1">
            <a href="logs.php" class="btn btn-secondary w-100">Reset</a>
        </div>
    </form>

    <!-- Logs Table -->
    <div class="card shadow">
        <div class="card-body">
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Assessment ID</th>
                        <th>Tax Bill ID</th>
                        <th>RPTSP No</th>
                        <th>Action</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['user_name'] ?? $row['user_id']) ?></td>
                            <td><?= $row['assessment_id'] ?></td>
                            <td><?= $row['tax_bill_id'] ?></td>
                            <td><span class="badge bg-info text-dark"><?= htmlspecialchars($row['rptsp_no']) ?></span></td>
                            <td><?= htmlspecialchars($row['action']) ?></td>
                            <td><?= $row['created_at'] ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
