<?php
/**
 * print_rcd_perday.php - TCPDF version (Exact Form Layout per uploaded PDFs)
 *
 * - Front:  "1. For Collectors" (serial From/To table) + "2. For Liquidating Officers/Treasurers"
 * - Back:   Accountability box + Summary of Collections and Remittances/Deposits
 *
 * TCPDF expected at: __DIR__ . '/tcpdf/tcpdf.php'
 *
 * (This version implements Option 1 — exact match to your uploaded front/back samples.)
 * References: front sample and back sample uploaded by user. 
 */

ob_start();
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';

// Role restriction
if (!in_array($_SESSION['role'], ['admin','treasurer','cashier'])) {
    echo "Unauthorized."; exit;
}

// helper for prepared queries
function q($sql, $params = []) {
    global $mysqli;
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) die("SQL Prepare Error: ".$mysqli->error);
    if (!empty($params)) {
        $types = '';
        foreach ($params as $p) {
            if (is_int($p)) $types .= 'i';
            elseif (is_float($p) || is_double($p)) $types .= 'd';
            else $types .= 's';
        }
        $stmt->bind_param($types, ...array_values($params));
    }
    if (!$stmt->execute()) die("SQL Exec Error: ".$stmt->error);
    return $stmt;
}

$date = $_GET['date'] ?? '';
if (!$date) {
    echo "<p style='font-family:Arial; padding:20px;'>No date specified. Use ?date=YYYY-MM-DD</p>";
    exit;
}

// fetch remittance rows for the date
$sql = "SELECT * FROM remittance WHERE remittance_date = ? ORDER BY created_by ASC, id ASC";
$stmt = q($sql, [$date]);
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// group by collector (created_by)
$byCollector = [];
$grandTotal = 0.0;
foreach ($rows as $r) {
    $collector = trim($r['created_by'] ?? '') ?: 'Unknown';
    if (!isset($byCollector[$collector])) $byCollector[$collector] = ['rows'=>[], 'subtotal'=>0.0];
    $byCollector[$collector]['rows'][] = $r;
    $amt = floatval($r['total_paid'] ?? 0);
    $byCollector[$collector]['subtotal'] += $amt;
    $grandTotal += $amt;
}

// header info (from settings or defaults)
$municipality = trim($_CONFIG['municipality'] ?? '') ?: 'MUNICIPALITY OF ARGAO';
$office = trim($_CONFIG['office'] ?? '') ?: "MUNICIPAL TREASURER'S OFFICE";
$fund = 'GENERAL';
$accountable_officer = $_SESSION['name'] ?? 'ACCOUNTABLE OFFICER';

function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

// Build HTML that mirrors the uploaded RCD front/back (Option 1)
ob_start();
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>RCD - <?=h($date)?></title>
<style>
    body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size:11px; color:#000; margin:0; }
    .center { text-align:center; }
    .right { text-align:right; }
    .bold { font-weight:700; }
    table { border-collapse: collapse; width:100%; }
    th, td { border:1px solid #000; padding:4px; vertical-align:top; }
    .noborder td, .noborder th { border: none; padding:2px; }
    .header-small { font-size:10px; margin-bottom:4px; }
    .no-print { margin:6px; }
    .small { font-size:10px; }
    /* specific widths tuned for long-bond page, hybrid approach */
    .w-8 { width:8%; } .w-16 { width:16%; } .w-18 { width:18%; } .w-36 { width:36%; } .w-12 { width:12%; }
    .sigline { border-top:1px solid #000; width:80%; display:inline-block; padding-top:4px; margin-top:6px;}
</style>
</head>
<body>

<!-- Browser controls -->
<div class="no-print">
    <button onclick="window.print()">Print (Browser)</button>
    &nbsp;<a href="remittance_list.php">Back</a>
</div>

<!-- FRONT PAGE -->
<div style="padding:8px;">
    <div class="center">
        <div class="bold" style="font-size:15px;"><?=h($municipality)?></div>
        <div class="bold" style="font-size:13px;"><?=h($office)?></div>
        <div class="bold" style="font-size:14px; margin-top:6px;">REPORT OF COLLECTIONS AND DEPOSITS</div>
    </div>

    <table class="noborder" style="margin-top:8px;">
        <tr>
            <td><b>Fund:</b> <?=h($fund)?></td>
            <td class="right"><b>Date:</b> <?=date('F j, Y', strtotime($date))?></td>
        </tr>
        <tr>
            <td><b>Name of Accountable Officer:</b> <?=h($accountable_officer)?></td>
            <td class="right"><b>Report No.:</b> ____________________</td>
        </tr>
    </table>

    <hr style="margin:6px 0 8px 0; border:none; border-top:1px solid #000;" />

    <!-- A. COLLECTIONS: Part 1 - For Collectors (From / To large table) -->
    <div class="bold">A. COLLECTIONS</div>
    <div class="small" style="margin-top:6px;"><b>1. For Collectors</b></div>

    <!-- Create a big From/To serial table like the uploaded sample (many small cells) -->
    <table style="margin-top:6px;">
        <thead>
            <tr>
                <th style="width:12%;">Type (Form No.)</th>
                <th style="width:19%;">Official Receipt/Serial No. (From)</th>
                <th style="width:19%;">Official Receipt/Serial No. (To)</th>
                <th style="width:25%;">Amount</th>
                <th style="width:25%;">Remarks</th>
            </tr>
        </thead>
        <tbody>
            <!-- We don't have per-serial data, leave blank rows for manual filling (mimic uploaded) -->
            <?php for($i=0;$i<8;$i++): ?>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <?php endfor; ?>
        </tbody>
    </table>

    <!-- A. COLLECTIONS: Part 2 - Liquidating Officers / Treasurers -->
    <div class="small" style="margin-top:10px;"><b>2. For Liquidating Officers / Treasurers</b></div>

    <table style="margin-top:6px;">
        <thead>
            <tr>
                <th style="width:60%;">Name (Liquidating Officer / Treasurer)</th>
                <th style="width:40%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Show grouped subtotals as the Liquidating Officers/Treasurers list (if available).
            // This matches the uploaded front sample where names are listed with amounts. :contentReference[oaicite:3]{index=3}
            if (empty($byCollector)) {
                echo "<tr><td colspan='2' class='center'>No collections</td></tr>";
            } else {
                foreach ($byCollector as $collector => $data) {
                    echo "<tr>";
                    echo "<td>".h($collector)."</td>";
                    echo "<td class='right'>₱".number_format($data['subtotal'],2)."</td>";
                    echo "</tr>";
                }
                // total row
                echo "<tr class='subtotal'>";
                echo "<td class='right'><b>Total Collection</b></td>";
                echo "<td class='right'>₱".number_format($grandTotal,2)."</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>

    <!-- B. REMITTANCES/DEPOSITS placeholder (mirrors sample) -->
    <div style="margin-top:10px;"><b>B. REMITTANCES/DEPOSITS</b></div>
    <table style="margin-top:6px;">
        <thead>
            <tr>
                <th style="width:40%;">Reference</th>
                <th style="width:40%;">Accountable Officer / Bank</th>
                <th style="width:20%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
            <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
        </tbody>
    </table>

    <!-- Detailed lists by collector (optional, keep brief or expand as needed) -->
    <?php if (!empty($byCollector)): ?>
        <div style="margin-top:10px;"><b>Detailed Collections (per OR / entry)</b></div>
        <?php foreach ($byCollector as $collector => $data): ?>
            <div style="margin-top:6px;">
                <div class="bold"><?=h($collector)?></div>
                <table style="margin-top:4px;">
                    <thead>
                        <tr>
                            <th class="w-8">#</th>
                            <th class="w-16">OR No</th>
                            <th class="w-18">Form No</th>
                            <th class="w-36">Payor / Nature</th>
                            <th class="w-12">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $idx = 1;
                        foreach ($data['rows'] as $row) {
                            $or = $row['or_no'] ?? '';
                            $form_no = $row['form_no'] ?? '';
                            $payor = $row['payor'] ?? ($row['payor_name'] ?? ($row['payer'] ?? ''));
                            $amt = floatval($row['total_paid'] ?? 0);
                            echo "<tr>";
                            echo "<td class='center'>".$idx."</td>";
                            echo "<td>".h($or)."</td>";
                            echo "<td>".h($form_no)."</td>";
                            echo "<td>".h($payor)."</td>";
                            echo "<td class='right'>₱".number_format($amt,2)."</td>";
                            echo "</tr>";
                            $idx++;
                        }
                        echo "<tr class='subtotal'>";
                        echo "<td colspan='4' class='right'><b>Subtotal (".h($collector).")</b></td>";
                        echo "<td class='right'>₱".number_format($data['subtotal'],2)."</td>";
                        echo "</tr>";
                        ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Grand total -->
    <div style="margin-top:8px; text-align:right; font-weight:700;">GRAND TOTAL: ₱<?=number_format($grandTotal,2)?></div>
</div> <!-- end front -->

<!-- BACK PAGE: keep layout consistent with uploaded back sample (accountability + summary). :contentReference[oaicite:4]{index=4} -->
<div style="page-break-before:always; padding:8px;">

    <div class="center bold" style="margin-bottom:8px;">(BACK) ACCOUNTABILITY FOR ACCOUNTABLE FORMS &amp; SUMMARY OF COLLECTIONS AND REMITTANCES / DEPOSITS</div>

    <!-- Accountability for accountable forms -->
    <div style="margin-bottom:8px;">
        <div class="bold">C. ACCOUNTABILITY FOR ACCOUNTABLE FORMS</div>
        <table style="margin-top:6px;">
            <tr>
                <td style="width:50%; vertical-align:top; padding:6px;">
                    <div><strong>Name of Form &amp; No.</strong></div>
                    <div style="height:60mm; border:1px solid #000; margin-top:6px;">&nbsp;</div>
                </td>
                <td style="width:50%; vertical-align:top; padding:6px;">
                    <div style="height:60mm; border:1px solid #000;">&nbsp;</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Summary of collections and remittances/deposits -->
    <div>
        <div class="bold">D. SUMMARY OF COLLECTIONS AND REMITTANCES/DEPOSITS</div>

        <table style="margin-top:6px;">
            <thead>
                <tr>
                    <th style="width:25%;">Beginning Balance</th>
                    <th style="width:25%;">Add: Collections (Cash / Checks)</th>
                    <th style="width:25%;">Total</th>
                    <th style="width:25%;">Less: Remittance/Deposit</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="height:26mm; vertical-align:top;">&nbsp;</td>
                    <td class="right" style="vertical-align:top;">₱<?=number_format($grandTotal,2)?></td>
                    <td style="vertical-align:top;">&nbsp;</td>
                    <td style="vertical-align:top;">&nbsp;</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Certification and signatures -->
    <div style="margin-top:10px;">
        <strong>CERTIFICATION: VERIFICATION AND ACKNOWLEDGEMENT</strong>
        <p style="margin-top:6px;">
            I do hereby certify that the foregoing report of collections and deposits and accountability for accountable forms is true and correct.
        </p>

        <table class="noborder" style="width:100%; margin-top:20px;">
            <tr>
                <td class="center">
                    <div class="sigline"><?=h($accountable_officer)?></div>
                    <div>Accountable Officer</div>
                </td>
                <td class="center">
                    <div class="sigline">&nbsp;</div>
                    <div>Municipal Treasurer</div>
                </td>
                <td class="center">
                    <div class="sigline">&nbsp;</div>
                    <div>Approving Authority</div>
                </td>
            </tr>
        </table>
    </div>

</div> <!-- end back -->

</body>
</html>
<?php
// collect HTML
$html = ob_get_clean();
ob_end_clean(); // clear buffers to avoid TCPDF warnings

// If PDF requested, generate via TCPDF (path: treasurer/tcpdf/tcpdf.php)
if (isset($_GET['pdf']) && $_GET['pdf'] == '1') {

    $tcpdf_path = __DIR__ . '/tcpdf/tcpdf.php';
    if (!file_exists($tcpdf_path)) {
        echo "<p style='font-family:Arial; padding:20px;'>TCPDF not found at <code>{$tcpdf_path}</code>.<br>";
        echo "Place the tcpdf folder in the same directory as this file (treasurer/tcpdf/tcpdf.php).</p>";
        echo $html;
        exit;
    }

    require_once $tcpdf_path;

    // create TCPDF for long-bond (216 x 330 mm)
    $pdf = new TCPDF('P', 'mm', array(216,330), true, 'UTF-8', false);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(10, 10, 10);
    $pdf->SetAutoPageBreak(TRUE, 10);

    // Use DejaVu Sans for UTF-8
    $pdf->SetFont('dejavusans', '', 11);

    $pdf->AddPage();

    // write the HTML
    $pdf->writeHTML($html, true, false, true, false, '');

    // stream PDF inline
    $filename = 'RCD_' . str_replace('-', '', $date) . '.pdf';
    $pdf->Output($filename, 'I');
    exit;
}

// Otherwise print HTML for browser
echo $html;
