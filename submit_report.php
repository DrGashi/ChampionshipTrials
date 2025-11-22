<?php
session_start();
header('Content-Type: application/json');
if(!isset($_SESSION['user_id'])){
    echo json_encode(['success'=>false,'message'=>'Not logged in']); 
    exit; 
}
require 'db.php';

$title = $_POST['title'] ?? '';
$type = $_POST['type'] ?? 'General';
$description = $_POST['description'] ?? '';
$latitude = $_POST['latitude'] ?? null;
$longitude = $_POST['longitude'] ?? null;
$image = $_POST['image'] ?? null;

if(empty(type)) $type = 'General';

if(empty($description)){
    echo json_encode(['success'=>false,'message'=>'Description required']); 
    exit;
}

$stmt = $conn->prepare("INSERT INTO reports (user_id, title, type, description, latitude, longitude, image) VALUES (?,?,?,?,?,?,?)");
$stmt->execute([$_SESSION['user_id'], $title, $type, $description, $latitude, $longitude, $image]);

echo json_encode(['success'=>true,'message'=>'Report submitted successfully!']);
