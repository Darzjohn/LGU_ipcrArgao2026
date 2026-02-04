<?php
require 'db_connection.php'; // adjust if needed

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=payments_list.xls");

$where = [];
if (!empty($_GET['or_no'])) {
    $or_no = $conn->real_escape_string($_GET['or_no']);
    $where[] = "p.or_no LIKE '%$or_no%'";
}
if (!empty($_GET['payor_name'])) {
    $payor_name = $conn->real_escape_string($_GET['payor_name']);
    $where[] = "p.payor_name LIKE '%$payor_name%'";
}
if (!empty($_GET['tax_year'])) {
    $tax_year = $conn->real_escape_string($_GET['tax_year']);
    $where[] = "p.tax_year = '$tax_year'";
}
$whereSQL = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

$sql = "SELECT p.or_no, p.payor_name, p.tax_year, p.assessed_value,
               p.basic_tax, p.sef_tax, p.discount, p.penalty, 
               p.total_paid, p.date_paid
        FROM payments p $whereSQL ORDER BY p.date_paid DESC";
$result = $conn->query($sql);

echo "<table border='1'>";
echo "<tr>
        <th>OR No</th>
        <th>Payor</th>
        <th>Tax Year</th>
        <th>Assessed Value</th>
        <th>Basic Tax</th>
        <th>SEF Tax</th>
        <th>Discount</th>
        <th>Penalty</th>
        <th>Total Paid</th>
        <th>Date Paid</th>
      </tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>
            <td>{$row['or_no']}</td>
            <td>{$row['payor_name']}</td>
            <td>{$row['tax_year']}</td>
            <td>{$row['assessed_value']}</td>
            <td>{$row['basic_tax']}</td>
            <td>{$row['sef_tax']}</td>
            <td>{$row['discount']}</td>
            <td>{$row['penalty']}</td>
            <td>{$row['total_paid']}</td>
            <td>{$row['date_paid']}</td>
          </tr>";
}
echo "</table>";
