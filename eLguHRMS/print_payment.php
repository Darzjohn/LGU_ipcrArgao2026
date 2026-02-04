<?php
ob_start();
require 'db.php';
require_once('tcpdf/tcpdf.php');

// ---------------- INPUT VALIDATION ----------------
$payment_id = isset($_GET['payment_id']) ? intval($_GET['payment_id']) : 0;
if ($payment_id <= 0) {
    ob_end_clean();
    die("❌ Invalid Payment ID.");
}

// ---------------- FETCH PAYMENT + PROPERTY INFO ----------------
$sql = "
  SELECT pay.id AS payment_id, pay.or_no, pay.amount_paid, pay.payment_date, pay.tax_year,
         p.id AS property_id, p.td_no, p.lot_no, p.location, p.classification, p.assessed_value,
         o.name AS owner_name, o.address
  FROM payments pay
  JOIN properties p ON p.id = pay.property_id
  LEFT JOIN owners o ON o.id = p.owner_id
  WHERE pay.id = ?
";
$stmt = $mysqli->prepare($sql);
if ($stmt === false) {
    ob_end_clean();
    die("SQL prepare error: " . $mysqli->error);
}
$stmt->bind_param("i", $payment_id);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$payment) {
    ob_end_clean();
    die("❌ Payment not found.");
}

// ---------------- TCPDF HEADER ----------------
class ReceiptPDF extends TCPDF {
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
        $this->Cell(0, 8, 'OFFICIAL RECEIPT', 0, 1, 'C');
        $this->Ln(3);
    }
}

// ---------------- PDF INIT ----------------
$pdf = new ReceiptPDF('L', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetMargins(15, 35, 15);
$pdf->AddPage();

// Title
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 8, 'REAL PROPERTY TAX PAYMENT RECEIPT', 0, 1, 'C');
$pdf->Ln(4);

// Property / Owner info
$pdf->SetFont('helvetica', '', 9);
$html = '
<table cellpadding="2" border="0">
<tr>
  <td width="15%"><b>Owner:</b></td><td width="35%">' . htmlspecialchars($payment['owner_name']) . '</td>
  <td width="15%"><b>Address:</b></td><td width="35%">' . htmlspecialchars($payment['address'] ?? '') . '</td>
</tr>
<tr>
  <td><b>TD No.:</b></td><td>' . htmlspecialchars($payment['td_no']) . '</td>
  <td><b>Classification:</b></td><td>' . htmlspecialchars($payment['classification']) . '</td>
</tr>
<tr>
  <td><b>Location:</b></td><td colspan="3">' . htmlspecialchars($payment['location']) . '</td>
</tr>
<tr>
  <td><b>Assessed Value:</b></td><td colspan="3"><b>' . number_format((float)($payment['assessed_value'] ?? 0), 2) . '</b></td>
</tr>
</table><br>';
$pdf->writeHTML($html, true, false, false, false, '');

// ---------------- Payment Info Table ----------------
$pdf->SetFont('helvetica', '', 9);
$html = '
<table border="1" cellpadding="4" width="100%">
<tr style="background-color:#f0f0f0; font-weight:bold;" align="center">
    <th width="20%">OR Number</th>
    <th width="20%">Tax Year</th>
    <th width="20%">Payment Date</th>
    <th width="20%">Amount Paid</th>
    <th width="20%">Property ID</th>
</tr>
<tr align="center">
    <td>' . htmlspecialchars($payment['or_no']) . '</td>
    <td>' . htmlspecialchars($payment['tax_year']) . '</td>
    <td>' . htmlspecialchars($payment['payment_date']) . '</td>
    <td align="right"><b>' . number_format((float)$payment['amount_paid'], 2) . '</b></td>
    <td>' . htmlspecialchars($payment['property_id']) . '</td>
</tr>
</table>';
$pdf->writeHTML($html, true, false, false, false, '');

// ---------------- SIGNATURES ----------------
$pdf->Ln(8);
$colWidth = ($pdf->GetPageWidth() - 30) / 2;

// Names
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell($colWidth, 6, 'MARIA LUISA C. MAGALLANES', 0, 0, 'C');
$pdf->Cell($colWidth, 6, 'Taxpayer / Payor', 0, 1, 'C');

// Positions
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell($colWidth, 6, 'Municipal Treasurer', 0, 0, 'C');
$pdf->Cell($colWidth, 6, '', 0, 1, 'C');

// Signature lines
$pdf->Cell($colWidth, 10, '___________________________', 0, 0, 'C');
$pdf->Cell($colWidth, 10, '___________________________', 0, 1, 'C');

// ---------------- NOTES ----------------
$pdf->Ln(6);
$pdf->SetFont('helvetica', '', 8);
$notes = <<<EOD
NOTE:
1. This serves as the Official Receipt for your Real Property Tax payment.
2. Please keep this document for your records.
3. Penalties apply for late payments in accordance with existing tax laws.
EOD;
$pdf->MultiCell(0, 0, $notes, 0, 'L');

// ---------------- OUTPUT ----------------
ob_end_clean();
$pdf->Output('PaymentReceipt_' . $payment['payment_id'] . '.pdf', 'I');
