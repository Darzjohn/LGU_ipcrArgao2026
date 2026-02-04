<?php
include 'db.php';

$date_from = $_GET['date_from'] ?? '';
$date_to   = $_GET['date_to'] ?? '';
$user      = $_GET['user'] ?? '';

$where = [];
if (!empty($date_from) && !empty($date_to)) {
    $where[] = "date_time BETWEEN '$date_from 00:00:00' AND '$date_to 23:59:59'";
}
if (!empty($user)) {
    $where[] = "user = '" . $conn->real_escape_string($user) . "'";
}

$where_sql = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

$sql = "SELECT * FROM payment_audit $where_sql ORDER BY date_time DESC";
$result = $conn->query($sql);

// Timestamp
$generated_on = date("F d, Y h:i A");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Audit Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .header { text-align: center; }
        .header img { width: 80px; height: 80px; float: left; }
        .header h3, .header h4, .header h5 { margin: 2px 0; }
        .report-title { margin-top: 20px; font-size: 18px; font-weight: bold; text-align: center; }
        .timestamp { text-align: center; font-size: 12px; margin-top: 5px; color: #555; }

        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 6px; font-size: 12px; }
        th { background: #f2f2f2; }

        .footer { margin-top: 50px; width: 100%; }
        .footer td { padding: 20px; font-size: 13px; text-align: center; }
        .line { border-top: 1px solid #000; width: 200px; margin: 0 auto; }

        @media print {
            button { display: none; }
            @page {
                size: A4;
                margin: 20mm;
            }
            body {
                margin: 0;
            }
            /* Page numbers */
            body::after {
                content: "Page " counter(page) " of " counter(pages);
                position: fixed;
                bottom: 10mm;
                right: 20mm;
                font-size: 12px;
                color: #000;
            }
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <div class="header">
        <img src="logo.png" alt="LGU Logo"> <!-- replace with your logo file -->
        <h3>Republic of the Philippines</h3>
        <h4>Province of ____________</h4>
        <h4>Municipality of ____________</h4>
        <h5>Office of the Treasurer</h5>
    </div>

    <div class="report-title">AUDIT TRAIL REPORT</div>
    <div class="timestamp">Generated on: <?php echo $generated_on; ?></div>

    <p><b>Date Range:</b> <?php echo $date_from ?> to <?php echo $date_to ?></p>
    <p><b>User:</b> <?php echo $user ?: "All"; ?></p>

    <!-- TABLE -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Action</th>
                <th>Details</th>
                <th>Date & Time</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['user']; ?></td>
                    <td><?php echo $row['action']; ?></td>
                    <td><?php echo $row['details']; ?></td>
                    <td><?php echo $row['date_time']; ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- FOOTER -->
    <table class="footer">
        <tr>
            <td>
                <div class="line"></div>
                Prepared by
            </td>
            <td>
                <div class="line"></div>
                Approved by
            </td>
        </tr>
    </table>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
