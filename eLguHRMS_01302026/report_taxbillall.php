<?php
require 'db.php';
require_once('tcpdf/tcpdf.php');

// Collect property IDs
$propertyIds = $_GET['property_ids'] ?? ($_GET['bills'] ?? '');
if (!$propertyIds) die("⚠️ No bill or property IDs provided.");

$idsArray = array_filter(array_map('intval', explode(',', $propertyIds)));
if (empty($idsArray)) die("⚠️ Invalid bill or property IDs.");
$idList = implode(",", $idsArray);

// Fetch bills
$sql = "
SELECT tb.id AS bill_id, tb.tax_year, tb.rptsp_no,
       p.id AS property_id, p.td_no, p.lot_no, p.location, p.barangay, p.classification,
       o.name AS owner_name, o.address, p.assessed_value,
       a.basic_tax, a.sef_tax
FROM tax_bills tb
JOIN assessments a ON a.id = tb.assessment_id
JOIN properties p ON p.id = a.property_id
LEFT JOIN owners o ON o.id = p.owner_id
WHERE tb.id IN ($idList)
ORDER BY p.id, tb.tax_year DESC";
$res = $mysqli->query($sql);
if (!$res || $res->num_rows === 0) die("⚠️ No records found for selected bills.");

// Group bills by property
$properties = [];
while ($row = $res->fetch_assoc()) {
    $pid = $row['property_id'];
    if (!isset($properties[$pid])) {
        $properties[$pid] = [
            'property'=>[
                'owner_name'=>$row['owner_name'],
                'barangay'=>$row['barangay'],
                'address'=>$row['address'],
                'td_no'=>$row['td_no'],
                'lot_no'=>$row['lot_no'],
                'location'=>$row['location'],
                'classification'=>$row['classification'],
                'assessed_value'=>$row['assessed_value'],
            ],
            'bills'=>[]
        ];
    }
    $properties[$pid]['bills'][] = $row;
}

// TCPDF Header
class ALLPDF extends TCPDF {
    public function Header(){
        $cebu_logo = __DIR__.'/assets/images/cebu.png';
        $argao_logo = __DIR__.'/assets/images/argao.png';
        if(file_exists($cebu_logo)) $this->Image($cebu_logo, 15, 10, 22, 22, 'PNG');
        if(file_exists($argao_logo)) $this->Image($argao_logo, 260, 10, 22, 22, 'PNG');
        $this->Ln(5);
        $this->SetFont('helvetica','',9);
        $this->Cell(0,4,'Republic of the Philippines',0,1,'C');
        $this->Cell(0,4,'Province of Cebu',0,1,'C');
        $this->Cell(0,4,'Municipality of Argao',0,1,'C');
        $this->SetFont('helvetica','B',11);
        $this->Cell(0,6,'OFFICE OF THE MUNICIPAL ASSESSOR',0,1,'C');
        $this->Ln(2);

        $this->SetFont('helvetica','B',13);
        $this->Cell(0,6,'CONSOLIDATED TAX BILLS REPORT',0,1,'C');
        $this->Ln(2);
    }
}

$pdf = new ALLPDF('L','mm','A4',true,'UTF-8',false);
$pdf->SetMargins(10,40,10);
$pdf->AddPage();

$pdf->SetFont('helvetica','',9);
$today = new DateTime();
$curYear = (int)$today->format('Y');
$curMonth = (int)$today->format('n');

$html = '<table border="1" cellpadding="3" width="100%">
<tr style="background-color:#f0f0f0;font-weight:bold;" align="center">
    <th>Owner</th><th>Barangay</th><th>TD No</th><th>Assessed Value</th><th>Year</th>
    <th>Basic</th><th>SEF</th><th>Tax Due</th>
    <th>Discount</th><th>Penalty (%)</th><th>Total</th>
</tr>';

$grandTotal = 0;

foreach($properties as $pid=>$data){
    $p = $data['property'];
    $propTotal = 0;

    foreach($data['bills'] as $r){
        $basic = (float)$r['basic_tax'];
        $sef = (float)$r['sef_tax'];
        $tax_year = (int)$r['tax_year'];

        // --- Tax Due ---
        $tax_due = $basic + $sef;

        // --- Discount Logic ---
        if ($tax_year > $curYear) {
            $discount = 0.20 * $tax_due;
        } elseif ($tax_year == $curYear && $curMonth <= 3) {
            $discount = 0.10 * $tax_due;
        } else {
            $discount = 0;
        }

        // --- Penalty Logic ---
        if ($tax_year < $curYear) {
            $months_due = (($curYear - $tax_year) * 12) + $curMonth;
            $penaltyRate = min(0.02 * $months_due, 0.72);
        } elseif ($tax_year == $curYear && $curMonth > 3) {
            $months_due = $curMonth;
            $penaltyRate = min(0.02 * $months_due, 0.72);
        } else {
            $penaltyRate = 0;
        }

        $penalty = $penaltyRate * $tax_due;
        $total = $tax_due - $discount + $penalty;
        $propTotal += $total;

        $html .= '<tr>
            <td>'.htmlspecialchars($p['owner_name']).'</td>
            <td>'.htmlspecialchars($p['barangay']).'</td>
            <td>'.$p['td_no'].'</td>
            <td align="right">'.number_format($p['assessed_value'],2).'</td>
            <td align="center">'.$tax_year.'</td>
            <td align="right">'.number_format($basic,2).'</td>
            <td align="right">'.number_format($sef,2).'</td>
            <td align="right">'.number_format($tax_due,2).'</td>
            <td align="right">'.number_format($discount,2).'</td>
            <td align="right">'.number_format($penalty,2).' ('.($penaltyRate*100).'%)</td>
            <td align="right"><b>'.number_format($total,2).'</b></td>
        </tr>';
    }

    $grandTotal += $propTotal;

    $html .= '<tr style="background-color:#d0e0ff;font-weight:bold;">
        <td colspan="10" align="right">TOTAL for '.$p['owner_name'].' (TD: '.$p['td_no'].'):</td>
        <td align="right">'.number_format($propTotal,2).'</td>
    </tr>';
}

$html .= '<tr style="background-color:#b0c4de;font-weight:bold;font-size:11px;">
    <td colspan="10" align="right">GRAND TOTAL:</td>
    <td align="right">'.number_format($grandTotal,2).'</td>
</tr></table>';

$pdf->writeHTML($html,true,false,false,false,'');
$pdf->Output('TaxBills_All.pdf','I');
