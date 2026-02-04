<?php
require_once __DIR__. '/../auth/session_check.php';
require_once __DIR__. '/../config/db.php';
require_once __DIR__. '/../config/settings.php';
require_once __DIR__. '/../layouts/header.php';
require_once __DIR__. '/../layouts/sidebar.php';

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$header = [
  "country" => "REPUBLIC OF THE PHILIPPINES",
  "province" => "PROVINCE OF CEBU",
  "municipality" => "MUNICIPALITY OF ARGAO",
  "office" => "OFFICE OF THE LOCAL CHIEF EXECUTIVE",
  "telefax" => "TELEFAX: (032) 3677-542",
  "email" => "email: argaomunicipality@gmail.com",
  "title" => "INDIVIDUAL PERFORMANCE COMMITMENT AND REVIEW (IPCR)  JANUARY - JUNE 2025",
];

$meta = [
  "statement" =>
    "I, CHARLTON H. TEO, Local Assessment and Operation Officer II/ IT Designate of the Municipality of Argao, commit to deliver and agree to be rated on the attainment of the following targets in accordance with the indicated measures for the period  JANUARY - JUNE 2025",
  "ratee_name" => "CHARLTON H. TEO",
];

$signatories = [
  "prepared_by_name" => "CHARLTON H. TEO",
  "prepared_by_position" => "LOCAL ASSESSMENT OFFICER II / IT DESIGNATE",
  "approved_by_name" => "ALLAN M. SESALDO",
  "approved_by_position" => "Local Chief Executive",
];

$rows = [
  [
    "mfo_pap_left" => "3.1. Business Permit and Licenses Services",
    "success_indicators" => "3.1.1. 500 business permit applications acted accurately with no errors prepared/checked/verified/assessed within 1 hour from receipt",
    "actual" => "1100/500 (P10,000) business permit applications acted accurately with no errors prepared/checked/verified/assessed within 30 minutes from receipt",
    "remarks" => "",
  ],
  [
    "mfo_pap_left" => "",
    "success_indicators" => "3.1.3. 300 business permit applications with no errors with complete requirements approved and released within 1 hour from receipt",
    "actual" => "500/300 business permit applications with no errors with complete requirements approved and released within 30 minutes from receipt",
    "remarks" => "",
  ],
  [
    "mfo_pap_left" => "3.2. Business Permit and Licenses Inspection and Monitoring",
    "success_indicators" => "3.2.1. 45 Brgys with business establishments were inspected within the year",
    "actual" => "45/45 Brgys with business establishments were inspected within the year",
    "remarks" => "",
  ],
  [
    "mfo_pap_left" => "",
    "success_indicators" => "3.2.2. 100 Notice of Delinquency with no errors for Delinquent Businesses within February",
    "actual" => "180/100 Notice of Delinquency with no errors for Delinquent Businesses within February",
    "remarks" => "",
  ],
];
?>

<style>
/* ===== FIX DARK BACKGROUND / OVERLAY FROM TEMPLATE ===== */
.ipcr-paper {
  background: #fff !important;
  color: #000 !important;
  padding: 24px !important;
  margin: 18px auto !important;
  max-width: 1100px;
  border-radius: 8px;
  box-shadow: 0 10px 30px rgba(0,0,0,.25);
}

/* Stop template from dimming content */
.ipcr-paper, .ipcr-paper *{
  opacity: 1 !important;
  filter: none !important;
}

/* ===== IPCR STYLES ===== */
.ipcr-header { text-align: center; line-height: 1.25; }
.ipcr-small { font-size: 12px; }
.ipcr-title { margin-top: 10px; font-weight: 700; font-size: 14px; }
.ipcr-meta { margin-top: 12px; font-size: 13px; }

.ipcr-boxgrid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  margin-top: 12px;
}
.ipcr-box { border: 1px solid #333; padding: 10px; min-height: 70px; }

.ipcr-table { width: 100%; border-collapse: collapse; margin-top: 12px; }
.ipcr-table th, .ipcr-table td { border: 1px solid #333; padding: 8px; vertical-align: top; font-size: 12px; }
.ipcr-table th { background: #e9eef3; text-align: center; }
.ipcr-subhead { background: #f4d6a6; font-weight: 700; text-transform: uppercase; text-align: left; }
.ipcr-center { text-align: center; font-weight: 700; }

.ipcr-mutedline { border-top: 1px solid #333; margin-top: 20px; }
.ipcr-sigline { margin: 16px auto 6px; width: 75%; border-top: 1px solid #111; }

@media print {
  .ipcr-paper { box-shadow: none; margin: 0; border-radius: 0; }
  tr { page-break-inside: avoid; }
}
</style>

<div class="ipcr-paper">

  <div class="ipcr-header">
    <div style="font-weight:700;"><?=h($header["country"])?></div>
    <div style="font-weight:700;"><?=h($header["province"])?></div>
    <div style="font-weight:700;"><?=h($header["municipality"])?></div>
    <div style="font-weight:700;"><?=h($header["office"])?></div>
    <div class="ipcr-small"><?=h($header["telefax"])?></div>
    <div class="ipcr-small"><?=h($header["email"])?></div>
    <div class="ipcr-title"><?=h($header["title"])?></div>
  </div>

  <div class="ipcr-meta">
    <?=h($meta["statement"])?>
  </div>

  <div class="ipcr-meta" style="margin-top:10px; display:flex; justify-content:flex-end;">
    <div style="text-align:center; min-width:280px;">
      <div class="ipcr-sigline"></div>
      <div><b><?=h($meta["ratee_name"])?></b></div>
      <div class="ipcr-small">Ratee</div>
    </div>
  </div>

  <div class="ipcr-boxgrid">
    <div class="ipcr-box">
      <div><b>Prepared by:</b></div>
      <div class="ipcr-sigline"></div>
      <div style="text-align:center;"><b><?=h($signatories["prepared_by_name"])?></b></div>
      <div style="text-align:center;" class="ipcr-small"><?=h($signatories["prepared_by_position"])?></div>
      <div class="ipcr-mutedline"></div>
      <div class="ipcr-small" style="text-align:center;">Date</div>
    </div>

    <div class="ipcr-box">
      <div><b>Approved by:</b></div>
      <div class="ipcr-sigline"></div>
      <div style="text-align:center;"><b><?=h($signatories["approved_by_name"])?></b></div>
      <div style="text-align:center;" class="ipcr-small"><?=h($signatories["approved_by_position"])?></div>
      <div class="ipcr-mutedline"></div>
      <div class="ipcr-small" style="text-align:center;">Date</div>
    </div>
  </div>

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
      <th>Q</th><th>E</th><th>T</th><th>A</th>
      <th></th>
    </tr>

    <tr>
      <td class="ipcr-subhead" colspan="8">CORE FUNCTIONS</td>
    </tr>

    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?=nl2br(h($r["mfo_pap_left"]))?></td>
        <td><?=nl2br(h($r["success_indicators"]))?></td>
        <td><?=nl2br(h($r["actual"]))?></td>

        <!-- Q E T A fixed to X -->
        <td class="ipcr-center">X</td>
        <td class="ipcr-center">X</td>
        <td class="ipcr-center">X</td>
        <td class="ipcr-center">X</td>

        <td><?=nl2br(h($r["remarks"]))?></td>
      </tr>
    <?php endforeach; ?>
  </table>

</div>

<?php
require_once __DIR__. '/../layouts/footer.php';
?>
