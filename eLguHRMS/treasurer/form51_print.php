<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../tcpdf/tcpdf.php';

$id = intval($_GET['id']); // always sanitize input

// Fetch Form51 record
$f = $mysqli->query("SELECT * FROM form51 WHERE id='$id'")->fetch_assoc();
if(!$f){
    die("Form 51 record not found.");
}

// Fetch Form51 items/payments
$payments = $mysqli->query("SELECT fi.*, ns.nature_of_collection, ns.ngas_code 
                            FROM form51_items fi
                            LEFT JOIN ngas_settings ns ON fi.ngas_id = ns.id
                            WHERE fi.form51_id='$id'")->fetch_all(MYSQLI_ASSOC);

// ✅ Function to convert numbers to words (supports decimals)
function convertNumberToWords($number) {
    $no = floor($number);
    $decimal = round(($number - $no) * 100);
    $words = convertIntegerToWords($no);
    if ($decimal > 0) {
        $words .= " and " . convertIntegerToWords($decimal) . " centavos";
    }
    return $words;
}

function convertIntegerToWords($num) {
    $ones = ['', 'One','Two','Three','Four','Five','Six','Seven','Eight','Nine'];
    $tens = ['', '', 'Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];
    $teens = ['Ten','Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen','Seventeen','Eighteen','Nineteen'];

    if ($num == 0) return 'Zero';
    $word = '';

    if ($num >= 1000) {
        $word .= convertIntegerToWords(floor($num/1000)) . ' Thousand ';
        $num %= 1000;
    }
    if ($num >= 100) {
        $word .= $ones[floor($num/100)] . ' Hundred ';
        $num %= 100;
    }
    if ($num >= 20) {
        $word .= $tens[floor($num/10)] . ' ';
        $num %= 10;
    } elseif ($num >= 10) {
        $word .= $teens[$num-10] . ' ';
        $num = 0;
    }
    if ($num > 0) $word .= $ones[$num] . ' ';
    return trim($word);
}

// Create PDF
$pdf = new TCPDF('P','mm',array(140,266), true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Form51');
$pdf->AddPage();

// Build HTML
$html = "<h3>Form 51</h3>";
$html .= "<p>Payor Name: {$f['payor_name']}<br>Date Issued: {$f['date_issued']}</p>";
$html .= "<table border='1' cellpadding='4'>
<tr>
<th>NGAS Code</th>
<th>Nature of Collection</th>
<th>Amount</th>
</tr>";

$grandTotal = 0;
foreach($payments as $p){
    $html .= "<tr>
    <td>{$p['ngas_code']}</td>
    <td>{$p['nature_of_collection']}</td>
    <td>₱".number_format($p['amount'],2)."</td>
    </tr>";
    $grandTotal += $p['amount'];
}

$html .= "<tr>
<td colspan='2'><b>Grand Total</b></td>
<td><b>₱".number_format($grandTotal,2)."</b></td>
</tr>";
$html .= "</table>";

// ✅ Add Amount in Words
$amountInWords = convertNumberToWords($grandTotal);
$html .= "<p><b>Amount in Words:</b> ₱ {$amountInWords}</p>";

// Output PDF
$pdf->writeHTML($html,true,false,true,false,'');
$pdf->Output('Form51_'.$id.'.pdf','I');
?>
