<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $category_id = (int)$_POST['category_id'];
    $location_address = sanitize($_POST['location_address']);
    $latitude = (float)$_POST['latitude'];
    $longitude = (float)$_POST['longitude'];
    $priority = sanitize($_POST['priority']);
    
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image_path = uploadImage($_FILES['image']);
    }
    
    $stmt = $conn->prepare("INSERT INTO reports (user_id, category_id, title, description, location_address, latitude, longitude, image_path, priority) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$user_id, $category_id, $title, $description, $location_address, $latitude, $longitude, $image_path, $priority])) {
        header('Location: dashboard.php?success=1');
    } else {
        header('Location: dashboard.php?error=1');
    }
    exit();
}
?>