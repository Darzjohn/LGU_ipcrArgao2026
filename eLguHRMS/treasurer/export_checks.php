<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';

$type = $_GET['type'] ?? '';
if (!$type) die("Invalid export type");

// Build Filter SQL (same as checks_issued.php)
$filter_sql = "
    SELECT c.*, a.account_name, a.account_number, f.name AS fund_source_name 
    FROM checks_issued c 
    LEFT JOIN bank_accounts a ON a.id = c.account_id
    LEFT JOIN fund_source f ON f.id = c.fund_source_id
    WHERE 1
";

$params = [];
$types = "";

// Search filter
if (!empty($_GET['search'])) {
    $filter_sql .= " AND (c.payee LIKE ? OR c.check_no LIKE ? OR c.nature_of_payment LIKE ?) ";
    $searchTerm = "%" . $_GET['search'] . "%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
    $types .= "sss";
}

// Date filter
if (!empty($_GET['date_filter'])) {
    $filter_sql .= " AND c.issue_date = ? ";
    $params[] = $_GET['date_filter'];
    $types .= "s";
}

// Month filter
if (!empty($_GET['month_filter'])) {
    $filter_sql .= " AND DATE_FORMAT(c.issue_date, '%Y-%m') = ? ";
    $params[] = $_GET['month_filter'];
    $types .= "s";
}

$filter_sql .= " ORDER BY c.id DESC";

// EXECUTE QUERY
$stmt = $mysqli->prepare($filter_sql);
if (!empty($params)) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) $data[] = $row;

// ------------------------------------------------------------------------
// EXPORT TO EXCEL
// ------------------------------------------------------------------------
if ($type == 'excel') {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=checks_issued.xls");

    echo "<table border='1'>";
    echo "<tr>
            <th>ID</th><th>Serial No</th><th>Check No</th><th>Payee</th>
            <th>Issue Date</th><th>Amount</th><th>Account</th><th>Fund Source</th>
        </tr>";

    foreach ($data as $d) {
        echo "<tr>
                <td>{$d['id']}</td>
                <td>{$d['serial_no']}</td>
                <td>{$d['check_no']}</td>
                <td>{$d['payee']}</td>
                <td>{$d['issue_date']}</td>
                <td>{$d['amount']}</td>
                <td>{$d['account_name']} ({$d['account_number']})</td>
                <td>{$d['fund_source_name']}</td>
            </tr>";
    }

    echo "</table>";
    exit;
}

// ------------------------------------------------------------------------
// EXPORT TO PDF
// ------------------------------------------------------------------------
if ($type == 'pdf') {

    require_once __DIR__ . '/../vendor/autoload.php';

    $html = "
        <h2 style='text-align:center;'>Checks Issued Report</h2>
        <table border='1' cellspacing='0' cellpadding='5'>
            <tr>
                <th>ID</th><th>Serial No</th><th>Check No</th><th>Payee</th>
                <th>Issue Date</th><th>Amount</th><th>Account</th><th>Fund Source</th>
            </tr>
    ";

    foreach ($data as $d) {
        $html .= "
            <tr>
                <td>{$d['id']}</td>
                <td>{$d['serial_no']}</td>
                <td>{$d['check_no']}</td>
                <td>{$d['payee']}</td>
                <td>{$d['issue_date']}</td>
                <td>{$d['amount']}</td>
                <td>{$d['account_name']} ({$d['account_number']})</td>
                <td>{$d['fund_source_name']}</td>
            </tr>
        ";
    }

    $html .= "</table>";

    $mpdf = new \Mpdf\Mpdf();
    $mpdf->WriteHTML($html);
    $mpdf->Output("checks_issued.pdf", "D");
    exit;
}
?>
