<?php
require_once '../../db.php';
header('Content-Type: application/json');

// --- Handle uploads ---
$uploadFields = [
    'municipal_logo' => '../../uploads/',
    'clerk_signature' => '../../uploads/signatures/',
    'accountant_signature' => '../../uploads/signatures/',
    'treasurer_signature' => '../../uploads/signatures/'
];

foreach ($uploadFields as $field => $dir) {
    if (!empty($_FILES[$field]['name'])) {
        if (!file_exists($dir)) mkdir($dir, 0777, true);
        $fileName = time() . "_" . basename($_FILES[$field]["name"]);
        $targetFile = $dir . $fileName;
        move_uploaded_file($_FILES[$field]["tmp_name"], $targetFile);
        $relativePath = str_replace('../../', '', $targetFile);

        $mysqli->query("INSERT INTO settings (setting_key, setting_value)
                        VALUES ('$field', '$relativePath')
                        ON DUPLICATE KEY UPDATE setting_value='$relativePath'");
    }
}

// --- Save text settings ---
$fields = ['municipality_name', 'province_name', 'prepared_by', 'reviewed_by', 'approved_by'];
foreach ($fields as $key) {
    if (isset($_POST[$key])) {
        $val = $mysqli->real_escape_string($_POST[$key]);
        $mysqli->query("INSERT INTO settings (setting_key, setting_value)
                        VALUES ('$key', '$val')
                        ON DUPLICATE KEY UPDATE setting_value='$val'");
    }
}

echo json_encode(['success' => true, 'message' => 'LGU settings updated successfully!']);
?>
