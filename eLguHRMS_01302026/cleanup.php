<?php
require 'db.php';

// Counters
$updates = 0;

// Clean up email
$sql1 = "UPDATE owners SET email = NULL WHERE email = '' OR email = 'none@gmail.com'";
if ($mysqli->query($sql1)) {
    $updates += $mysqli->affected_rows;
}

// Clean up contact numbers
$sql2 = "UPDATE owners SET contact_no = NULL WHERE contact_no = '' OR contact_no = '0'";
if ($mysqli->query($sql2)) {
    $updates += $mysqli->affected_rows;
}

// Output
echo "<h2>Cleanup Complete</h2>";
echo "<p>Total rows updated: <strong>$updates</strong></p>";
echo "<p><a href='owners.php'>Back to Owners</a></p>";
?>
