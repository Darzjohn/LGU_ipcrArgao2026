<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

/** set page counter (change these per file) */
$current_page = 4;   // <-- THIS page number
$total_pages  = 6;   // <-- total pages

$rows_page2 = [
  ["type" => "subhead", "label" => "B. Monitoring"],
  [
    "mfo_pap_left" => "B.1 Checking of attendance",
    "success_indicators" => "100% record of attendance of personnel in both compulsory and co-curricular activities",
    "actual" => "100% record of attendance of personnel in both compulsory and co-curricular activities",
    "remarks" => "",
  ],
  [
    "mfo_pap_left" => "B.2 Coaching and Monitoring",
    "success_indicators" => "the conduct of coaching and monitoring to 100% of underperforming personnel within a rating period",
    "actual" => "1 person undergo coaching and monitoring due to underperforming personnel within a rating period",
    "remarks" => "",
  ],
  [
    "mfo_pap_left" => "B.3 Intervention for Underperforming Personnel",
    "success_indicators" => "at least 1 intervention program conducted within a rating period to address underperformance of a personnel",
    "actual" => "1 person undergo intervention program due underperformance to within a rating period",
    "remarks" => "",
  ],
  [
    "mfo_pap_left" => "B.4 Preparation of Travel Vouchers for Cash Advances, Liquidation & Refund purposes",
    "success_indicators" => "100% of Travel vouchers prepared 1 week before date of travel",
    "actual" => "100% of Travel vouchers prepared 1 week before date of travel",
    "remarks" => "",
  ],
  [
    "mfo_pap_left" => "B.5 Office Upkeep and Maintenance",
    "success_indicators" => "zero incident report",
    "actual" => "zero incident report",
    "remarks" => "",
  ],
  [
    "mfo_pap_left" => "B.6 Action on Complaints",
    "success_indicators" => "100% of written complaints acted upon within 24 hours from receipt",
    "actual" => "100% of written complaints acted upon within 24 hours from receipt",
    "remarks" => "",
  ],

  ["type" => "subhead", "label" => "C. Evaluation"],
  [
    "mfo_pap_left" => "C.1 Submission of OPCR with ratings",
    "success_indicators" => "OPCR with rating submitted with no error on Jan. 10 and July 10 of the Fiscal Year",
    "actual" => "OPCR with rating submitted with no error on Jan. 10 and July 10 of the Fiscal Year",
    "remarks" => "",
  ],
  [
    "mfo_pap_left" => "C.2 Submission of Personnel Training Needs Summary",
    "success_indicators" => "PTN submitted with no error on the deadline set by the HR",
    "actual" => "PTN submitted with no error on the deadline set by the HR",
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

.ipcr-subhead{
  background:#d9d9d9;
  font-weight:700;
  text-transform:none;
  text-align:left;
}

/* ===== PAGE COUNTER ===== */
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

/* ===== BUTTONS (like your image) ===== */
.nav-pill{
  display:inline-flex;
  align-items:center;
  gap:14px;
  height:54px;
  padding:0 22px 0 0;
  border-radius:999px;
  background:#0b6b3a;      /* government green */
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
  margin-left:-3px; /* aligns circle edge */
}
.nav-pill svg{
  width:28px;
  height:28px;
}
.nav-pill:hover{ filter:brightness(.95); }
.nav-pill:active{ transform: translateY(1px); }

/* left version (back) */
.nav-pill.back{
  padding:0 0 0 22px;
}
.nav-pill.back .circle{
  margin-left:0;
  margin-right:-3px;
}

@media print{
  .ipcr-footerbar{ display:none; }
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

    <?php foreach ($rows_page2 as $r): ?>
      <?php if (($r["type"] ?? "") === "subhead"): ?>
        <tr><td class="ipcr-subhead" colspan="8"><?= h($r["label"]) ?></td></tr>
        <?php continue; ?>
      <?php endif; ?>

      <tr>
        <td><?= nl2br(h($r["mfo_pap_left"])) ?></td>
        <td><?= nl2br(h($r["success_indicators"])) ?></td>
        <td><?= nl2br(h($r["actual"])) ?></td>

        <td class="ipcr-center">X</td>
        <td class="ipcr-center">X</td>
        <td class="ipcr-center">X</td>
        <td class="ipcr-center">X</td>

        <td><?= nl2br(h($r["remarks"])) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>

  <!-- footer bar with page counter + buttons -->
  <div class="ipcr-footerbar">
    <div class="ipcr-pagecount">
      Page <?= (int)$current_page ?> of <?= (int)$total_pages ?>
    </div>

    <div style="display:flex; gap:12px; align-items:center;">
      <!-- BACK button pill -->
      <a class="nav-pill back" href="ipcr_doris_page3.php" aria-label="Back Page">
        <span style="padding-right:6px;">Back</span>
        <span class="circle" aria-hidden="true">
          <!-- left arrow -->
          <svg viewBox="0 0 24 24" fill="none">
            <path d="M14.5 5L8 12l6.5 7" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </span>
      </a>

      <!-- NEXT button pill -->
      <a class="nav-pill" href="ipcr_doris_page5.php" aria-label="Next Page">
        <span class="circle" aria-hidden="true">
          <!-- right arrow -->
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
