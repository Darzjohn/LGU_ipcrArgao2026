<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$rows3 = [
  // MAIN SECTION HEADER
  [
    "type" => "section",
    "label" => "SUPERVISORY FUNCTIONS (20%)",
  ],

  // SUB HEADER
  [
    "type" => "sub",
    "left" => "A. Planning",
  ],

  // A.1
  [
    "mfo_pap_left" => "A.1 Regular Organizational Meeting",
    "success_indicators" => "at least 1 organizational meeting conducted every month",
    "actual" => "8 / 6 conducted organizational meeting every month",
    "q" => "4", "e" => "", "t" => "4", "a" => "4.00",
    "remarks" => "",
  ],

  // A.2
  [
    "mfo_pap_left" => "A.2 Submission of Plans and Programs",
    "success_indicators" => "100% of required plans submitted on the deadline",
    "actual" => "100% required plans submitted on deadline",
    "q" => "4", "e" => "", "t" => "4", "a" => "4.00",
    "remarks" => "",
  ],

  // A.3
  [
    "mfo_pap_left" => "A.3 Submission of Budget Proposal",
    "success_indicators" => "Proposal submitted with no error before July 15 of the fiscal year",
    "actual" => "Proposal submitted with no error before July 15 of the fiscal year",
    "q" => "4", "e" => "", "t" => "4", "a" => "4.00",
    "remarks" => "",
  ],

  // A.4
  [
    "mfo_pap_left" => "A.4 Attendance to Meeting/Trainings",
    "success_indicators" => "100% attendance to meeting/training as required by the local chief executive",
    "actual" => "100% attendance to meeting/training as required by the local chief executive",
    "q" => "4", "e" => "", "t" => "3", "a" => "3.50",
    "remarks" => "",
  ],

  // A.5
  [
    "mfo_pap_left" => "A.5 Submission of OPCR with targets",
    "success_indicators" => "OPCR with targets prepared and submitted with no error on May 30 and Nov. 30 of the Fiscal Year",
    "actual" => "submitted OPCR with targets prepared and with no error on Before May 30 and Nov. 30 of the Fiscal Year",
    "q" => "4", "e" => "", "t" => "3", "a" => "3.50",
    "remarks" => "",
  ],

  // A.6
  [
    "mfo_pap_left" => "A.6 Submission of PPMP",
    "success_indicators" => "PPMP submitted with no error on the deadline set by the Budget Officer",
    "actual" => "submitted PPMP with no error on the deadline set by the Budget Officer",
    "q" => "4", "e" => "", "t" => "4", "a" => "4.00",
    "remarks" => "",
  ],

  // A.7
  [
    "mfo_pap_left" => "A.7 Submission of Purchase Requests (office supplies)",
    "success_indicators" => "Purchase requests for office supplies submitted on the 1st month of every quarter",
    "actual" => "submitted Purchase requests for office supplies on the 1st month of every quarter",
    "q" => "5", "e" => "3", "t" => "4", "a" => "4.00",
    "remarks" => "",
  ],

  // A.8
  [
    "mfo_pap_left" => "A.8 Submission of Purchase Requests (for other needs)",
    "success_indicators" => "Purchase requests for other needs submitted 2 weeks before the activity/event/others",
    "actual" => "Submitted 2 weeks before the Purchase Request for Any Activities, Events and Others",
    "q" => "4", "e" => "3", "t" => "4", "a" => "3.67",
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
.ipcr-paper, .ipcr-paper *{
  opacity: 1 !important;
  filter: none !important;
}

.ipcr-table { width: 100%; border-collapse: collapse; margin-top: 12px; }
.ipcr-table th, .ipcr-table td { border: 1px solid #333; padding: 8px; vertical-align: top; font-size: 12px; }
.ipcr-table th { background: #e9eef3; text-align: center; }
.ipcr-center { text-align: center; font-weight: 700; }

.ipcr-section {
  background: #bcd7ee; /* light blue like image header */
  font-weight: 700;
  text-transform: uppercase;
}
.ipcr-sub {
  background: #efefef;
  font-weight: 700;
}
.btns { margin-top: 14px; display:flex; gap:10px; }
.btn {
  padding: 10px 14px;
  border: 1px solid #333;
  background: #fff;
  cursor: pointer;
  text-decoration: none;
  color: #000;
  border-radius: 6px;
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
      <th>Q</th><th>E</th><th>T</th><th>A</th>
      <th></th>
    </tr>

    <?php foreach ($rows3 as $r): ?>

      <?php if (($r["type"] ?? "") === "section"): ?>
        <tr>
          <td colspan="8" class="ipcr-section"><?= h($r["label"]) ?></td>
        </tr>
        <?php continue; ?>
      <?php endif; ?>

      <?php if (($r["type"] ?? "") === "sub"): ?>
        <tr>
          <td colspan="8" class="ipcr-sub"><?= h($r["left"]) ?></td>
        </tr>
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

  <div class="btns">
    <a class="btn" href="ipcr_doris_page2.php">⬅ Back to Page 1</a>
    <!-- change this link if your page2 name is different -->
    <a class="btn" href="ipcr_doris_page4.php">➡ Go to Page 2</a>
  </div>

</div>
<?php
require_once __DIR__. '/../layouts/footer.php';
?>