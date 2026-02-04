<?php
require 'db.php';

$source = $_GET['source'] ?? 'assessments';
$format = $_GET['format'] ?? 'html';

if (!in_array($source, ['assessments','tax_bills','tax_billsall'])) {
    die("Invalid source.");
}

// build query per source
if ($source === 'assessments') {
    $query = "SELECT a.id, p.td_no, p.lot_no, p.location, p.barangay, 
                     o.name AS owner_name, 
                     a.basic_tax, a.sef_tax, a.adjustments, 
                     (a.basic_tax + a.sef_tax + a.adjustments) AS total_amount
              FROM assessments a
              JOIN properties p ON p.id = a.property_id
              LEFT JOIN owners o ON o.id = p.owner_id
              ORDER BY a.id DESC";
} elseif ($source === 'tax_bills') {
    $query = "SELECT tb.id, tb.tax_year, tb.total_amount, tb.status, 
                     p.td_no, o.name AS owner_name
              FROM tax_bills tb
              JOIN assessments a ON a.id = tb.assessment_id
              JOIN properties p ON p.id = a.property_id
              LEFT JOIN owners o ON o.id = p.owner_id
              ORDER BY tb.id DESC";
} else { // tax_billsall
    $query = "SELECT tb.id, tb.tax_year, tb.total_amount, tb.status, 
                     p.td_no, p.lot_no, p.location, p.barangay, 
                     p.classification, o.name AS owner_name
              FROM tax_bills tb
              JOIN assessments a ON a.id = tb.assessment_id
              JOIN properties p ON p.id = a.property_id
              LEFT JOIN owners o ON o.id = p.owner_id
              ORDER BY tb.id DESC";
}

$res = $mysqli->query($query);
if (!$res) {
    die("Query error: " . $mysqli->error);
}

if ($format === 'pdf') {
    require 'vendor/autoload.php';
    use Dompdf\Dompdf;
    use Dompdf\Options;

    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);

    // --- LOGO ---
    $logoPath = __DIR__ . "/images/logo.png"; 
    $logoBase64 = '';
    if (file_exists($logoPath)) {
        $logoData = base64_encode(file_get_contents($logoPath));
        $logoBase64 = "data:image/png;base64,{$logoData}";
    }

    $dateGenerated = date("F d, Y h:i A");

    // --- HEADER ---
    $html = "
    <div style='text-align:center;'>
        <table width='100%'>
            <tr>
                <td width='15%' style='text-align:center;'>
                    " . ($logoBase64 ? "<img src='{$logoBase64}' width='80'>" : "") . "
                </td>
                <td width='70%' style='text-align:center;'>
                    <h3 style='margin:0;'>Republic of the Philippines</h3>
                    <h2 style='margin:0;'>MUNICIPALITY OF SAMPLE TOWN</h2>
                    <h4 style='margin:0;'>Office of the Municipal Treasurer</h4>
                </td>
                <td width='15%'></td>
            </tr>
        </table>
        <hr>
        <h3 style='margin:10px 0;'>".strtoupper($source)." REPORT</h3>
    </div>
    ";

    // --- TABLE ---
    $html .= "<table border='1' cellspacing='0' cellpadding='5' width='100%' style='font-size:12px; border-collapse:collapse;'>
                <thead><tr style='background:#f0f0f0;'>";

    $first = true;
    $totalAmount = 0;
    while ($row = $res->fetch_assoc()) {
        if ($first) {
            foreach (array_keys($row) as $col) {
                $html .= "<th>".htmlspecialchars(strtoupper($col))."</th>";
            }
            $html .= "</tr></thead><tbody>";
            $first = false;
        }
        $html .= "<tr>";
        foreach ($row as $key => $val) {
            if (strtolower($key) === 'total_amount') {
                $totalAmount += (float)$val;
                $html .= "<td style='text-align:right;'>₱".number_format($val,2)."</td>";
            } else {
                $html .= "<td>".htmlspecialchars($val)."</td>";
            }
        }
        $html .= "</tr>";
    }

    // --- TOTAL ROW ---
    if ($totalAmount > 0) {
        $colspan = isset($row) ? count($row) - 1 : 1;
        $html .= "
        <tr style='font-weight:bold; background:#f9f9f9;'>
            <td colspan='{$colspan}' style='text-align:right;'>TOTAL:</td>
            <td style='text-align:right;'>₱".number_format($totalAmount,2)."</td>
        </tr>";
    }

    $html .= "</tbody></table>";

    // --- FOOTER ---
    $html .= "
    <br><br>
    <table width='100%' style='font-size:12px;'>
        <tr>
            <td style='text-align:left;'>Date Generated: <strong>{$dateGenerated}</strong></td>
        </tr>
    </table>

    <br><br><br>
    <table width='100%' style='font-size:12px;'>
        <tr>
            <td style='text-align:center;'>
                Prepared by:<br><br><br>
                _________________________<br>
                Municipal Treasurer
            </td>
            <td style='text-align:center;'>
                Certified Correct:<br><br><br>
                _________________________<br>
                Municipal Assessor
            </td>
        </tr>
    </table>
    ";

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();

    // --- PAGE NUMBERS ---
    $canvas = $dompdf->getCanvas();
    $font = $dompdf->getFontMetrics()->get_font("Helvetica", "normal");
    $canvas->page_text(50, 570, "Date: {$dateGenerated}", $font, 9, [0,0,0]);
    $canvas->page_text(750, 570, "Page {PAGE_NUM} of {PAGE_COUNT}", $font, 9, [0,0,0]);

    $dompdf->stream("{$source}_report.pdf", ["Attachment" => true]);
    exit;
}
?>
