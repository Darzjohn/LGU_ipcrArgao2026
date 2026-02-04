<!-- Employee Modal -->
<div class="modal fade" id="employeeModal" tabindex="-1" aria-labelledby="employeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <form id="employeeForm" method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="employee_id" id="employeeId">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="employeeModalLabel">Add Employee</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">

                        <!-- Left Column: Photo & Personal Info -->
                        <div class="col-lg-4 col-md-12 text-center border-end">
                            <h6>Photo</h6>
                            <img id="photoPreview" src="../assets/default_user.png" class="img-thumbnail mb-2" style="width:200px;height:200px;object-fit:cover;">
                            <input type="file" class="form-control mb-2" name="photo_file" accept="image/*" onchange="previewFile(event)">
                            
                            <video id="video" width="200" height="150" autoplay style="border:1px solid #ccc; display:block; margin:auto;"></video>
                            <canvas id="canvas" width="200" height="150" style="display:none;"></canvas>
                            <button type="button" class="btn btn-sm btn-secondary mt-1" id="captureBtn">Capture Photo</button>
                            
                            <input type="hidden" name="photo" id="photo">

                            <h6 class="mt-3">Personal Info</h6>
                            <div class="form-floating mb-2">
                                <input type="text" class="form-control" name="emp_idno" id="emp_idno" placeholder="Employee ID" required>
                                <label for="emp_idno">Employee ID</label>
                            </div>
                            <div class="form-floating mb-2">
                                <input type="text" class="form-control" name="first_name" id="first_name" placeholder="First Name" required>
                                <label for="first_name">First Name</label>
                            </div>
                            <div class="form-floating mb-2">
                                <input type="text" class="form-control" name="middle_name" id="middle_name" placeholder="Middle Name">
                                <label for="middle_name">Middle Name</label>
                            </div>
                            <div class="form-floating mb-2">
                                <input type="text" class="form-control" name="surname" id="surname" placeholder="Surname" required>
                                <label for="surname">Surname</label>
                            </div>
                            <div class="form-floating mb-2">
                                <input type="text" class="form-control" name="name_extension" id="name_extension" placeholder="Name Extension">
                                <label for="name_extension">Name Extension</label>
                            </div>
                            <div class="form-floating mb-2">
                                <input type="date" class="form-control" name="dob" id="dob" placeholder="Date of Birth">
                                <label for="dob">Date of Birth</label>
                            </div>
                            <div class="form-floating mb-2">
                                <select class="form-select" name="sex" id="sex">
                                    <option value="">Select Sex</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                                <label for="sex">Sex</label>
                            </div>
                            <div class="form-floating mb-2">
                                <select class="form-select" name="civil_status" id="civil_status">
                                    <option value="">Select Civil Status</option>
                                    <option value="Single">Single</option>
                                    <option value="Married">Married</option>
                                    <option value="Widowed">Widowed</option>
                                    <option value="Separated">Separated</option>
                                </select>
                                <label for="civil_status">Civil Status</label>
                            </div>
                            <div class="form-floating mb-2">
                                <input type="text" class="form-control" name="blood_type" id="blood_type" placeholder="Blood Type">
                                <label for="blood_type">Blood Type</label>
                            </div>
                            <div class="form-floating mb-2">
                                <input type="text" class="form-control" name="place_of_birth" id="place_of_birth" placeholder="Place of Birth">
                                <label for="place_of_birth">Place of Birth</label>
                            </div>
                            <div class="form-floating mb-2">
                                <input type="text" class="form-control" name="citizenship" id="citizenship" placeholder="Citizenship">
                                <label for="citizenship">Citizenship</label>
                            </div>
                            <div class="form-floating mb-2">
                                <input type="number" class="form-control" name="height" id="height" placeholder="Height (cm)">
                                <label for="height">Height (cm)</label>
                            </div>
                            <div class="form-floating mb-2">
                                <input type="number" class="form-control" name="weight" id="weight" placeholder="Weight (kg)">
                                <label for="weight">Weight (kg)</label>
                            </div>
                        </div>

                        <!-- Right Column: Contact, Employment, Addresses, Government IDs -->
                        <div class="col-lg-8 col-md-12">
                            <h6>Contact & Employment Info</h6>
                            <div class="row g-2 mb-2">
                                <div class="col-md-6 form-floating">
                                    <input type="date" class="form-control" name="appointment_date" id="appointment_date" placeholder="Appointment Date">
                                    <label for="appointment_date">Appointment Date</label>
                                </div>
                                <div class="col-md-6 form-floating">
                                    <input type="text" class="form-control" name="telephon_no" id="telephon_no" placeholder="Telephone No.">
                                    <label for="telephon_no">Telephone No.</label>
                                </div>
                                <div class="col-md-6 form-floating">
                                    <input type="text" class="form-control" name="mobile_no" id="mobile_no" placeholder="Mobile No.">
                                    <label for="mobile_no">Mobile No.</label>
                                </div>
                                <div class="col-md-6 form-floating">
                                    <input type="email" class="form-control" name="email" id="email" placeholder="Email">
                                    <label for="email">Email</label>
                                </div>
                                <div class="col-md-6 form-floating">
                                    <input type="email" class="form-control" name="email_address" id="email_address" placeholder="Alternate Email">
                                    <label for="email_address">Alternate Email</label>
                                </div>
                                <div class="col-md-6 form-floating">
                                    <select class="form-select" name="department_id" id="department_id">
                                        <option value="">Select Department</option>
                                        <?php
                                        $departments = $mysqli->query("SELECT id,name FROM departments ORDER BY name ASC");
                                        while($d=$departments->fetch_assoc()){
                                            echo "<option value='{$d['id']}'>{$d['name']}</option>";
                                        }
                                        ?>
                                    </select>
                                    <label for="department_id">Department</label>
                                </div>
                                <div class="col-md-6 form-floating">
                                    <select class="form-select" name="position_id" id="position_id">
                                        <option value="">Select Position</option>
                                        <?php
                                        $positions = $mysqli->query("SELECT id,name FROM positions ORDER BY name ASC");
                                        while($p=$positions->fetch_assoc()){
                                            echo "<option value='{$p['id']}'>{$p['name']}</option>";
                                        }
                                        ?>
                                    </select>
                                    <label for="position_id">Position</label>
                                </div>
                                <div class="col-md-6 form-floating">
                                    <select class="form-select" name="employment_status_id" id="employment_status_id">
                                        <option value="">Select Employment Status</option>
                                        <?php
                                        $employment_status_list = $mysqli->query("SELECT id,name FROM employment_status ORDER BY name ASC");
                                        while($es = $employment_status_list->fetch_assoc()){
                                            echo "<option value='{$es['id']}'>{$es['name']}</option>";
                                        }
                                        ?>
                                    </select>
                                    <label for="employment_status_id">Employment Status</label>
                                </div>
                                <div class="col-md-6 form-floating">
                                    <input type="text" class="form-control" name="emergency_contact_person" id="emergency_contact_person" placeholder="Emergency Contact Person">
                                    <label for="emergency_contact_person">Emergency Contact Person</label>
                                </div>
                                <div class="col-md-6 form-floating">
                                    <input type="text" class="form-control" name="emergency_contact_no" id="emergency_contact_no" placeholder="Emergency Contact No.">
                                    <label for="emergency_contact_no">Emergency Contact No.</label>
                                </div>
                                <div class="col-md-6 form-floating">
                                    <input type="text" class="form-control" name="Agency_EmployeeNo" id="Agency_EmployeeNo" placeholder="Agency Employee No.">
                                    <label for="Agency_EmployeeNo">Agency Employee No.</label>
                                </div>
                            </div>

                            <h6>Government IDs</h6>
                            <div class="row g-2 mb-2">
                                <div class="col-md-6 form-floating">
                                    <input type="text" class="form-control" name="sss_no" id="sss_no" placeholder="SSS">
                                    <label for="sss_no">SSS</label>
                                </div>
                                <div class="col-md-6 form-floating">
                                    <input type="text" class="form-control" name="gsis_no" id="gsis_no" placeholder="GSIS">
                                    <label for="gsis_no">GSIS</label>
                                </div>
                                <div class="col-md-6 form-floating">
                                    <input type="text" class="form-control" name="tin_no" id="tin_no" placeholder="TIN">
                                    <label for="tin_no">TIN</label>
                                </div>
                                <div class="col-md-6 form-floating">
                                    <input type="text" class="form-control" name="pagibig_no" id="pagibig_no" placeholder="Pag-IBIG">
                                    <label for="pagibig_no">Pag-IBIG</label>
                                </div>
                                <div class="col-md-6 form-floating">
                                    <input type="text" class="form-control" name="phic_no" id="phic_no" placeholder="PHIC">
                                    <label for="phic_no">PHIC</label>
                                </div>
                                <div class="col-md-6 form-floating">
                                    <input type="text" class="form-control" name="UMID_IdNo" id="UMID_IdNo" placeholder="UMID">
                                    <label for="UMID_IdNo">UMID</label>
                                </div>
                                <div class="col-md-6 form-floating">
                                    <input type="text" class="form-control" name="PhilSys_IdNo" id="PhilSys_IdNo" placeholder="PhilSys">
                                    <label for="PhilSys_IdNo">PhilSys</label>
                                </div>
                            </div>

                            <h6>Residential Address</h6>
                            <div class="row g-2 mb-2">
                                <div class="col-md-6 form-floating">
                                    <input type="text" class="form-control" name="ra_house_block_lotno" id="ra_house_block_lotno" placeholder="House / Block / Lot No.">
                                    <label for="ra_house_block_lotno">House / Block / Lot No.</label>
                                </div>
                                <div class="col-md-6 form-floating">
                                    <input type="text" class="form-control" name="ra_street" id="ra_street" placeholder="Street">
                                    <label for="ra_street">Street</label>
                                </div>
                                <div class="col-md-6 form-floating">
                                    <input type="text" class="form-control" name="ra_subdivisionvillage" id="ra_subdivisionvillage" placeholder="Subdivision / Village">
                                    <label for="ra_subdivisionvillage">Subdivision / Village</label>
                                </div>
                                <div class="col-md-6 form-floating">
                                    <input type="text" class="form-control" name="ra_barangay" id="ra_barangay" placeholder="Barangay">
                                    <label for="ra_barangay">Barangay</label>
                                </div>
                                <div class="col-md-6 form-floating">
                                    <input type="text" class="form-control" name="ra_citymunicipality" id="ra_citymunicipality" placeholder="City / Municipality">
                                    <label for="ra_citymunicipality">City / Municipality</label>
                                </div>
                                <div class="col-md-6 form-floating">
                                    <input type="text" class="form-control" name="ra_province" id="ra_province" placeholder="Province">
                                    <label for="ra_province">Province</label>
                                </div>
                            </div>

                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="sameAsResidential">
                                <label class="form-check-label" for="sameAsResidential">Permanent same as Residential</label>
                            </div>

                            <h6>Permanent Address</h6>
                            <div class="row g-2 mb-2">
                                <div class="col-md-6 form-floating">
                                    <input type="text" class="form-control" name="pa_house_block_lotno" id="pa_house_block_lotno" placeholder="House / Block / Lot No.">
                                    <label for="pa_house_block_lotno">House / Block / Lot No.</label>
                                </div>
                                <div class="col-md-6 form-floating">
                                    <input type="text" class="form-control" name="pa_street" id="pa_street" placeholder="Street">
                                    <label for="pa_street">Street</label>
                                </div>
                                <div class="col-md-6 form-floating">
                                    <input type="text" class="form-control" name="pa_subdivisionvillage" id="pa_subdivisionvillage" placeholder="Subdivision / Village">
                                    <label for="pa_subdivisionvillage">Subdivision / Village</label>
                                </div>
                                <div class="col-md-6 form-floating">
                                    <input type="text" class="form-control" name="pa_barangay" id="pa_barangay" placeholder="Barangay">
                                    <label for="pa_barangay">Barangay</label>
                                </div>
                                <div class="col-md-6 form-floating">
                                    <input type="text" class="form-control" name="pa_citymunicipality" id="pa_citymunicipality" placeholder="City / Municipality">
                                    <label for="pa_citymunicipality">City / Municipality</label>
                                </div>
                                <div class="col-md-6 form-floating">
                                    <input type="text" class="form-control" name="pa_province" id="pa_province" placeholder="Province">
                                    <label for="pa_province">Province</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Webcam capture
const video = document.getElementById('video');
const canvas = document.getElementById('canvas');
const captureBtn = document.getElementById('captureBtn');
const photoInput = document.getElementById('photo');
const photoPreview = document.getElementById('photoPreview');

navigator.mediaDevices.getUserMedia({ video: true })
    .then(stream => video.srcObject = stream)
    .catch(err => console.log('Webcam error:', err));

captureBtn.addEventListener('click', () => {
    canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
    photoInput.value = canvas.toDataURL('image/png');
    photoPreview.src = photoInput.value;
});

// File upload preview
function previewFile(event){
    const file = event.target.files[0];
    if(file){
        const reader = new FileReader();
        reader.onload = e => {
            photoPreview.src = e.target.result;
            photoInput.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}

// Copy residential to permanent
document.getElementById('sameAsResidential').addEventListener('change', function () {
    const checked = this.checked;
    ['house_block_lotno','street','subdivisionvillage','barangay','citymunicipality','province'].forEach(field=>{
        document.getElementsByName('pa_'+field)[0].value = checked ? document.getElementsByName('ra_'+field)[0].value : '';
    });
});

// Open Edit Modal
function openEditModal(employee) {
    document.getElementById('employeeModalLabel').innerText = 'Edit Employee';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('employeeId').value = employee.id;

    // Personal Info
    const fields = ['emp_idno','first_name','middle_name','surname','name_extension','dob','sex','civil_status','blood_type','place_of_birth','citizenship','height','weight'];
    fields.forEach(f => {document.getElementById(f).value = employee[f] ?? '';});

    // Contact & Employment
    const contactFields = ['telephon_no','mobile_no','email','email_address','emergency_contact_person','emergency_contact_no','Agency_EmployeeNo','appointment_date'];
    contactFields.forEach(f => {document.getElementById(f).value = employee[f] ?? '';});

    ['department_id','position_id','employment_status_id'].forEach(f=>{
        if(employee[f]) document.getElementById(f).value = employee[f];
    });

    // Government IDs
    const govFields = ['sss_no','gsis_no','tin_no','pagibig_no','phic_no','UMID_IdNo','PhilSys_IdNo'];
    govFields.forEach(f => {document.getElementById(f).value = employee[f] ?? '';});

    // Residential & Permanent Address
    const resFields = ['ra_house_block_lotno','ra_street','ra_subdivisionvillage','ra_barangay','ra_citymunicipality','ra_province'];
    const perFields = ['pa_house_block_lotno','pa_street','pa_subdivisionvillage','pa_barangay','pa_citymunicipality','pa_province'];
    resFields.forEach(f=>document.getElementById(f).value = employee[f] ?? '');
    perFields.forEach(f=>document.getElementById(f).value = employee[f] ?? '');

    // Photo
    if(employee.photo){
        photoPreview.src = '../uploads/' + employee.photo;
        photoInput.value = '';
    } else {
        photoPreview.src = '../assets/default_user.png';
        photoInput.value = '';
    }

    const modal = new bootstrap.Modal(document.getElementById('employeeModal'));
    modal.show();
}

// Reset modal to Add Employee
document.getElementById('employeeModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('employeeModalLabel').innerText = 'Add Employee';
    document.getElementById('formAction').value = 'add';
    document.getElementById('employeeForm').reset();
    photoPreview.src = '../assets/default_user.png';
});
</script>
