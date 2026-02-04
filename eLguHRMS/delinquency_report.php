<?php
require '../config/db.php';

$barangay = $_GET['barangay'] ?? '';
$year = $_GET['year'] ?? '';
$owner = $_GET['owner'] ?? '';

$query = "SELECT * FROM tax_bills WHERE status='Unpaid'";
$params = [];
$types = '';

if ($barangay !== '') {
    $query .= " AND barangay = ?";
    $params[] = $barangay;
    $types .= 's';
}

if ($year !== '') {
    $query .= " AND year = ?";
    $params[] = $year;
    $types .= 'i';
}

if ($owner !== '') {
    $query .= " AND owner LIKE ?";
    $params[] = "%$owner%";
    $types .= 's';
}

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Delinquency Report</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h4 class="mb-3">Delinquency Report</h4>

    <form class="row g-2 mb-3" method="get">
        <div class="col-md-3">
            <input type="text" name="barangay" class="form-control" placeholder="Barangay" value="<?= htmlspecialchars($barangay) ?>">
        </div>
        <div class="col-md-2">
            <input type="number" name="year" class="form-control" placeholder="Year" value="<?= htmlspecialchars($year) ?>">
        </div>
        <div class="col-md-3">
            <input type="text" name="owner" class="form-control" placeholder="Owner Name" value="<?= htmlspecialchars($owner) ?>">
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100">Filter</button>
        </div>
        <div class="col-md-2">
            <a href="delinquency_report.php" class="btn btn-secondary w-100">Reset</a>
        </div>
    </form>

    <table class="table table-bordered table-sm">
        <thead class="table-dark">
            <tr>
                <th>TD No</th>
                <th>Owner</th>
                <th>Barangay</th>
                <th>Year</th>
                <th>Amount</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['td_no']) ?></td>
                    <td><?= htmlspecialchars($row['owner']) ?></td>
                    <td><?= htmlspecialchars($row['barangay']) ?></td>
                    <td><?= htmlspecialchars($row['year']) ?></td>
                    <td>₱<?= number_format($row['amount'], 2) ?></td>
                    <td><span class="badge bg-danger"><?= htmlspecialchars($row['status']) ?></span></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center text-muted">No unpaid records found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
