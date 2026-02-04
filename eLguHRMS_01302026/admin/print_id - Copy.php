<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';

if (!isset($_GET['id'])) {
    die("Missing employee ID.");
}

$id = intval($_GET['id']);

// Fetch employee
$sql = "
SELECT *,
    CONCAT(surname, ', ', first_name, ' ', middle_name, ' ', name_extension) AS fullname
FROM employees 
WHERE id = ?
LIMIT 1
";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) die("No record found.");

// Lookup department
$dept_name = "";
if (!empty($data['department_id'])) {
    $q = $mysqli->query("SELECT name FROM departments WHERE id=" . intval($data['department_id']));
    $dept_name = $q->num_rows ? $q->fetch_assoc()['name'] : "";
}

// Lookup position
$position_name = "";
if (!empty($data['position_id'])) {
    $q2 = $mysqli->query("SELECT name FROM positions WHERE id=" . intval($data['position_id']));
    $position_name = $q2->num_rows ? $q2->fetch_assoc()['name'] : "";
}


// Lookup employment status
$employment_status_name = "";
if (!empty($data['employment_status_id'])) {
    $q3 = $mysqli->query("SELECT name FROM employment_status WHERE id=" . intval($data['employment_status_id']));
    $employment_status_name = $q3->num_rows ? $q3->fetch_assoc()['name'] : "";
}


// Resolve address
$address = "";
if (!empty($data['pa_barangay'])) {
    $address = $data['pa_house_block_lotno'] . ", " . $data['pa_street'] . ", " . $data['pa_subdivisionvillage'] . ", " . $data['pa_barangay'] . ", " . $data['pa_citymunicipality'] . ", " . $data['pa_province'];
} else {
    $address = $data['ra_house_block_lotno'] . ", " . $data['ra_street'] . ", " . $data['ra_subdivisionvillage'] . ", " . $data['ra_barangay'] . ", " . $data['ra_citymunicipality'] . ", " . $data['ra_province'];
}

// Load FPDF
require_once __DIR__ . '/../libraries/fpdf/fpdf.php';

$pdf = new FPDF("P", "mm", "Letter");
$pdf->SetAutoPageBreak(false);

// Front template
$pdf->AddPage();
$front_img = __DIR__ . "/../assets/idregcasfrontblank.png";
$back_img  = __DIR__ . "/../assets/idregcasbackblank.png";

if (!file_exists($front_img)) die("Front template missing: " . $front_img);
$pdf->Image($front_img, 10, 10, 95, 150);

// Employee photo
$photo_path = __DIR__ . "/../uploads/" . $data['photo'];
if (!empty($data['photo']) && file_exists($photo_path)) {
    // Resize and fit photo within the circle area (FPDF only)
    $pdf->Image($photo_path, 31, 38, 50, 50); // adjust X, Y, width, height to match your circle
} else {
    die("Employee photo missing: " . $photo_path);
}

// Employee info
$pdf->SetFont("Arial", "B", 12);
$pdf->SetTextColor(0, 0, 90);
$pdf->SetXY(20, 100);
$pdf->Cell(80, 7, strtoupper($data['fullname']), 0, 1, "C");

$pdf->SetFont("Arial", "", 11);
$pdf->SetXY(20, 106);
$pdf->Cell(80, 6, strtoupper($position_name), 0, 1, "C");


$pdf->SetFont("Arial", "", 9);
$pdf->SetXY(20, 111);
$pdf->Cell(80, 6, strtoupper($employment_status_name), 0, 1, "C");



$pdf->SetFont("Arial", "B", 11);
$pdf->SetXY(19, 121);
$pdf->Cell(80, 6, $data['emp_idno'], 0, 1, "C");

$pdf->SetXY(22, 126.5);
$pdf->Cell(80, 6, strtoupper($dept_name), 0, 1, "C");

// Back template
$pdf->AddPage();
if (!file_exists($back_img)) die("Back template missing: " . $back_img);
$pdf->Image($back_img, 10, 10, 95, 150);

// Helper function for rows
function writeRow($pdf, $x, $y, $label, $value) {
    $pdf->SetTextColor(255, 255, 255); // white font
    $pdf->SetFont("Arial", "B", 10);
    $pdf->SetXY($x, $y);
    $pdf->Cell(35, 5, $label);

    $pdf->SetFont("Arial", "", 10);
    $pdf->SetXY($x + 33, $y);
    $pdf->Cell(60, 5, $value);

    $pdf->SetTextColor(0, 0, 0); // optional reset
}

function writeWrappedAddress($pdf, $x, $y, $label, $value, $labelWidth = 35, $valueWidth = 40, $lineHeight = 5) {
    $pdf->SetTextColor(255, 255, 255); // white font

    // Print label
    $pdf->SetFont("Arial", "B", 9);
    $pdf->SetXY($x, $y);
    $pdf->Cell($labelWidth, $lineHeight, $label);

    // Print value with wrapping
    $pdf->SetFont("Arial", "", 7);
    $pdf->SetXY($x + $labelWidth, $y);
    $pdf->MultiCell($valueWidth, $lineHeight, $value);

    $pdf->SetTextColor(0, 0, 0); // reset if needed
}



// ---------------- Back details ----------------
$X = 20;
$Y = 14.5;

// Address (wrapped) — get bottom Y
$Y = writeWrappedAddress($pdf, $X, $Y, "", $address);

$X = 28;
$Y = 15;
// Other fields — increment Y dynamically
$Y += 5; // add some spacing after address
writeRow($pdf, $X, 30, "", $data['mobile_no']);
$Y += 10;
writeRow($pdf, $X, 39, "", $data['dob']);
$Y += 10;
writeRow($pdf, $X, 47, "", $data['blood_type']);
$Y += 10;
writeRow($pdf, $X, 58, "", $data['sss_no']);
$Y += 10;
writeRow($pdf, $X, 66, "", $data['gsis_no']);
$Y += 10;
writeRow($pdf, $X, 74.5, "", $data['tin_no']);
$Y += 10;
writeRow($pdf, $X, 83, ":", $data['pagibig_no']);
$Y += 10;
writeRow($pdf, $X, 91, "", $data['phic_no']);



// Emergency
$pdf->SetFont("Arial", "B", 10);
$pdf->SetXY($X, $Y + 95);
// $pdf->Cell(80, 6, "IN CASE OF EMERGENCY, NOTIFY:");

$pdf->SetFont("Arial", "B", 12);
$pdf->SetXY(38, 115);
$pdf->Cell(80, 6, strtoupper($data['emergency_contact_person']));

$pdf->SetXY(45, 125);
$pdf->Cell(80, 6, $data['emergency_contact_no']);

// Date issued
$pdf->SetFont("Arial", "B", 9);
$pdf->SetXY(58, 154.5);
$pdf->Cell(80, 6, date("m-d-Y", strtotime($data['created_at'])));

// Output
$pdf->Output("I", "ID-" . $data['emp_idno'] . ".pdf");
exit;
?>
