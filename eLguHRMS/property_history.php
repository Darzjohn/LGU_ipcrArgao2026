<?php
require 'db.php';

$property_id = (int)($_GET['property_id'] ?? 0);
if (!$property_id) {
    echo "<tr><td colspan='4'>⚠️ Invalid property ID.</td></tr>";
    exit;
}

// Fetch history with property info
$res = q(
    "SELECT a.tax_year, a.assessed_value, p.barangay, p.location
     FROM assessments a
     INNER JOIN properties p ON p.id = a.property_id
     WHERE a.property_id=?
     ORDER BY a.tax_year DESC",
    "i",
    [$property_id]
);

$result = $res->get_result();
if ($result->num_rows === 0) {
    echo "<tr><td colspan='4'>No history available.</td></tr>";
    exit;
}

// Output table rows
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['tax_year']) . "</td>";
    echo "<td>₱" . number_format($row['assessed_value'], 2) . "</td>";
    echo "<td>" . htmlspecialchars($row['barangay']) . "</td>";
    echo "<td>" . htmlspecialchars($row['location']) . "</td>";
    echo "</tr>";
}
?>
