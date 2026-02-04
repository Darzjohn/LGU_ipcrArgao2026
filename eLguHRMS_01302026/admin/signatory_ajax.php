<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

$response = ['success'=>false,'message'=>'','data'=>null];

// Fetch
if(isset($_GET['id'])){
    $id=(int)$_GET['id'];
    $stmt=$mysqli->prepare("SELECT * FROM signatories WHERE id=?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    $res=$stmt->get_result();
    $response['success'] = $res && $res->num_rows>0;
    $response['data'] = $res->fetch_assoc() ?? null;
    $response['message'] = $response['success']?'':'Signatory not found.';
    echo json_encode($response); exit;
}

// Add/Edit
if($_SERVER['REQUEST_METHOD']==='POST' && (isset($_POST['position'],$_POST['name'],$_POST['title']) || isset($_POST['delete_id']))){
    if(isset($_POST['delete_id'])){
        $id=(int)$_POST['delete_id'];
        $stmt=$mysqli->prepare("DELETE FROM signatories WHERE id=?");
        $stmt->bind_param("i",$id);
        $stmt->execute();
        $response['success'] = $stmt->affected_rows>0;
        $response['message'] = $response['success']?"Signatory deleted successfully.":"Failed to delete.";
        $stmt->close();
    } else {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $position=trim($_POST['position']);
        $name=trim($_POST['name']);
        $title=trim($_POST['title']);
        if($id>0){
            $stmt=$mysqli->prepare("UPDATE signatories SET position=?,name=?,title=? WHERE id=?");
            $stmt->bind_param("sssi",$position,$name,$title,$id);
            $stmt->execute();
            $response['success']=$stmt->affected_rows>=0;
            $response['message']=$response['success']?"Signatory updated successfully.":"No changes made.";
            $stmt->close();
        } else {
            $stmt=$mysqli->prepare("INSERT INTO signatories (position,name,title) VALUES (?,?,?)");
            $stmt->bind_param("sss",$position,$name,$title);
            $stmt->execute();
            $response['success']=$stmt->affected_rows>0;
            $response['message']=$response['success']?"Signatory added successfully.":"Failed to add.";
            $stmt->close();
        }
    }
    echo json_encode($response); exit;
}

echo json_encode(['success'=>false,'message'=>'Invalid request.']);
