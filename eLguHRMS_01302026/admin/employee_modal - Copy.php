<!-- Employee Add/Edit Modal -->
<div class="modal fade" id="employeeModal" tabindex="-1" aria-labelledby="employeeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <form method="POST" id="employeeForm">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="employeeModalLabel">Add Employee</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body row g-3">
          <input type="hidden" name="employee_id" id="employee_id">
          <input type="hidden" name="action" id="form_action" value="add">
          <input type="hidden" name="photo" id="photo">

          <!-- Employee Basic Info -->
          <div class="col-md-3">
            <label class="form-label">Employee ID</label>
            <input type="text" class="form-control" name="emp_idno" id="emp_idno" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">First Name</label>
            <input type="text" class="form-control" name="first_name" id="first_name" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Middle Name</label>
            <input type="text" class="form-control" name="middle_name" id="middle_name">
          </div>
          <div class="col-md-3">
            <label class="form-label">Surname</label>
            <input type="text" class="form-control" name="surname" id="surname" required>
          </div>
          <div class="col-md-2">
            <label class="form-label">Name Extension</label>
            <input type="text" class="form-control" name="name_extension" id="name_extension">
          </div>

          <div class="col-md-2">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" id="email">
          </div>
          <div class="col-md-2">
            <label class="form-label">Contact No.</label>
            <input type="text" class="form-control" name="contact_no" id="contact_no">
          </div>
          <div class="col-md-4">
            <label class="form-label">Address</label>
            <input type="text" class="form-control" name="address" id="address">
          </div>
          <div class="col-md-2">
            <label class="form-label">DOB</label>
            <input type="date" class="form-control" name="dob" id="dob">
          </div>
          <div class="col-md-2">
            <label class="form-label">Blood Type</label>
            <input type="text" class="form-control" name="blood_type" id="blood_type">
          </div>
          <div class="col-md-2">
            <label class="form-label">Sex</label>
            <select class="form-select" name="sex" id="sex">
              <option value="">Select</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Civil Status</label>
            <select class="form-select" name="civil_status" id="civil_status">
              <option value="">Select</option>
              <option value="Single">Single</option>
              <option value="Married">Married</option>
              <option value="Widowed">Widowed</option>
              <option value="Separated">Separated</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Civil Status Specify</label>
            <input type="text" class="form-control" name="civil_status_specify" id="civil_status_specify">
          </div>
          <div class="col-md-2">
            <label class="form-label">Height (cm)</label>
            <input type="number" step="0.01" class="form-control" name="height" id="height">
          </div>
          <div class="col-md-2">
            <label class="form-label">Weight (kg)</label>
            <input type="number" step="0.01" class="form-control" name="weight" id="weight">
          </div>

          <!-- IDs -->
          <div class="col-md-2">
            <label class="form-label">SSS No</label>
            <input type="text" class="form-control" name="sss_no" id="sss_no">
          </div>
          <div class="col-md-2">
            <label class="form-label">GSIS No</label>
            <input type="text" class="form-control" name="gsis_no" id="gsis_no">
          </div>
          <div class="col-md-2">
            <label class="form-label">TIN No</label>
            <input type="text" class="form-control" name="tin_no" id="tin_no">
          </div>
          <div class="col-md-2">
            <label class="form-label">Pag-IBIG No</label>
            <input type="text" class="form-control" name="pagibig_no" id="pagibig_no">
          </div>
          <div class="col-md-2">
            <label class="form-label">PHIC No</label>
            <input type="text" class="form-control" name="phic_no" id="phic_no">
          </div>
          <div class="col-md-2">
            <label class="form-label">UMID ID No</label>
            <input type="text" class="form-control" name="UMID_IdNo" id="UMID_IdNo">
          </div>
          <div class="col-md-2">
            <label class="form-label">PhilSys ID No</label>
            <input type="text" class="form-control" name="PhilSys_IdNo" id="PhilSys_IdNo">
          </div>
          <div class="col-md-2">
            <label class="form-label">Agency Employee No</label>
            <input type="text" class="form-control" name="Agency_EmployeeNo" id="Agency_EmployeeNo">
          </div>

          <!-- Emergency Contact -->
          <div class="col-md-3">
            <label class="form-label">Emergency Contact Person</label>
            <input type="text" class="form-control" name="emergency_contact_person" id="emergency_contact_person">
          </div>
          <div class="col-md-3">
            <label class="form-label">Emergency Contact No</label>
            <input type="text" class="form-control" name="emergency_contact_no" id="emergency_contact_no">
          </div>

          <!-- Addresses -->
          <div class="col-md-6">
            <label class="form-label">Residential Address (House/Street/Subdivision/Barangay/City/Province)</label>
            <input type="text" class="form-control mb-1" name="ra_house_block_lotno" id="ra_house_block_lotno" placeholder="House/Block/Lot">
            <input type="text" class="form-control mb-1" name="ra_street" id="ra_street" placeholder="Street">
            <input type="text" class="form-control mb-1" name="ra_subdivisionvillage" id="ra_subdivisionvillage" placeholder="Subdivision/Village">
            <input type="text" class="form-control mb-1" name="ra_barangay" id="ra_barangay" placeholder="Barangay">
            <input type="text" class="form-control mb-1" name="ra_citymunicipality" id="ra_citymunicipality" placeholder="City/Municipality">
            <input type="text" class="form-control mb-1" name="ra_province" id="ra_province" placeholder="Province">
          </div>

          <div class="col-md-6">
            <label class="form-label">Permanent Address (House/Street/Subdivision/Barangay/City/Province)</label>
            <input type="text" class="form-control mb-1" name="pa_house_block_lotno" id="pa_house_block_lotno" placeholder="House/Block/Lot">
            <input type="text" class="form-control mb-1" name="pa_street" id="pa_street" placeholder="Street">
            <input type="text" class="form-control mb-1" name="pa_subdivisionvillage" id="pa_subdivisionvillage" placeholder="Subdivision/Village">
            <input type="text" class="form-control mb-1" name="pa_barangay" id="pa_barangay" placeholder="Barangay">
            <input type="text" class="form-control mb-1" name="pa_citymunicipality" id="pa_citymunicipality" placeholder="City/Municipality">
            <input type="text" class="form-control mb-1" name="pa_province" id="pa_province" placeholder="Province">
          </div>

          <div class="col-md-2">
            <label class="form-label">Telephone No</label>
            <input type="text" class="form-control" name="telephon_no" id="telephon_no">
          </div>
          <div class="col-md-2">
            <label class="form-label">Mobile No</label>
            <input type="text" class="form-control" name="mobile_no" id="mobile_no">
          </div>
          <div class="col-md-2">
            <label class="form-label">Email Address</label>
            <input type="email" class="form-control" name="email_address" id="email_address">
          </div>

          <!-- Department/Position -->
          <div class="col-md-3">
            <label class="form-label">Department</label>
            <select class="form-select" name="department_id" id="department_id">
              <option value="">Select Department</option>
              <?php
              $departments = $mysqli->query("SELECT id,name FROM departments ORDER BY name");
              while($d=$departments->fetch_assoc()){
                  echo "<option value='{$d['id']}'>{$d['name']}</option>";
              }
              ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Position</label>
            <select class="form-select" name="position_id" id="position_id">
              <option value="">Select Position</option>
              <?php
              $positions = $mysqli->query("SELECT id,name FROM positions ORDER BY name");
              while($p=$positions->fetch_assoc()){
                  echo "<option value='{$p['id']}'>{$p['name']}</option>";
              }
              ?>
            </select>
          </div>

          <!-- Photo Capture -->
          <div class="col-md-6">
            <label class="form-label">Photo</label>
            <div class="mb-2">
              <video id="video" width="200" height="150" autoplay muted></video>
              <canvas id="canvas" style="display:none;"></canvas>
            </div>
            <button type="button" class="btn btn-sm btn-secondary" onclick="capturePhoto()">Capture Photo</button>
            <img id="photo_preview" src="../assets/default_user.png" class="img-thumbnail mt-2" width="150">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Save Employee</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Webcam capture
const video = document.getElementById('video');
navigator.mediaDevices.getUserMedia({ video: true })
.then(stream => video.srcObject = stream)
.catch(console.error);

function capturePhoto(){
    const canvas = document.getElementById('canvas');
    canvas.width = 200; canvas.height = 150;
    canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
    const dataURL = canvas.toDataURL('image/png');
    document.getElementById('photo_preview').src = dataURL;
    document.getElementById('photo').value = dataURL;
}

// Modal open functions
function openAddModal(){
    document.getElementById('employeeModalLabel').textContent='Add Employee';
    document.getElementById('form_action').value='add';
    document.getElementById('employeeForm').reset();
    document.getElementById('photo_preview').src='../assets/default_user.png';
}

function openEditModal(emp){
    document.getElementById('employeeModalLabel').textContent='Edit Employee';
    document.getElementById('form_action').value='edit';
    document.getElementById('employee_id').value = emp.id;

    for(const key in emp){
        if(document.getElementById(key)){
            document.getElementById(key).value = emp[key];
        }
    }

    if(emp.photo){
        document.getElementById('photo_preview').src='../uploads/'+emp.photo;
    } else {
        document.getElementById('photo_preview').src='../assets/default_user.png';
    }
}
</script>
