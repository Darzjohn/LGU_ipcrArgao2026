<?php
require_once __DIR__ . '/../vendor/phpqrcode/qrlib.php';
require_once __DIR__ . '/../config/settings.php';

$emp_idno = $_GET['emp_idno'] ?? '';
if (!$emp_idno) exit;

$filename = __DIR__ . '/../uploads/qrcodes/' . md5($emp_idno) . '.png';

if (!file_exists($filename)) {
    QRcode::png($emp_idno, $filename, QR_ECLEVEL_L, 3);
}

// serve
header('Content-Type: image/png');
readfile($filename);
