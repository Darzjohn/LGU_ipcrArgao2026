<?php
require 'db.php';
require_once('tcpdf/tcpdf.php');

// Capture filters
$search = trim($_GET['search'] ?? '');
$sort   = $_GET['sort'] ?? 'id';
$dir    = strtoupper($_GET['dir'] ?? 'DESC');

// Build WHERE
$where = [];
$params = [];
$types = '';

if ($search !== '') {
    $where[] = "(p.td_no LIKE ? OR p.lot_no LIKE ? OR p.location LIKE ? OR o.name LIKE ? OR p.barangay LIKE ?)";
    $like = "%$search%";
    $params = [$like, $like, $like, $like, $like];
    $types = "sssss";
}
$where_sql = $where ? "WHERE ".implode(" AND ", $where) : "";

// Sorting
$allowedSort = ['id','tax_year','assessed_value','basic_tax','sef_tax','adjustments','status','barangay','location'];
if (!in_array($sort, $allowedSort)) $sort = 'id';
$dir = ($dir === 'ASC') ? 'ASC' : 'DESC';

// Query all filtered rows
$sql = "SELECT a.*, p.td_no, p.lot_no, COALESCE(p.barangay,'Blank') AS barangay,
               COALESCE(p.location,'Blank') AS location, o.name AS owner_name
        FROM assessments a
        JOIN properties p ON p.id=a.property_id
        LEFT JOIN owners o ON o.id=p.owner_id
        $where_sql
        ORDER BY $sort $dir";

$stmt = $mysqli->prepare($sql);
if($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

// Init PDF
$pdf = new TCPDF();
$pdf->SetCreator('System');
$pdf->SetAuthor('LGU System');
$pdf->SetTitle('Assessments Report');
$pdf->SetMargins(10, 10, 10, true);
$pdf->AddPage();

// Title
$pdf->SetFont('helvetica','B',14);
$pdf->Cell(0, 10, 'Assessments Report', 0, 1, 'C');
$pdf->Ln(5);

// Table
$pdf->SetFont('helvetica','',9);
$html = '<table border="1" cellpadding="3">
<tr style="background-color:#f2f2f2;">
  <th><b>ID</b></th>
  <th><b>Property</b></th>
  <th><b>Owner</b></th>
  <th><b>Barangay</b></th>
  <th><b>Location</b></th>
  <th><b>Tax Year</b></th>
  <th><b>Assessed Value</b></th>
  <th><b>Basic Tax</b></th>
  <th><b>SEF Tax</b></th>
  <th><b>Adjustments</b></th>
  <th><b>Total Tax</b></th>
  <th><b>Status</b></th>
</tr>';

while($row = $res->fetch_assoc()){
    $total = $row['basic_tax'] + $row['sef_tax'] + $row['adjustments'];
    $html .= '<tr>
        <td>'.$row['id'].'</td>
        <td>'.$row['td_no'].' | Lot '.$row['lot_no'].'</td>
        <td>'.$row['owner_name'].'</td>
        <td>'.$row['barangay'].'</td>
        <td>'.$row['location'].'</td>
        <td>'.$row['tax_year'].'</td>
        <td>'.number_format($row['assessed_value'],2).'</td>
        <td>'.number_format($row['basic_tax'],2).'</td>
        <td>'.number_format($row['sef_tax'],2).'</td>
        <td>'.number_format($row['adjustments'],2).'</td>
        <td>'.number_format($total,2).'</td>
        <td>'.ucfirst($row['status']).'</td>
    </tr>';
}
$html .= '</table>';

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('assessments_report.pdf', 'D'); // D = Download
exit;
