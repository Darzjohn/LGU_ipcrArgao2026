<?php
require 'db.php';

// Capture filters
$search = trim($_GET['search'] ?? '');
$sort   = $_GET['sort'] ?? 'id';
$dir    = strtoupper($_GET['dir'] ?? 'DESC');
$limit  = (int)($_GET['limit'] ?? 10);

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

// Query (no pagination so export ALL filtered results)
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

// CSV or Excel
$format = $_GET['format'] ?? 'csv';
$filename = "assessments_" . date("Y-m-d") . ".$format";

if ($format === 'csv') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=$filename");
    $out = fopen("php://output", "w");
    fputcsv($out, ["ID","Property","Owner","Barangay","Location","Tax Year","Assessed Value","Basic Tax","SEF Tax","Adjustments","Total Tax","Status"]);
    while($row = $res->fetch_assoc()){
        fputcsv($out, [
            $row['id'],
            $row['td_no']." | Lot ".$row['lot_no'],
            $row['owner_name'],
            $row['barangay'],
            $row['location'],
            $row['tax_year'],
            $row['assessed_value'],
            $row['basic_tax'],
            $row['sef_tax'],
            $row['adjustments'],
            $row['basic_tax'] + $row['sef_tax'] + $row['adjustments'],
            ucfirst($row['status'])
        ]);
    }
    fclose($out);
} else {
    require 'vendor/autoload.php'; // PhpSpreadsheet
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->fromArray(["ID","Property","Owner","Barangay","Location","Tax Year","Assessed Value","Basic Tax","SEF Tax","Adjustments","Total Tax","Status"], NULL, 'A1');

    $rowNum = 2;
    while($row = $res->fetch_assoc()){
        $sheet->fromArray([
            $row['id'],
            $row['td_no']." | Lot ".$row['lot_no'],
            $row['owner_name'],
            $row['barangay'],
            $row['location'],
            $row['tax_year'],
            $row['assessed_value'],
            $row['basic_tax'],
            $row['sef_tax'],
            $row['adjustments'],
            $row['basic_tax'] + $row['sef_tax'] + $row['adjustments'],
            ucfirst($row['status'])
        ], NULL, "A$rowNum");
        $rowNum++;
    }

    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    header("Content-Disposition: attachment; filename=$filename");
    $writer = new Xlsx($spreadsheet);
    $writer->save("php://output");
}
exit;
