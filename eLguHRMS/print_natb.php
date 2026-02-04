<?php
ob_start();
require 'db.php';
require_once('tcpdf/tcpdf.php');

// ---------------- INPUT VALIDATION ----------------
$property_id = isset($_GET['property_id']) ? intval($_GET['property_id']) : 0;
if ($property_id <= 0) {
    ob_end_clean();
    die("❌ Invalid Property ID.");
}

// ---------------- FETCH PROPERTY ----------------
$sql = "
  SELECT p.id AS property_id, p.td_no, p.lot_no, p.location, p.classification,
         p.assessed_value, o.name AS owner_name, o.address
  FROM properties p
  LEFT JOIN owners o ON o.id = p.owner_id
  WHERE p.id = ?
";
$stmt = $mysqli->prepare($sql);
if ($stmt === false) {
    ob_end_clean();
    die("SQL prepare error (property): " . $mysqli->error . "<br>Query: " . $sql);
}
$stmt->bind_param("i", $property_id);
$stmt->execute();
$property = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$property) {
    ob_end_clean();
    die("❌ Property not found.");
}

// ---------------- FETCH BILLS ----------------
$sql = "
  SELECT tb.id AS bill_id, tb.tax_year, tb.rptsp_no,
         a.basic_tax, a.sef_tax, a.adjustments,
         (a.basic_tax + a.sef_tax + a.adjustments) AS total_due
  FROM tax_bills tb
  JOIN assessments a ON a.id = tb.assessment_id
  JOIN properties p ON p.id = a.property_id
  WHERE p.id = ?
  ORDER BY tb.tax_year DESC
";
$stmt = $mysqli->prepare($sql);
if ($stmt === false) {
    ob_end_clean();
    die("SQL prepare error (bills): " . $mysqli->error . "<br>Query: " . $sql);
}
$stmt->bind_param("i", $property_id);
$stmt->execute();
$bills_result = $stmt->get_result();
$stmt->close();

$rows = [];
while ($row = $bills_result->fetch_assoc()) {
    if (empty($row['rptsp_no'])) {
        $new_rptsp = "RPTSP-" . str_pad($row['bill_id'], 6, "0", STR_PAD_LEFT);
        $up = $mysqli->prepare("UPDATE tax_bills SET rptsp_no=? WHERE id=?");
        if ($up) {
            $up->bind_param("si", $new_rptsp, $row['bill_id']);
            $up->execute();
            $up->close();
            $row['rptsp_no'] = $new_rptsp;
        }
    }
    $rows[] = $row;
}

// ---------------- TCPDF HEADER ----------------
class NATBPDF extends TCPDF {
    public function Header() {
        $cebu_logo = __DIR__ . '/assets/images/cebu.png';
        $argao_logo = __DIR__ . '/assets/images/argao.png';
        if (file_exists($cebu_logo)) {
            $this->Image($cebu_logo, 15, 10, 20, 20, 'PNG');
        }
        if (file_exists($argao_logo)) {
            $this->Image($argao_logo, 262, 10, 20, 20, 'PNG');
        }
        $this->Ln(5);
        $this->SetFont('helvetica', '', 9);
        $this->Cell(0, 5, 'Republic of the Philippines', 0, 1, 'C');
        $this->Cell(0, 5, 'Province of Cebu', 0, 1, 'C');
        $this->Cell(0, 5, 'Municipality of Argao', 0, 1, 'C');
        $this->SetFont('helvetica', 'B', 11);
        $this->Cell(0, 8, 'OFFICE OF THE MUNICIPAL ASSESSOR', 0, 1, 'C');
        $this->Ln(3);
    }
}

// ---------------- PDF INIT ----------------
$pdf = new NATBPDF('L', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetMargins(15, 35, 15);
$pdf->AddPage();

// Title
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 8, 'TAX BILL / NOTICE OF ASSESSMENT', 0, 1, 'C');
$pdf->Ln(4);

// Property / Owner info
$pdf->SetFont('helvetica', '', 9);
$html = '
<table cellpadding="2" border="0">
<tr>
  <td width="15%"><b>Owner:</b></td><td width="35%">' . htmlspecialchars($property['owner_name']) . '</td>
  <td width="15%"><b>Address:</b></td><td width="35%">' . htmlspecialchars($property['address'] ?? '') . '</td>
</tr>
<tr>
  <td><b>TD No.:</b></td><td>' . htmlspecialchars($property['td_no']) . '</td>
  <td><b>Classification:</b></td><td>' . htmlspecialchars($property['classification']) . '</td>
</tr>
<tr>
  <td><b>Location:</b></td><td colspan="3">' . htmlspecialchars($property['location']) . '</td>
</tr>
<tr>
  <td><b>Assessed Value:</b></td><td colspan="3"><b>' . number_format((float)($property['assessed_value'] ?? 0), 2) . '</b></td>
</tr>
</table><br>';
$pdf->writeHTML($html, true, false, false, false, '');

// ---------------- Bills table ----------------
$pdf->SetFont('helvetica', '', 8);
$html = '
<table border="1" cellpadding="3" width="100%">
<tr style="background-color:#f0f0f0; font-weight:bold;" align="center">
    <th width="15%">RPTSP No.</th>
    <th width="15%">Tax Year</th>
    <th width="15%">Basic Tax</th>
    <th width="15%">SEF Tax</th>
    <th width="15%">Adjustment</th>
    <th width="25%">Total</th>
</tr>';
$totalAll = 0;
foreach ($rows as $r) {
    $html .= '<tr>
        <td align="center">' . htmlspecialchars($r['rptsp_no']) . '</td>
        <td align="center">' . htmlspecialchars($r['tax_year']) . '</td>
        <td align="right"><b>' . number_format((float)$r['basic_tax'], 2) . '</b></td>
        <td align="right"><b>' . number_format((float)$r['sef_tax'], 2) . '</b></td>
        <td align="right"><b>' . number_format((float)$r['adjustments'], 2) . '</b></td>
        <td align="right"><b>' . number_format((float)$r['total_due'], 2) . '</b></td>
    </tr>';
    $totalAll += (float)$r['total_due'];
}
$html .= '<tr style="font-weight:bold; background-color:#e6e6e6; font-size:10px;">
    <td colspan="5" align="right"><b>GRAND TOTAL:</b></td>
    <td align="right"><b><u>' . number_format($totalAll, 2) . '</u></b></td>
</tr></table>';
$pdf->writeHTML($html, true, false, false, false, '');

// ---------------- SIGNATURES ----------------
$pdf->Ln(5);
$colWidth = ($pdf->GetPageWidth() - 30) / 2;

// Top labels

// Names
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell($colWidth, 6, 'AILEEN ANGELA S. ALFOIRNON', 0, 0, 'C');
$pdf->Cell($colWidth, 6, 'MARIA LUISA C. MAGALLANES', 0, 1, 'C');

// Positions
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell($colWidth, 6, 'Municipal Assessor', 0, 0, 'C');
$pdf->Cell($colWidth, 6, 'Municipal Treasurer', 0, 1, 'C');

// ---------------- NOTES ----------------
$pdf->Ln(5);
$pdf->SetFont('helvetica', '', 7.5);
$notes = <<<EOD
NOTE:
1. Kindly inform the Assessor's Office of any error or omission.
2. This Notice pertains only to current year taxes and does not include delinquencies for previous years.
3. Payments for the entire year should be made not later than March 31st.
4. Delinquent payments are assessed a penalty of 2% per month of delinquency.
5. Disregard this notice if payment has been made.
6. Please present this Notice to the Treasurer when payment is made. Quarterly installments are due:
   Mar 31, Jun 30, Sept 30, Dec 31.
EOD;
$pdf->MultiCell(0, 0, $notes, 0, 'L');

$pdf->SetFont('helvetica', '', 9);
$pdf->Cell($colWidth, 6, 'Prepared by:', 0, 0, 'C');
$pdf->Cell($colWidth, 6, 'Received by:', 0, 1, 'C');

// Signature lines
$pdf->Cell($colWidth, 10, '___________________________', 0, 0, 'C');
$pdf->Cell($colWidth, 10, '___________________________', 0, 1, 'C');

// ---------------- OUTPUT ----------------
ob_end_clean();
$pdf->Output('NATB_' . $property['property_id'] . '.pdf', 'I');
