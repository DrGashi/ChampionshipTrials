<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

requireLogin();

try {
    // Get filter parameters
    $status = $_GET['status'] ?? '';
    $category = $_GET['category_id'] ?? '';
    $priority = $_GET['priority'] ?? '';
    
    // Build query
    $sql = "SELECT r.*, c.name as category_name, u.full_name, u.username 
            FROM reports r 
            LEFT JOIN categories c ON r.category_id = c.id 
            LEFT JOIN users u ON r.user_id = u.id 
            WHERE 1=1";
    
    $params = [];
    
    if (!empty($status)) {
        $sql .= " AND r.status = ?";
        $params[] = $status;
    }
    
    if (!empty($category)) {
        $sql .= " AND r.category_id = ?";
        $params[] = $category;
    }
    
    if (!empty($priority)) {
        $sql .= " AND r.priority = ?";
        $params[] = $priority;
    }
    
    // Non-admin users can only see their own reports
    if (!isAdmin()) {
        $sql .= " AND r.user_id = ?";
        $params[] = $_SESSION['user_id'];
    }
    
    $sql .= " ORDER BY r.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $reports = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'reports' => $reports
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch reports'
    ]);
}
