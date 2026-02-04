<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

/**
 * PAGE (Based on your image): C.3 + OTHER SUPPORT FUNCTIONS (10%)
 * NOTE: Q/E/T/A are DISPLAY ONLY = "X" (no inputs)
 */
$rows_page3 = [
  // C.3 row (still under Evaluation in your form)
  [
    "mfo_pap_left" => "C.3 Submission of Quarterly Accomplishment Report",
    "success_indicators" => "Complete Quarterly Accomplishment Report submitted to the LCE office on the deadline",
    "actual" => "Complete Quarterly Accomplishment Report submitted to the LCE office on the deadline",
    "remarks" => "",
  ],

  // Orange header row in the image
  ["type" => "orange_head", "label" => "OTHER SUPPORT FUNCTIONS (10%)"],

  [
    "mfo_pap_left" => "1. Attendance to flag ceremonies",
    "success_indicators" => "100% attendance to flag ceremonies",
    "actual" => "22 / 24 attendance to flag ceremonies",
    "remarks" => "",
  ],
  [
    "mfo_pap_left" => "2. Liquidation of Cash Advances",
    "success_indicators" => "Travel/training/seminar-workshop, paper presentations and other activities expenditures are 100% liquidated within the required no. of working days",
    "actual" => "100% liquidated within the required no. of working days the Travel/training/seminar-workshop, paper presentations and other activities expenditures are",
    "remarks" => "",
  ],
  [
    "mfo_pap_left" => "3. Submission of SALN",
    "success_indicators" => "SALN submitted to the HR office before on March 31st of the year",
    "actual" => "SALN submitted to the HR office before on March 31st of the year",
    "remarks" => "",
  ],
  [
    "mfo_pap_left" => "4. Submission of DTR",
    "success_indicators" => "Signed DTR is submitted to the HRO 2 days after release from the IT office",
    "actual" => "Signed DTR is submitted to the HRO 2 days after release from the IT office",
    "remarks" => "",
  ],
  [
    "mfo_pap_left" => "5. Compliance to RA 9114 (No-Smoking Law)",
    "success_indicators" => "100% compliance to the no-smoking policy in the working area and public places within the rating period",
    "actual" => "100% complied to the no-smoking policy in the working area and public places within the rating period",
    "remarks" => "",
  ],
  [
    "mfo_pap_left" => "6. Compliance to RA 9165 (Comprehensive Dangerous Drugs Act of 2002)",
    "success_indicators" => "100% compliance to the Comprehensive Anti-Drug Law within the rating period",
    "actual" => "100% complied to the Comprehensive Anti-Drug Law within the rating period",
    "remarks" => "",
  ],
  [
    "mfo_pap_left" => "7. Compliance to RA 9003",
    "success_indicators" => "100% compliance to RA 9003 in the working area and at Home within the rating period",
    "actual" => "100% complied to RA 9003 in the working area and at Home within the rating period",
    "remarks" => "",
  ],
  [
    "mfo_pap_left" => "8. Wearing of IDs and office uniforms",
    "success_indicators" => "100% compliance to office policy on the wearing of IDs and office uniforms within the rating period",
    "actual" => "100% complied to office policy on the wearing of IDs and office uniforms within the rating period",
    "remarks" => "",
  ],
  [
    "mfo_pap_left" => "9. CSC Health and Wellness Program",
    "success_indicators" => "100% attendance to the Health and Wellness Program of the office policy within the rating period",
    "actual" => "Always present to the Health and Wellness Program of the office policy within the rating period",
    "remarks" => "",
  ],
];
?>

<style>
.ipcr-paper{
  background:#fff !important;
  color:#000 !important;
  padding:24px !important;
  margin:18px auto !important;
  max-width:1100px;
  border-radius:8px;
  box-shadow:0 10px 30px rgba(0,0,0,.25);
}
.ipcr-paper, .ipcr-paper *{ opacity:1 !important; filter:none !important; }

.ipcr-table{ width:100%; border-collapse:collapse; margin-top:12px; table-layout:fixed; }
.ipcr-table th, .ipcr-table td{ border:1px solid #333; padding:8px; vertical-align:top; font-size:12px; }
.ipcr-table th{ background:#e9eef3; text-align:center; }

.ipcr-center{ text-align:center; font-weight:700; vertical-align:middle; }

.ipcr-orange{
  background:#d09a63; /* orange-ish like your paper */
  font-weight:700;
  text-transform:uppercase;
}

.btns{ margin-top:14px; display:flex; gap:10px; }
.btn{
  padding:10px 14px;
  border:1px solid #333;
  background:#fff;
  cursor:pointer;
  text-decoration:none;
  color:#000;
  border-radius:6px;
}
</style>

<div class="ipcr-paper">

  <table class="ipcr-table">
    <tr>
      <th style="width:20%;">MFO/PAP</th>
      <th style="width:28%;">SUCCESS INDICATORS<br>(TARGETS + MEASURES)</th>
      <th style="width:30%;">Actual Accomplishments / Expenses</th>
      <th colspan="4" style="width:14%;">Rating*</th>
      <th style="width:8%;">Remarks</th>
    </tr>
    <tr>
      <th></th><th></th><th></th>
      <th style="width:3.5%;">Q</th>
      <th style="width:3.5%;">E</th>
      <th style="width:3.5%;">T</th>
      <th style="width:3.5%;">A</th>
      <th></th>
    </tr>

    <?php foreach ($rows_page3 as $r): ?>

      <?php if (($r["type"] ?? "") === "orange_head"): ?>
        <tr>
          <td class="ipcr-orange" colspan="8"><?= h($r["label"]) ?></td>
        </tr>
        <?php continue; ?>
      <?php endif; ?>

      <tr>
        <td><?= nl2br(h($r["mfo_pap_left"])) ?></td>
        <td><?= nl2br(h($r["success_indicators"])) ?></td>
        <td><?= nl2br(h($r["actual"])) ?></td>

        <!-- Rating Q/E/T/A are LABELS only (X) -->
        <td class="ipcr-center">X</td>
        <td class="ipcr-center">X</td>
        <td class="ipcr-center">X</td>
        <td class="ipcr-center">X</td>

        <td><?= nl2br(h($r["remarks"])) ?></td>
      </tr>

    <?php endforeach; ?>
  </table>

  <div class="btns">
    <a class="btn" href="ipcr_doris_page4.php">⬅ Back</a>
   
  </div>

</div>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
