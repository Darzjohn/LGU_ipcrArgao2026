<?php
require_once __DIR__ . '/../config/db.php';

$property_id = (int)($_GET['property_id'] ?? 0);
if (!$property_id) {
    echo "<tr><td colspan='4'>⚠️ Invalid property ID.</td></tr>";
    exit;
}

// ✅ Prepare and execute query safely using mysqli
$stmt = $mysqli->prepare("
    SELECT a.tax_year, a.assessed_value,
           " . (column_exists($mysqli, 'properties', 'barangay') ? "p.barangay," : "'' AS barangay,") . "
           p.location
    FROM assessments a
    INNER JOIN properties p ON p.id = a.property_id
    WHERE a.property_id = ?
    ORDER BY a.tax_year DESC
");

$stmt->bind_param("i", $property_id);
$stmt->execute();
$result = $stmt->get_result();

// ✅ Handle empty result
if ($result->num_rows === 0) {
    echo "<tr><td colspan='4'>No history available.</td></tr>";
    exit;
}

// ✅ Output table rows
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['tax_year']) . "</td>";
    echo "<td>₱" . number_format($row['assessed_value'], 2) . "</td>";
    echo "<td>" . htmlspecialchars($row['barangay']) . "</td>";
    echo "<td>" . htmlspecialchars($row['location']) . "</td>";
    echo "</tr>";
}

// ✅ Helper function (add at bottom)
function column_exists($mysqli, $table, $column) {
    $result = $mysqli->query("SHOW COLUMNS FROM $table LIKE '$column'");
    return ($result && $result->num_rows > 0);
}
?>
