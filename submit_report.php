<?php
session_start();
header('Content-Type: application/json');
if(!isset($_SESSION['user_id'])){ echo json_encode(['success'=>false,'message'=>'Not logged in']); exit; }
require 'db.php';

$title = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';
$latitude = $_POST['latitude'] ?? null;
$longitude = $_POST['longitude'] ?? null;

if(empty($title) || empty($description)){
    echo json_encode(['success'=>false,'message'=>'Title and description required']); exit;
}

$stmt = $conn->prepare("INSERT INTO reports (user_id, title, description, latitude, longitude) VALUES (?,?,?,?,?)");
$stmt->execute([$_SESSION['user_id'], $title, $description, $latitude, $longitude]);

echo json_encode(['success'=>true,'message'=>'Report submitted successfully!']);
