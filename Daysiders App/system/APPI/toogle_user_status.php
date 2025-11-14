<?php
session_start();
header('Content-Type: application/json');

if(empty($_SESSION['username']) || $_SESSION['role'] !== 'admin'){
    echo json_encode(['success'=>false, 'message'=>'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $data['user_id'] ?? null;

if(!$user_id){
    echo json_encode(['success'=>false, 'message'=>'User ID missing']);
    exit();
}

require_once '../database/db.php';
$pdo = db_connect();

// Get current status
$stmt = $pdo->prepare("SELECT active FROM users WHERE id=?");
$stmt->execute([$user_id]);
$current = $stmt->fetchColumn();

if($current === false){
    echo json_encode(['success'=>false, 'message'=>'User not found']);
    exit();
}

// Toggle status
$newStatus = $current ? 0 : 1;
$stmt = $pdo->prepare("UPDATE users SET active=? WHERE id=?");
$stmt->execute([$newStatus, $user_id]);

echo json_encode(['success'=>true, 'active'=>$newStatus]);
