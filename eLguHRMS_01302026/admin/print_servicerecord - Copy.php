<?php
// print_servicerecord.php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';

// Adjust this path if your tcpdf location differs
require_once __DIR__ . '/../libraries/tcpdf/tcpdf.php';

// Header texts
$header_lines = [
    'REPUBLIC OF THE PHILIPPINES',
    'PROVINCE OF CEBU',
    'MUNICIPALITY OF ARGAO',
    'LOCAL GOVERNMENT UNIT'
];

// Default Signatories (fallbacks)
$approved_by = ['name' => 'ALLAN M. SESALDO', 'position' => 'Municipal Mayor'];
$prepared_by = ['name' => 'JO ANN M. VILLAFUERTE', 'position' => 'Administrative Officer V'];

// Load signatories from DB (dynamic selection)
$sign_q = $mysqli->query("SELECT id, position, name, title FROM signatories");
if ($sign_q) {
    while ($s = $sign_q->fetch_assoc()) {
        $pos = strtolower($s['position'] ?? '');
        $title = strtolower($s['title'] ?? '');

        // Mayor -> Approved by (match 'mayor' in position or title)
        if (strpos($pos, 'mayor') !== false || strpos($title, 'mayor') !== false) {
            $approved_by = ['name' => $s['name'], 'position' => $s['position']];
        }

        // HR/Admin -> Prepared by (match common HR/admin keywords)
        if (
            strpos($pos, 'administrative officer') !== false ||
            strpos($pos, 'administrative') !== false ||
            strpos($pos, 'admin') !== false ||
            strpos($pos, 'hr') !== false ||
            strpos($pos, 'human resource') !== false ||
            strpos($title, 'human') !== false ||
            strpos($title, 'personnel') !== false
        ) {
            $prepared_by = ['name' => $s['name'], 'position' => $s['position']];
        }
    }
}

// parse ids from query string
$ids_param = $_GET['ids'] ?? '';
$ids_param = trim($ids_param);
if (empty($ids_param)) {
    echo "No records selected.";
    exit;
}
$ids = array_filter(array_map('intval', explode(',', $ids_param)));
if (count($ids) === 0) {
    echo "No valid records selected.";
    exit;
}

// Prepare placeholders for IN(...)
$ids_placeholders = implode(',', array_fill(0, count($ids), '?'));

// Main query: join service_records with lookup tables and employees
$sql = "SELECT sr.*, e.first_name, e.middle_name, e.surname, e.name_extension, e.dob,
               d.name AS department_name, p.name AS position_name, es.name AS status_name
        FROM service_records sr
        LEFT JOIN employees e ON sr.emp_idno = e.emp_idno
        LEFT JOIN departments d ON sr.assignment = d.id
        LEFT JOIN positions p ON sr.position = p.id
        LEFT JOIN employment_status es ON sr.status = es.id
        WHERE sr.id IN ($ids_placeholders)
        ORDER BY sr.emp_idno, sr.recfrom ASC, sr.id ASC";

$stmt = $mysqli->prepare($sql);
if ($stmt === false) {
    echo "Database error: " . $mysqli->error;
    exit;
}

// Bind params dynamically - types string of 'i' repeated
$types = str_repeat('i', count($ids));
$bind_names = [];
$bind_names[] = $types;
for ($i = 0; $i < count($ids); $i++) {
    $bind_names[] = &$ids[$i];
}
call_user_func_array([$stmt, 'bind_param'], $bind_names);

$stmt->execute();
$res = $stmt->get_result();

// group records by employee idno
$grouped = [];
while ($row = $res->fetch_assoc()) {
    $emp = $row['emp_idno'];
    if (!isset($grouped[$emp])) $grouped[$emp] = ['employee' => $row, 'rows' => []];
    $grouped[$emp]['rows'][] = $row;
}
$stmt->close();

// instantiate TCPDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// document info
$pdf->SetCreator('eLguHRMS');
$pdf->SetAuthor('eLguHRMS');
$pdf->SetTitle('Service Record');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// fonts & margins
$pdf->SetMargins(12, 10, 12);
$pdf->SetAutoPageBreak(TRUE, 18);
// F1 chosen: keep font size 9
$pdf->SetFont('dejavusans', '', 9);

// The note that must appear above the table (D2)
$above_table_note = "Date herein should be checked from birth or baptismal certificate or some other reliable document/paper.";

// The certification paragraph (placed above signatures)
$certification_paragraph = "This is to certify that the employee named herein above actually rendered services in this Office as shown by the employee service record, below, each line of which is supported by appointment and other papers actually issued by this Office and approved by the authorities concerned.";

// Column widths (portrait narrow columns)
$col = [
    // keep widths small to fit portrait but allow wrapping
    'from'           => 16,
    'to'             => 16,
    'desig'          => 36,
    'status'         => 14,
    'salary'         => 18,
    'salary_grade'   => 10,
    'step_increment' => 8,
    'station'        => 30,
    'lawop'          => 12,
    'sepdate'        => 12,
    'sepcause'       => 18,
    'remarks'        => 16
];

// Helper to compute multi-line heights
function getCellHeight($pdf, $w, $txt, $minLineHeight = 6) {
    $h = $pdf->getStringHeight($w, $txt);
    if ($h < $minLineHeight) $h = $minLineHeight;
    return $h;
}

// Helper to print the table header (using MultiCell so titles wrap)
// $printNote flag decides whether to print the "above_table_note" (only on first page of employee)
function printTableHeader($pdf, $col, $above_table_note, $printNote = true, $header_lines = []) {
    // Print header lines (LGU lines) — these are printed outside before calling this in main flow usually.
    // The caller must handle printing header_lines and employee name/dob.

    // Print the note above the table if requested
    if ($printNote && !empty(trim($above_table_note))) {
        $pdf->SetFont('', 'I', 8);
        // Use MultiCell full width
        $margins = $pdf->getMargins();
        $fullWidth = $pdf->getPageWidth() - $margins['left'] - $margins['right'];
        $pdf->MultiCell($fullWidth, 5, $above_table_note, 0, 'L', 0, 1, '', '', true);
        $pdf->Ln(2);
        $pdf->SetFont('', '', 9);
    }

    // Column headers text array - keep short labels that can wrap
    $headers = [
        'FROM', 'TO', 'DESIGNATION', 'STATUS', 'SALARY',
        'SG', 'STEP', 'STATION / ASSIGNMENT', 'LAW / OP', 'SEPARATION DATE', 'SEPARATION CAUSE', 'REMARKS'
    ];

    // Print header row with shading
    $pdf->SetFont('', 'B', 9);
    $pdf->SetFillColor(220,220,220);

    // we must use MultiCell to allow header wrapping; however MultiCell always advances line by default.
    // We'll print them sequentially in the same row using the 0,0, and at the last one set ln=1.
    // To avoid messing X/Y, we capture current X and Y and then place cells.

    $startX = $pdf->GetX();
    $startY = $pdf->GetY();

    $wlist = [
        $col['from'],$col['to'],$col['desig'],$col['status'],$col['salary'],
        $col['salary_grade'],$col['step_increment'],$col['station'],$col['lawop'],$col['sepdate'],$col['sepcause'],$col['remarks']
    ];

    // We'll loop printing each header as a MultiCell but force it to stay in same line by using x,y control.
    for ($i = 0; $i < count($headers); $i++) {
        $w = $wlist[$i];
        // Determine the height needed for the header label (allow up to 3 lines)
        $h = $pdf->getStringHeight($w, $headers[$i], false, true, '', 1);
        if ($h < 7) $h = 7;
        // Print the cell (bordered, filled)
        // Save current position
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->MultiCell($w, $h, $headers[$i], 1, 'C', 1, 0, $x, $y, true);
    }
    // finish the row
    $pdf->Ln();
    $pdf->SetFont('', '', 9);
}

// Main loop per employee
foreach ($grouped as $empno => $data) {
    $emp = $data['employee'];
    $rows = $data['rows'];

    // start page for employee
    $pdf->AddPage();

    // Print header lines
    $pdf->SetFont('', '', 9);
    foreach ($header_lines as $h) {
        $pdf->Cell(0, 4, $h, 0, 1, 'C');
    }
    $pdf->Ln(2);

    // Title
    $pdf->SetFont('', 'B', 12);
    $pdf->Cell(0, 6, 'SERVICE RECORD', 0, 1, 'C');
    $pdf->Ln(3);

    // Employee info block
    $pdf->SetFont('', '', 9);
    $fullname = trim(($emp['first_name'] ?? '') . ' ' . ($emp['middle_name'] ?? '') . ' ' . ($emp['surname'] ?? '') . ' ' . ($emp['name_extension'] ?? ''));
    $fullname = strtoupper($fullname);
    $pdf->Cell(30, 6, 'NAME:', 0, 0);
    $pdf->SetFont('', 'B', 9);
    $pdf->Cell(0, 6, $fullname, 0, 1);

    $pdf->SetFont('', '', 9);
    $pdf->Cell(30, 6, 'BIRTHDATE:', 0, 0);
    $dob = !empty($emp['dob']) && $emp['dob'] != '0000-00-00' ? date('F j, Y', strtotime($emp['dob'])) : '';
    $pdf->Cell(0, 6, $dob, 0, 1);

    $pdf->Ln(4);

    // Print the table header with the above-table note
    printTableHeader($pdf, $col, $above_table_note, true);

    // Start rows
    foreach ($rows as $r) {
        // prepare cell texts (ensure all fields exist)
        $from = !empty($r['recfrom']) && $r['recfrom'] != '0000-00-00' ? date('m/d/Y', strtotime($r['recfrom'])) : '';
        $to = !empty($r['recto']) && $r['recto'] != '0000-00-00' ? date('m/d/Y', strtotime($r['recto'])) : '';
        $designation = $r['position_name'] ?? ($r['position'] ?? '');
        $status = $r['status_name'] ?? ($r['status'] ?? '');
        $salary_text = isset($r['salary']) && $r['salary'] !== null ? number_format((float)$r['salary'], 2, '.', ',') : '';
        $salary_grade = $r['salary_grade'] ?? '';
        $step_increment = $r['step_increment'] ?? '';
        $station = $r['department_name'] ?? ($r['assignment'] ?? '');
        $lawop = $r['lawop'] ?? '';
        $sepdate = !empty($r['separation_date']) && $r['separation_date'] != '0000-00-00' ? date('m/d/Y', strtotime($r['separation_date'])) : '';
        $sepcause = $r['separation_cause'] ?? '';
        $remarks = $r['remarks'] ?? '';

        // compute heights for multiline columns
        $h_desig = getCellHeight($pdf, $col['desig'], $designation);
        $h_station = getCellHeight($pdf, $col['station'], $station);
        $h_sepcause = getCellHeight($pdf, $col['sepcause'], $sepcause);
        $h_remarks = getCellHeight($pdf, $col['remarks'], $remarks);

        // minimal row height
        $minRowH = 6;
        $rowH = max($minRowH, $h_desig, $h_station, $h_sepcause, $h_remarks);

        // If nearing bottom of page, add new page and reprint header_lines, employee info, and table header (but don't reprint the above_table_note again)
        $y = $pdf->GetY();
        $margins = $pdf->getMargins();
        $pageBottomY = $pdf->getPageHeight() - $margins['bottom'];
        if ($y + $rowH + 80 > $pageBottomY) {
            $pdf->AddPage();
            // reprint header lines
            $pdf->SetFont('', '', 9);
            foreach ($header_lines as $h) {
                $pdf->Cell(0, 4, $h, 0, 1, 'C');
            }
            $pdf->Ln(2);
            $pdf->SetFont('', 'B', 12);
            $pdf->Cell(0, 6, 'SERVICE RECORD', 0, 1, 'C');
            $pdf->Ln(3);

            // emp name & dob
            $pdf->SetFont('', '', 9);
            $pdf->Cell(30, 6, 'NAME:', 0, 0);
            $pdf->SetFont('', 'B', 9);
            $pdf->Cell(0, 6, $fullname, 0, 1);
            $pdf->SetFont('', '', 9);
            $pdf->Cell(30, 6, 'BIRTHDATE:', 0, 0);
            $pdf->Cell(0, 6, $dob, 0, 1);
            $pdf->Ln(4);

            // reprint header row but skip the above-table note (only on first page)
            printTableHeader($pdf, $col, $above_table_note, false);
        }

        // Print the row using MultiCell with fixed width and computed height; ensure cells align in a grid
        $pdf->MultiCell($col['from'],             $rowH, $from,           1, 'C', 0, 0, '', '', true);
        $pdf->MultiCell($col['to'],               $rowH, $to,             1, 'C', 0, 0, '', '', true);
        $pdf->MultiCell($col['desig'],            $rowH, $designation,    1, 'L', 0, 0, '', '', true);
        $pdf->MultiCell($col['status'],           $rowH, $status,         1, 'C', 0, 0, '', '', true);
        $pdf->MultiCell($col['salary'],           $rowH, $salary_text,    1, 'R', 0, 0, '', '', true);
        $pdf->MultiCell($col['salary_grade'],     $rowH, $salary_grade,   1, 'C', 0, 0, '', '', true);
        $pdf->MultiCell($col['step_increment'],   $rowH, $step_increment, 1, 'C', 0, 0, '', '', true);
        $pdf->MultiCell($col['station'],          $rowH, $station,        1, 'L', 0, 0, '', '', true);
        $pdf->MultiCell($col['lawop'],            $rowH, $lawop,          1, 'C', 0, 0, '', '', true);
        $pdf->MultiCell($col['sepdate'],          $rowH, $sepdate,        1, 'C', 0, 0, '', '', true);
        $pdf->MultiCell($col['sepcause'],         $rowH, $sepcause,       1, 'L', 0, 0, '', '', true);
        $pdf->MultiCell($col['remarks'],          $rowH, $remarks,        1, 'L', 0, 1, '', '', true);
    }

    // space before certification and signatures
    $pdf->Ln(6);

    // Certification paragraph (above signatures)
    $pdf->MultiCell(0, 5, $certification_paragraph, 0, 'L', 0, 1, '', '', true);
    $pdf->Ln(6);

    // Signatures block - two columns
    $margins = $pdf->getMargins();
    $pageW = $pdf->getPageWidth() - $margins['left'] - $margins['right'];
    $colW = ($pageW / 2) - 10;

    $pdf->SetFont('', '', 9);
    $pdf->Cell($colW, 6, 'Approved:', 0, 0, 'L');
    $pdf->Cell($colW, 6, 'Prepared by:', 0, 1, 'R');

    $pdf->Ln(18); // space for signatures

    $pdf->SetFont('', 'B', 9);
    $pdf->Cell($colW, 6, $approved_by['name'], 0, 0, 'L');
    $pdf->Cell($colW, 6, $prepared_by['name'], 0, 1, 'R');

    $pdf->SetFont('', '', 9);
    $pdf->Cell($colW, 6, $approved_by['position'], 0, 0, 'L');
    $pdf->Cell($colW, 6, $prepared_by['position'], 0, 1, 'R');

    $pdf->Ln(6);
    $pdf->Cell(0, 6, 'Date Accomplished: ' . date('F j, Y'), 0, 1, 'C');

    // footer page number
    $pdf->SetY(-12);
    $pdf->SetFont('', '', 8);
    $pdf->Cell(0, 6, 'Page ' . $pdf->getAliasNumPage() . ' of ' . $pdf->getAliasNbPages(), 0, 0, 'C');
}

// Output PDF inline
$pdf->Output('service_records.pdf', 'I');
exit;
