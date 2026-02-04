<?php
require_once __DIR__ . '/../config/settings.php';

header('Content-Type: application/json');

if (!isset($_POST['image'])) {
    echo json_encode(['success'=>false,'msg'=>'No image received']);
    exit;
}

$imageData = $_POST['image'];
$matches = [];
if (!preg_match('/^data:image\/png;base64,(.+)$/', $imageData, $matches)) {
    echo json_encode(['success'=>false,'msg'=>'Invalid image format']);
    exit;
}

$data = base64_decode($matches[1]);
$filename = uniqid() . '.png';
$savePath = __DIR__ . '/../uploads/employees/' . $filename;

if (file_put_contents($savePath, $data)) {
    echo json_encode(['success'=>true,'filename'=>$filename]);
} else {
    echo json_encode(['success'=>false,'msg'=>'Failed to save']);
}
