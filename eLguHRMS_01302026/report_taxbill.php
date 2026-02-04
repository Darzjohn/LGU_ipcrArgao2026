<?php
require 'db.php';
require_once('tcpdf/tcpdf.php');

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
       a.basic_tax, a.sef_tax, a.adjustments
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
                'address'=>$row['address'],
                'td_no'=>$row['td_no'],
                'lot_no'=>$row['lot_no'],
                'barangay'=>$row['barangay'],
                'location'=>$row['location'],
                'classification'=>$row['classification'],
                'assessed_value'=>$row['assessed_value'],
            ],
            'bills'=>[]
        ];
    }
    $properties[$pid]['bills'][] = $row;
}

// Fetch signatories
$signRes = $mysqli->query("SELECT * FROM signatories WHERE position IN ('assessor','treasurer')");
$signatories = ['assessor'=>['name'=>'','title'=>''],'treasurer'=>['name'=>'','title'=>'']];
while($row = $signRes->fetch_assoc()){
    $signatories[strtolower($row['position'])] = ['name'=>$row['name'], 'title'=>$row['title']];
}

// TCPDF class
class NATBPDF extends TCPDF {
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
    }
}

$pdf = new NATBPDF('L','mm','A4',true,'UTF-8',false);
$pdf->SetMargins(12,30,12);

foreach($properties as $pid=>$data){
    $pdf->AddPage();

    // Title
    $pdf->SetFont('helvetica','B',13);
    $pdf->Cell(0,6,'TAX BILL / NOTICE OF ASSESSMENT',0,1,'C');
    $pdf->Ln(1);

    // Property info
    $p = $data['property'];
    $pdf->SetFont('helvetica','',8.5);
    $html = '<table cellpadding="1" border="0">
    <tr><td width="20%"><b>Owner:</b></td><td width="30%">'.htmlspecialchars($p['owner_name']).'</td>
        <td width="20%"><b>Address:</b></td><td width="30%">'.htmlspecialchars($p['address']??'').'</td></tr>
    <tr><td><b>TD No:</b></td><td>'.htmlspecialchars($p['td_no']).'</td>
        <td><b>Classification:</b></td><td>'.htmlspecialchars($p['classification']).'</td></tr>
    <tr><td><b>Lot No:</b></td><td>'.htmlspecialchars($p['lot_no']).'</td>
        <td><b>Barangay:</b></td><td>'.htmlspecialchars($p['barangay']).'</td></tr>
    <tr><td><b>Location:</b></td><td colspan="3">'.htmlspecialchars($p['location']).'</td></tr>
    <tr><td><b>Assessed Value:</b></td><td colspan="3"><b>'.number_format((float)$p['assessed_value'],2).'</b></td></tr>
    </table><br>';
    $pdf->writeHTML($html,true,false,false,false,'');

    // Bills table with dynamic font
    $numBills = count($data['bills']);
    $fontSize = ($numBills>15)?7.5:8.5; // shrink font if too many bills
    $pdf->SetFont('helvetica','',$fontSize);

    $html = '<table border="1" cellpadding="2" width="100%">
    <tr style="background-color:#f0f0f0;font-weight:bold;" align="center">
        <th>RPTSP</th><th>Year</th><th>Basic</th><th>SEF</th><th>Adj</th><th>Disc</th><th>Pen</th><th>Total</th>
    </tr>';
    $totalAll=0;
    $today = new DateTime();
    $curYear = (int)$today->format('Y');
    $curMonth = (int)$today->format('n');

    foreach($data['bills'] as $r){
        $basic=(float)$r['basic_tax'];
        $sef=(float)$r['sef_tax'];
        $adj=(float)$r['adjustments'];
        $tax_year=(int)$r['tax_year'];

        // --- Discount ---
        if ($tax_year==$curYear && $curMonth<=3) {
            $discount = 0.10*($basic+$sef);
            $discountLabel = "10%";
        } else {
            $discount = 0;
            $discountLabel = "";
        }

        // --- Penalty ---
        $months_due = ($tax_year==$curYear) ? $curMonth : (($tax_year<$curYear)?($curYear-$tax_year)*12+$curMonth:0);
        $rawPenaltyRate = 0.02*$months_due;
        $appliedRate = min($rawPenaltyRate,0.72);
        $penalty = $appliedRate*($basic+$sef);
        $penaltyLabel = $appliedRate>0 ? number_format($appliedRate*100,0)."%" : "";

        // --- Total ---
        $total=$basic+$sef+$adj-$discount+$penalty;
        $totalAll+=$total;

        $html.='<tr>
            <td align="center">'.htmlspecialchars($r['rptsp_no']).'</td>
            <td align="center">'.$tax_year.'</td>
            <td align="right">'.number_format($basic,2).'</td>
            <td align="right">'.number_format($sef,2).'</td>
            <td align="right">'.number_format($adj,2).'</td>
            <td align="right">'.number_format($discount,2).' '.$discountLabel.'</td>
            <td align="right">'.number_format($penalty,2).' '.$penaltyLabel.'</td>
            <td align="right"><b>'.number_format($total,2).'</b></td>
        </tr>';
    }
    $html.='<tr style="background-color:#e6e6e6;font-weight:bold;">
        <td colspan="7" align="right">GRAND TOTAL:</td>
        <td align="right"><b>'.number_format($totalAll,2).'</b></td>
    </tr></table>';
    $pdf->writeHTML($html,true,false,false,false,'');

    // --- Signatories ---
    $pdf->Ln(2);
    $colWidth = ($pdf->GetPageWidth()-24)/2;
    $pdf->SetFont('helvetica','B',10);
    $pdf->Cell($colWidth,5,$signatories['assessor']['name'],0,0,'C');
    $pdf->Cell($colWidth,5,$signatories['treasurer']['name'],0,1,'C');
    $pdf->SetFont('helvetica','',9);
    $pdf->Cell($colWidth,5,$signatories['assessor']['title'],0,0,'C');
    $pdf->Cell($colWidth,5,$signatories['treasurer']['title'],0,1,'C');

    // --- Note ---
    $pdf->Ln(2);
    $notes = "NOTE:\n1. Kindly inform the Assessor's Office of any error or omission.\n2. This Notice pertains only to current year taxes and does not include delinquencies for previous years.\n3. Payments for the entire year should be made not later than March 31st.\n4. Delinquent payments are assessed a penalty of 2% per month of delinquency.\n5. Disregard this notice if payment has been made.\n6. Present this Notice to the Treasurer when payment is made. Quarterly installments: Mar 31, Jun 30, Sept 30, Dec 31.";
    $pdf->MultiCell(0,0,$notes,0,'L');
    $pdf->Ln(2);

    // --- Prepared/Received ---
    $pdf->Cell($colWidth,5,'Prepared by:',0,0,'C');
    $pdf->Cell($colWidth,5,'Received by:',0,1,'C');
    $pdf->Cell($colWidth,8,'___________________________',0,0,'C');
    $pdf->Cell($colWidth,8,'___________________________',0,1,'C');
}

$pdf->Output('NATB_Selected.pdf','I');
