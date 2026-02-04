<?php
require 'db.php';

$res = $mysqli->query("SELECT id, property_id FROM tax_bills WHERE rptsp_no IS NULL");
if (!$res || $res->num_rows === 0) {
    die("✅ No NULL RPTSP numbers found. All good!");
}

while ($row = $res->fetch_assoc()) {
    $id = $row['id'];
    $prop = $row['property_id'];

    $newNo = "RPTSP-" . $prop . "-" . $id;

    $stmt = $mysqli->prepare("UPDATE tax_bills SET rptsp_no=? WHERE id=?");
    $stmt->bind_param("si", $newNo, $id);
    if ($stmt->execute()) {
        echo "✔ Updated Bill ID $id with RPTSP No: $newNo<br>";
    } else {
        echo "❌ Failed to update Bill ID $id: " . $stmt->error . "<br>";
    }
    $stmt->close();
}

echo "<br>🎉 Fix complete!";
