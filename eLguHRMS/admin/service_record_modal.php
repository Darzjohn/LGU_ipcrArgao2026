<!-- Service Record Modal -->
<div class="modal fade" id="serviceRecordModal" tabindex="-1" aria-labelledby="serviceRecordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form id="serviceRecordForm" method="post">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="record_id" id="recordId">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="serviceRecordModalLabel">Add Service Record</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label>Employee</label>
                            <select class="form-select" name="emp_idno" required>
                                <option value="">Select Employee</option>
                                <?php
                                $emps = $mysqli->query("SELECT emp_idno, first_name, middle_name, surname, name_extension FROM employees ORDER BY surname ASC");
                                while($e = $emps->fetch_assoc()){
                                    $fullName = trim($e['first_name'].' '.$e['middle_name'].' '.$e['surname'].' '.$e['name_extension']);
                                    echo "<option value='{$e['emp_idno']}'>{$fullName}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label>From</label>
                            <input type="date" class="form-control" name="recfrom" required>
                        </div>
                        <div class="col-md-3">
                            <label>To</label>
                            <input type="date" class="form-control" name="recto">
                        </div>

                        <div class="col-md-4">
                            <label>Position</label>
                            <select class="form-select" name="position_id">
                                <option value="">Select Position</option>
                                <?php
                                $positions = $mysqli->query("SELECT id,name FROM positions ORDER BY name ASC");
                                while($p = $positions->fetch_assoc()){
                                    echo "<option value='{$p['id']}'>{$p['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label>Status</label>
                            <select class="form-select" name="status_id">
                                <option value="">Select Status</option>
                                <?php
                                $statuses = $mysqli->query("SELECT id,name FROM employment_status ORDER BY name ASC");
                                while($s = $statuses->fetch_assoc()){
                                    echo "<option value='{$s['id']}'>{$s['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label>Assignment</label>
                            <select class="form-select" name="assignment_id">
                                <option value="">Select Assignment</option>
                                <?php
                                $departments = $mysqli->query("SELECT id,name FROM departments ORDER BY name ASC");
                                while($d = $departments->fetch_assoc()){
                                    echo "<option value='{$d['id']}'>{$d['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label>LAW/OP</label>
                            <input type="text" class="form-control" name="lawop">
                        </div>
                        <div class="col-md-6">
                            <label>Separation Cause</label>
                            <input type="text" class="form-control" name="separation_cause">
                        </div>
                        <div class="col-md-6">
                            <label>Separation Date</label>
                            <input type="date" class="form-control" name="separation_date">
                        </div>
                        <div class="col-md-6">
                            <label>Remarks</label>
                            <input type="text" class="form-control" name="remarks">
                        </div>

                        <!-- NEW SALARY FIELDS -->
                        <div class="col-md-4">
                            <label>Salary</label>
                            <input type="text" class="form-control" name="salary">
                        </div>
                        <div class="col-md-4">
                            <label>Salary Grade</label>
                            <input type="text" class="form-control" name="salary_grade">
                        </div>
                        <div class="col-md-4">
                            <label>Step Increment</label>
                            <input type="text" class="form-control" name="step_increment">
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Record</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddModal(){
    document.getElementById('serviceRecordModalLabel').innerText = 'Add Service Record';
    document.getElementById('formAction').value = 'add';
    document.getElementById('serviceRecordForm').reset();
    document.getElementById('recordId').value = '';
}

function openEditModal(record){
    if(!record) return;

    document.getElementById('serviceRecordModalLabel').innerText = 'Edit Service Record';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('recordId').value = record.id || '';

    // Map all fields correctly
    document.getElementsByName('emp_idno')[0].value        = record.emp_idno ?? '';
    document.getElementsByName('recfrom')[0].value        = record.recfrom ?? '';
    document.getElementsByName('recto')[0].value          = record.recto ?? '';
    document.getElementsByName('position_id')[0].value    = record.position ?? '';
    document.getElementsByName('status_id')[0].value      = record.status ?? '';
    document.getElementsByName('assignment_id')[0].value  = record.assignment ?? '';
    document.getElementsByName('lawop')[0].value          = record.lawop ?? '';
    document.getElementsByName('separation_cause')[0].value = record.separation_cause ?? '';
    document.getElementsByName('separation_date')[0].value  = record.separation_date ?? '';
    document.getElementsByName('remarks')[0].value        = record.remarks ?? '';
    document.getElementsByName('salary')[0].value         = record.salary ?? '';
    document.getElementsByName('salary_grade')[0].value   = record.salary_grade ?? '';
    document.getElementsByName('step_increment')[0].value = record.step_increment ?? '';

    // Open modal safely
    bootstrap.Modal.getOrCreateInstance(document.getElementById('serviceRecordModal')).show();
}

// Reset modal on close
document.getElementById('serviceRecordModal').addEventListener('hidden.bs.modal', openAddModal);
</script>
