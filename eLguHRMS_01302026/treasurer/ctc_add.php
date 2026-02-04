<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';

if($_SERVER['REQUEST_METHOD']==='POST'){
    $stmt = $mysqli->prepare("
        INSERT INTO ctc_individual 
        (year,place_of_issue,date_issued,ctc_no,surname,firstname,middlename,address,citizenship,icr_no,place_of_birth,civil_status,profession,gross_receipts,salaries,real_property_income,basic_tax,additional_tax,total_due,treasurer,date_of_payment,created_by)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
    ");
    $stmt->bind_param(
        "issssssssssssddddddsss",
        $_POST['year'], $_POST['place_of_issue'], $_POST['date_issued'], $_POST['ctc_no'],
        $_POST['surname'], $_POST['firstname'], $_POST['middlename'], $_POST['address'],
        $_POST['citizenship'], $_POST['icr_no'], $_POST['place_of_birth'], $_POST['civil_status'],
        $_POST['profession'], $_POST['gross_receipts'], $_POST['salaries'], $_POST['real_property_income'],
        $_POST['basic_tax'], $_POST['additional_tax'], $_POST['total_due'], $_POST['treasurer'],
        $_POST['date_of_payment'], $_SESSION['username']
    );
    $stmt->execute();
    header("Location: ctc_list.php");
    exit;
}
?>
<div class="container-fluid mt-4">
  <h4>➕ New Community Tax Certificate</h4>
  <form method="post" class="card p-3">
    <div class="row g-3">
      <div class="col-md-2"><label>Year</label><input type="number" name="year" class="form-control" required></div>
      <div class="col-md-4"><label>Place of Issue</label><input type="text" name="place_of_issue" class="form-control"></div>
      <div class="col-md-3"><label>Date Issued</label><input type="date" name="date_issued" class="form-control"></div>
      <div class="col-md-3"><label>CTC No.</label><input type="text" name="ctc_no" class="form-control" required></div>

      <div class="col-md-4"><label>Surname</label><input type="text" name="surname" class="form-control"></div>
      <div class="col-md-4"><label>First Name</label><input type="text" name="firstname" class="form-control"></div>
      <div class="col-md-4"><label>Middle Name</label><input type="text" name="middlename" class="form-control"></div>
      <div class="col-md-12"><label>Address</label><input type="text" name="address" class="form-control"></div>

      <div class="col-md-4"><label>Citizenship</label><input type="text" name="citizenship" class="form-control"></div>
      <div class="col-md-4"><label>ICR No.</label><input type="text" name="icr_no" class="form-control"></div>
      <div class="col-md-4"><label>Place of Birth</label><input type="text" name="place_of_birth" class="form-control"></div>

      <div class="col-md-4">
        <label>Civil Status</label>
        <select name="civil_status" class="form-select">
          <option>Single</option><option>Married</option><option>Widow/Widower</option><option>Divorced</option>
        </select>
      </div>
      <div class="col-md-8"><label>Profession / Occupation / Business</label><input type="text" name="profession" class="form-control"></div>

      <div class="col-md-4"><label>Gross Receipts</label><input type="number" step="0.01" name="gross_receipts" class="form-control"></div>
      <div class="col-md-4"><label>Salaries</label><input type="number" step="0.01" name="salaries" class="form-control"></div>
      <div class="col-md-4"><label>Real Property Income</label><input type="number" step="0.01" name="real_property_income" class="form-control"></div>

      <div class="col-md-4"><label>Basic Tax</label><input type="number" step="0.01" name="basic_tax" value="5.00" class="form-control"></div>
      <div class="col-md-4"><label>Additional Tax</label><input type="number" step="0.01" name="additional_tax" class="form-control"></div>
      <div class="col-md-4"><label>Total Due</label><input type="number" step="0.01" name="total_due" class="form-control"></div>

      <div class="col-md-6"><label>Treasurer</label><input type="text" name="treasurer" class="form-control"></div>
      <div class="col-md-6"><label>Date of Payment</label><input type="date" name="date_of_payment" class="form-control"></div>
    </div>
    <div class="mt-3 text-end">
      <button type="submit" class="btn btn-success">💾 Save</button>
      <a href="ctc_list.php" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
