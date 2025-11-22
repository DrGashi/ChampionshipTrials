<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: index.php');
        exit();
    }
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function uploadImage($file) {
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $allowed = array('jpg', 'jpeg', 'png', 'gif');
    
    if (!in_array($imageFileType, $allowed)) {
        return false;
    }
    
    $filename = uniqid() . '.' . $imageFileType;
    $target_file = $target_dir . $filename;
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $target_file;
    }
    
    return false;
}

function getStatusBadge($status) {
    $badges = [
        'pending' => '<span class="badge badge-warning">Pending</span>',
        'in_progress' => '<span class="badge badge-info">In Progress</span>',
        'resolved' => '<span class="badge badge-success">Resolved</span>',
        'rejected' => '<span class="badge badge-danger">Rejected</span>'
    ];
    return $badges[$status] ?? $status;
}

function getPriorityBadge($priority) {
    $badges = [
        'low' => '<span class="badge badge-secondary">Low</span>',
        'medium' => '<span class="badge badge-primary">Medium</span>',
        'high' => '<span class="badge badge-warning">High</span>',
        'urgent' => '<span class="badge badge-danger">Urgent</span>'
    ];
    return $badges[$priority] ?? $priority;
}
?>