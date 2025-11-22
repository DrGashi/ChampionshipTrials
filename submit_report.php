<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $categoryId = $_POST['category_id'] ?? '';
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $location = $_POST['location'] ?? '';
    $latitude = $_POST['latitude'] ?? null;
    $longitude = $_POST['longitude'] ?? null;
    $priority = $_POST['priority'] ?? 'medium';
    
    // Validate inputs
    if (empty($categoryId) || empty($title) || empty($description) || empty($location)) {
        $_SESSION['error'] = 'All fields are required';
        header('Location: dashboard.php');
        exit;
    }
    
    // Handle image upload
    $imageUrl = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageUrl = uploadImage($_FILES['image']);
    }
    
    try {
        $stmt = $conn->prepare("
            INSERT INTO reports (user_id, category_id, title, description, location_address, latitude, longitude, image_path, priority) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId, 
            $categoryId, 
            $title, 
            $description, 
            $location, 
            $latitude, 
            $longitude, 
            $imageUrl, 
            $priority
        ]);
        
        $newStreak = updateReportStreak($conn, $userId);
        
        $xpResult = awardXp($conn, $userId, 50, 'report_submitted', "Submitted report: $title");
        
        $hour = (int)date('H');
        if ($hour >= 22 || $hour < 5) {
            // Night Owl badge (10 PM - 5 AM)
            $stmt = $conn->prepare("
                INSERT IGNORE INTO user_badges (user_id, badge_id) 
                SELECT ?, id FROM badges WHERE badge_type = 'time_night'
            ");
            $stmt->execute([$userId]);
        } elseif ($hour >= 5 && $hour < 7) {
            // Early Bird badge (5 AM - 7 AM)
            $stmt = $conn->prepare("
                INSERT IGNORE INTO user_badges (user_id, badge_id) 
                SELECT ?, id FROM badges WHERE badge_type = 'time_morning'
            ");
            $stmt->execute([$userId]);
        }
        
        $newBadges = checkAndAwardBadges($conn, $userId);
        
        $successMessage = 'Report submitted successfully! ';
        $successMessage .= "You earned 50 XP! ";
        
        if ($xpResult['leveled_up']) {
            $rank = getRankTitle($xpResult['new_level']);
            $successMessage .= "🎉 Level up! You are now Level {$xpResult['new_level']} - {$rank}! ";
        }
        
        if ($newStreak > 1) {
            $successMessage .= "Streak: {$newStreak} days! ";
        }
        
        if (!empty($newBadges)) {
            $badgeNames = array_map(function($b) { return $b['icon'] . ' ' . $b['name']; }, $newBadges);
            $successMessage .= "New badge" . (count($newBadges) > 1 ? 's' : '') . ": " . implode(', ', $badgeNames) . "! ";
        }
        
        $_SESSION['success'] = $successMessage;
        header('Location: dashboard.php');
        exit;
        
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error submitting report: ' . $e->getMessage();
        header('Location: dashboard.php');
        exit;
    }
}
?>
