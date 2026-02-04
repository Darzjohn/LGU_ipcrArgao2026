<?php
// admin/attendance.php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';

// Serve attendance log
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'log') {

    $emp_idno = trim($_POST['emp_idno'] ?? '');
    $type = ($_POST['type'] ?? 'in') === 'out' ? 'out' : 'in';
    $method = in_array($_POST['method'] ?? 'manual', ['webcam','qr','manual']) ? $_POST['method'] : 'manual';
    $note = trim($_POST['note'] ?? null);

    // handle captured photo (base64) or uploaded filename
    $captured = null;
    if (!empty($_POST['captured_photo'])) {
        // POST contains filename returned by capture endpoint
        $captured = $_POST['captured_photo'];
    } elseif (!empty($_FILES['photo']['name'])) {
        $captured = uniqid()."_".$_FILES['photo']['name'];
        move_uploaded_file($_FILES['photo']['tmp_name'], __DIR__ . "/../uploads/attendance/" . $captured);
    }

    // try to find employee id (id pk) from emp_idno
    $employee_id = null;
    if ($emp_idno) {
        $stmt = $mysqli->prepare("SELECT id FROM employees WHERE emp_idno = ? LIMIT 1");
        $stmt->bind_param("s", $emp_idno);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($res) $employee_id = $res['id'];
    }

    $stmt = $mysqli->prepare("INSERT INTO attendance (emp_idno, employee_id, type, captured_photo, method, note) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param("sissss", $emp_idno, $employee_id, $type, $captured, $method, $note);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success'=>true]);
    exit;
}

// fetch today's logs for display
$logs = $mysqli->query("SELECT a.*, e.name FROM attendance a LEFT JOIN employees e ON a.employee_id = e.id ORDER BY a.created_at DESC LIMIT 200");
?>

<div class="container-fluid mt-4">
  <h4><i class="bi bi-clock-history"></i> Attendance</h4>

  <div class="row g-3 mt-2">
    <div class="col-md-4">
      <div class="card p-3">
        <h6>Log Attendance</h6>
        <div class="mb-2">
          <label>Employee ID / QR Data</label>
          <input id="att_emp" class="form-control" placeholder="Enter Employee ID or scan QR...">
        </div>

        <div class="mb-2">
          <label>Method</label>
          <select id="att_method" class="form-select">
            <option value="manual">Manual</option>
            <option value="qr">QR</option>
            <option value="webcam">Webcam</option>
          </select>
        </div>

        <div id="webcamArea" style="display:none">
          <video id="att_camera" autoplay style="width:100%;"></video>
          <canvas id="att_canvas" class="d-none"></canvas>
          <div class="mt-2">
            <button class="btn btn-dark btn-sm" onclick="startAttendanceCamera()">Open Camera</button>
            <button class="btn btn-primary btn-sm" onclick="captureAttendance()">Capture & Log</button>
          </div>
        </div>

        <div class="d-flex gap-2 mt-3">
          <button class="btn btn-success" onclick="sendAttendance('in')">Check In</button>
          <button class="btn btn-warning" onclick="sendAttendance('out')">Check Out</button>
        </div>
      </div>
    </div>

    <div class="col-md-8">
      <div class="card p-3">
        <h6>Recent Logs</h6>
        <div style="max-height:520px;overflow:auto">
          <table class="table table-sm">
            <thead><tr><th>Time</th><th>Employee</th><th>Emp ID</th><th>Type</th><th>Method</th><th>Photo</th></tr></thead>
            <tbody>
            <?php while($r = $logs->fetch_assoc()): ?>
              <tr>
                <td><?= $r['created_at'] ?></td>
                <td><?= htmlspecialchars($r['name'] ?: '-') ?></td>
                <td><?= htmlspecialchars($r['emp_idno']) ?></td>
                <td><?= $r['type'] ?></td>
                <td><?= $r['method'] ?></td>
                <td>
                  <?php if ($r['captured_photo']): ?>
                    <a href="../uploads/attendance/<?= htmlspecialchars($r['captured_photo']) ?>" target="_blank">View</a>
                  <?php else: echo '-'; endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>

<script>
let attStream = null;
document.getElementById('att_method').addEventListener('change', function(){
  document.getElementById('webcamArea').style.display = this.value === 'webcam' ? 'block' : 'none';
});

function startAttendanceCamera() {
  navigator.mediaDevices.getUserMedia({video:true}).then(s=>{
    attStream = s;
    document.getElementById('att_camera').srcObject = s;
  }).catch(e=>alert('Camera access denied'));
}

function captureAttendance() {
  const video = document.getElementById('att_camera');
  const canvas = document.getElementById('att_canvas');
  canvas.width = video.videoWidth; canvas.height = video.videoHeight;
  canvas.getContext('2d').drawImage(video,0,0);
  const data = canvas.toDataURL('image/png');

  // send to capture endpoint (reuse capture_photo.php or face_crop.php as you like)
  fetch('capture_photo.php', {
    method:'POST',
    body: new URLSearchParams({image: data})
  }).then(r=>r.json()).then(res=>{
    if(res.success){
      sendAttendance('in', res.filename); // default as 'in' or supply type explicitly if needed
    } else alert('Capture failed');
  });

  if (attStream) attStream.getTracks().forEach(t=>t.stop());
}

function sendAttendance(type, captured_file = null) {
  const emp = document.getElementById('att_emp').value.trim();
  const method = document.getElementById('att_method').value;
  const payload = new URLSearchParams({
    action: 'log',
    emp_idno: emp,
    type: type,
    method: method
  });
  if (captured_file) payload.append('captured_photo', captured_file);

  fetch('attendance.php', { method: 'POST', body: payload })
  .then(r=>r.json()).then(j=>{
    if (j.success) location.reload();
    else alert('Logging failed');
  });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
