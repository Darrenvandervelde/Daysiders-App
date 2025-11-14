<?php
session_start();
header('Content-Type: application/json');

require '../database/db.php';

$pdo = db_connect(); // should return PDO

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if(!$username || !$password){
    echo json_encode(['success'=>false,'message'=>'Enter username and password']);
    exit;
}

$stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = :username");
$stmt->execute(['username' => $username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$user){
    echo json_encode(['success'=>false,'message'=>'Invalid username or password']);
    exit;
}

if(password_verify($password, $user['password'])){
    $_SESSION['username'] = $user['username'];
    $_SESSION['user_id'] = $user['id'];
    echo json_encode(['success'=>true,'message'=>'Login successful']);
}else{
    echo json_encode(['success'=>false,'message'=>'Invalid username or password']);
}
?>
