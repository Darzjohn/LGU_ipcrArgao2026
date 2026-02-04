<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['photo'])) {
    $data = $_POST['photo'];
    $emp_id = $_POST['emp_idno'] ?? 'unknown';

    // Extract base64 data
    if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
        $data = substr($data, strpos($data, ',') + 1);
        $type = strtolower($type[1]); // jpg, png, etc.
        $data = base64_decode($data);

        $filename = "../uploads/employees/{$emp_id}_" . time() . ".$type";
        file_put_contents($filename, $data);

        // Optionally, update DB
        $stmt = $mysqli->prepare("UPDATE employees SET photo=? WHERE emp_idno=?");
        $stmt->bind_param("ss", $filename, $emp_id);
        $stmt->execute();
        $stmt->close();

        echo json_encode(['success'=>true, 'file'=>$filename]);
        exit;
    }
}
echo json_encode(['success'=>false]);
