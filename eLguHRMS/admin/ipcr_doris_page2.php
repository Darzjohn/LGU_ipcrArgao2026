<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

/** page counter (change per file) */
$current_page = 2;   // <-- set this page number
$total_pages  = 6;   // <-- set total pages

$rows2 = [
  [
    "mfo_pap_left" => "",
    "success_indicators" => "3.2.3. 100 Reminder Letter with no errors for Business with Quarterly Based Payments within one month before start of each quarter",
    "actual" => "200 / 100 Reminder Letter with no errors for Business with Quarterly Based Payments within one month before start of each quarter",
    "remarks" => "",
  ],
  [
    "mfo_pap_left" => "Technical Support Services: 4.3. System and Database Management and Backup",
    "success_indicators" => "24 of backup acted and saved to portable backup drive accurately and completely",
    "actual" => "36 / 24 backup acted and saved to portable backup drive accurately and completely",
    "remarks" => "",
  ],
  [
    "mfo_pap_left" => "PROJECTION IMPLEMENTATION AND MANAGEMENT",
    "success_indicators" => "",
    "actual" => "",
    "remarks" => "",
  ],
  [
    "mfo_pap_left" => "MANAGEMENT IN THE IMPLEMENTATION PHASE I -23 INSTALLED STARLINK 1 FOR PNP, 1 MAYORS OFFICE, 1 FOR MDRRMO AND 19 BARANGAYS",
    "success_indicators" => "100% Complete Implementation and Installation 3 Main Local Government Offices and 19 Barangays",
    "actual" => "100% Complete Implementation and Installation 3 Main Local Government Offices and 19 Barangays",
    "remarks" => "",
  ],
  [
    "mfo_pap_left" => "MANAGEMENT IN THE IMPLEMENTATION AND INSTALLATION OF 32 UNITS INTEGRATED SURVEILLANCE CCTV SYSTEM IN THE MUNICIPAL PUBLIC MARKET",
    "success_indicators" => "100% Complete Implementation and Installation of 32 Units Integrated Surveillance System CCTV System in the Municipal Public Market",
    "actual" => "100% Complete Implementation and Installation of 32 Units Integrated Surveillance System CCTV System in the Municipal Public Market",
    "remarks" => "",
  ],
  [
    "mfo_pap_left" => "MANAGEMENT IN THE IMPLEMENTATION PHASE II -26 INSTALLED STARLINK 1 FOR CNU, 1 NATURE PARK AND 24 FOR BARANGAYS",
    "success_indicators" => "100% Complete Implementation and Installation 1 for CNU, 1 for Nature Park and 24 Barangays",
    "actual" => "100% Complete Implementation and Installation 1 for CNU, 1 for Nature Park and 24 Barangays",
    "remarks" => "",
  ],
  [
    "mfo_pap_left" => "STRATEGIC FUNCTIONS",
    "success_indicators" => "",
    "actual" => "",
    "remarks" => "",
  ],
  [
    "mfo_pap_left" => "1. Ease of Doing Business Implementation using the Online Business Permit System",
    "success_indicators" => "100% Implementation of Online Business Permit and Licensing System",
    "actual" => "100% Implementation of Online Business Permit and Licensing System",
    "remarks" => "",
  ],
  [
    "mfo_pap_left" => "2. Investment and Promotions (through CMCI- Cities and Municipalities Competitive Index)",
    "success_indicators" => "100% Accomplishments of Reports based CMCI Indicators for For National and Provincial Ratings of All Municipalities and Cities within the end of the 2nd Quarter",
    "actual" => "100% Accomplishments of Reports based CMCI Indicators for For National and Provincial Ratings of All Municipalities and Cities within the end of the 2nd Quarter",
    "remarks" => "",
  ],
];
?>

<style>
.ipcr-paper {
  background: #fff !important;
  color: #000 !important;
  padding: 24px !important;
  margin: 18px auto !important;
  max-width: 1100px;
  border-radius: 8px;
  box-shadow: 0 10px 30px rgba(0,0,0,.25);
}
.ipcr-paper, .ipcr-paper *{ opacity: 1 !important; filter: none !important; }

.ipcr-table { width: 100%; border-collapse: collapse; margin-top: 12px; }
.ipcr-table th, .ipcr-table td { border: 1px solid #333; padding: 8px; vertical-align: top; font-size: 12px; }
.ipcr-table th { background: #e9eef3; text-align: center; }
.ipcr-center { text-align: center; font-weight: 700; }

.ipcr-section { background: #d9d9d9; font-weight: 700; }
.ipcr-strategic { background: #a6a65b; font-weight: 700; text-transform: uppercase; }

/* footer bar */
.ipcr-footerbar{
  margin-top: 14px;
  padding-top: 10px;
  border-top: 2px solid #111;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
}
.ipcr-pagecount{
  font-weight:700;
  font-size:12px;
  letter-spacing:.3px;
  text-transform:uppercase;
}

/* green pill buttons */
.nav-pill{
  display:inline-flex;
  align-items:center;
  gap:14px;
  height:54px;
  padding:0 22px 0 0;
  border-radius:999px;
  background:#0b6b3a;
  color:#fff;
  text-decoration:none;
  font-weight:800;
  letter-spacing:.6px;
  text-transform:uppercase;
  border:3px solid #ffffff;
  box-shadow: 0 6px 18px rgba(0,0,0,.15);
}
.nav-pill .circle{
  width:58px;
  height:58px;
  border-radius:999px;
  background:#0b6b3a;
  display:flex;
  align-items:center;
  justify-content:center;
  border:3px solid #ffffff;
  margin-left:-3px;
}
.nav-pill svg{ width:28px; height:28px; }
.nav-pill:hover{ filter:brightness(.95); }
.nav-pill:active{ transform: translateY(1px); }

/* back version */
.nav-pill.back{ padding:0 0 0 22px; }
.nav-pill.back .circle{ margin-left:0; margin-right:-3px; }

@media print { .ipcr-footerbar { display:none; } }
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
      <th>Q</th><th>E</th><th>T</th><th>A</th>
      <th></th>
    </tr>

    <?php foreach ($rows2 as $r): ?>

      <?php if ($r["mfo_pap_left"] === "PROJECTION IMPLEMENTATION AND MANAGEMENT"): ?>
        <tr><td colspan="8" class="ipcr-section"><?=h($r["mfo_pap_left"])?></td></tr>
        <?php continue; ?>
      <?php endif; ?>

      <?php if ($r["mfo_pap_left"] === "STRATEGIC FUNCTIONS"): ?>
        <tr><td colspan="8" class="ipcr-strategic"><?=h($r["mfo_pap_left"])?></td></tr>
        <?php continue; ?>
      <?php endif; ?>

      <tr>
        <td><?=nl2br(h($r["mfo_pap_left"]))?></td>
        <td><?=nl2br(h($r["success_indicators"]))?></td>
        <td><?=nl2br(h($r["actual"]))?></td>

        <td class="ipcr-center">X</td>
        <td class="ipcr-center">X</td>
        <td class="ipcr-center">X</td>
        <td class="ipcr-center">X</td>

        <td><?=nl2br(h($r["remarks"]))?></td>
      </tr>
    <?php endforeach; ?>
  </table>

  <div class="ipcr-footerbar">
    <div class="ipcr-pagecount">Page <?= (int)$current_page ?> of <?= (int)$total_pages ?></div>

    <div style="display:flex; gap:12px; align-items:center;">
      <a class="nav-pill back" href="ipcr_doris_page1.php" aria-label="Back Page">
        <span style="padding-right:6px;">Back</span>
        <span class="circle" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none">
            <path d="M14.5 5L8 12l6.5 7" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </span>
      </a>

      <a class="nav-pill" href="ipcr_doris_page3.php" aria-label="Next Page">
        <span class="circle" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none">
            <path d="M9.5 5L16 12l-6.5 7" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </span>
        <span style="padding-right:6px;">Next Page</span>
      </a>
    </div>
  </div>

</div>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
