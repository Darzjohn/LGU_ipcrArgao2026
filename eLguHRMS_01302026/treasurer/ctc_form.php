<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';

$id = $_GET['id'] ?? null;
$data = [];
if($id){
  $res = $mysqli->query("SELECT * FROM ctc_individual WHERE id=$id");
  $data = $res->fetch_assoc();
}

if($_SERVER['REQUEST_METHOD']==='POST'){
  $fields = [
    'ctc_no','year','place_of_issue','date_issued','surname','firstname','middlename',
    'address','citizenship','icr_no','place_of_birth','civil_status','profession',
    'gross_receipts','salaries','real_property_income','total_due','interest','total_paid',
    'amount_words','dop','treasurer'
  ];
  $vals = [];
  foreach($fields as $f) $vals[$f] = $mysqli->real_escape_string($_POST[$f]);

  if($id){
    $sql = "UPDATE ctc_individual SET ";
    foreach($fields as $f) $sql .= "$f='{$vals[$f]}',";
    $sql = rtrim($sql,',')." WHERE id=$id";
    $mysqli->query($sql);
    echo "<script>alert('Updated successfully!');location='ctc_list.php';</script>";
  }else{
    $columns = implode(',', $fields);
    $values = "'" . implode("','", $vals) . "'";
    $mysqli->query("INSERT INTO ctc_individual ($columns, created_by) VALUES ($values, '{$_SESSION['username']}')");
    echo "<script>alert('CTC Added!');location='ctc_list.php';</script>";
  }
}
?>

<div class="container mt-4">
  <h4><?= $id ? 'Edit' : 'Create' ?> Community Tax Certificate</h4>
  <form method="post" class="card card-body">
    <div class="row g-2">
      <div class="col-md-3"><label>CTC No</label><input name="ctc_no" class="form-control" value="<?= $data['ctc_no'] ?? '' ?>" required></div>
      <div class="col-md-2"><label>Year</label><input name="year" class="form-control" value="<?= $data['year'] ?? date('Y') ?>"></div>
      <div class="col-md-4"><label>Place of Issue</label><input name="place_of_issue" class="form-control" value="<?= $data['place_of_issue'] ?? '' ?>"></div>
      <div class="col-md-3"><label>Date Issued</label><input type="date" name="date_issued" class="form-control" value="<?= $data['date_issued'] ?? date('Y-m-d') ?>"></div>
      <div class="col-md-4"><label>Surname</label><input name="surname" class="form-control" value="<?= $data['surname'] ?? '' ?>"></div>
      <div class="col-md-4"><label>Firstname</label><input name="firstname" class="form-control" value="<?= $data['firstname'] ?? '' ?>"></div>
      <div class="col-md-4"><label>Middlename</label><input name="middlename" class="form-control" value="<?= $data['middlename'] ?? '' ?>"></div>
      <div class="col-md-12"><label>Address</label><input name="address" class="form-control" value="<?= $data['address'] ?? '' ?>"></div>
      <div class="col-md-4"><label>Citizenship</label><input name="citizenship" class="form-control" value="<?= $data['citizenship'] ?? '' ?>"></div>
      <div class="col-md-4"><label>ICR No</label><input name="icr_no" class="form-control" value="<?= $data['icr_no'] ?? '' ?>"></div>
      <div class="col-md-4"><label>Place of Birth</label><input name="place_of_birth" class="form-control" value="<?= $data['place_of_birth'] ?? '' ?>"></div>
      <div class="col-md-4"><label>Civil Status</label>
        <select name="civil_status" class="form-select">
          <?php $status=['Single','Married','Widow/Widower','Divorced'];
          foreach($status as $s): ?>
          <option <?=$data['civil_status']==$s?'selected':''?>><?=$s?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4"><label>Profession</label><input name="profession" class="form-control" value="<?= $data['profession'] ?? '' ?>"></div>
      <div class="col-md-4"><label>Gross Receipts</label><input type="number" step="0.01" name="gross_receipts" class="form-control" value="<?= $data['gross_receipts'] ?? 0 ?>"></div>
      <div class="col-md-4"><label>Salaries</label><input type="number" step="0.01" name="salaries" class="form-control" value="<?= $data['salaries'] ?? 0 ?>"></div>
      <div class="col-md-4"><label>Real Property Income</label><input type="number" step="0.01" name="real_property_income" class="form-control" value="<?= $data['real_property_income'] ?? 0 ?>"></div>
      <div class="col-md-4"><label>Total Due</label><input type="number" step="0.01" name="total_due" class="form-control" value="<?= $data['total_due'] ?? 0 ?>"></div>
      <div class="col-md-4"><label>Interest</label><input type="number" step="0.01" name="interest" class="form-control" value="<?= $data['interest'] ?? 0 ?>"></div>
      <div class="col-md-4"><label>Total Paid</label><input type="number" step="0.01" name="total_paid" class="form-control" value="<?= $data['total_paid'] ?? 0 ?>"></div>
      <div class="col-md-6"><label>Amount in Words</label><input name="amount_words" class="form-control" value="<?= $data['amount_words'] ?? '' ?>"></div>
      <div class="col-md-3"><label>Date of Payment</label><input type="date" name="dop" class="form-control" value="<?= $data['dop'] ?? '' ?>"></div>
      <div class="col-md-3"><label>Treasurer</label><input name="treasurer" class="form-control" value="<?= $data['treasurer'] ?? '' ?>"></div>
    </div>
    <button class="btn btn-primary mt-3">💾 Save</button>
    <a href="ctc_list.php" class="btn btn-secondary mt-3">Cancel</a>
  </form>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
