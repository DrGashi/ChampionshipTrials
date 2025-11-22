<?php

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

function clean($data) {
    return sanitize($data);
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
        'pending' => '<span class="badge bg-warning">Pending</span>',
        'in_progress' => '<span class="badge bg-info">In Progress</span>',
        'resolved' => '<span class="badge bg-success">Resolved</span>',
        'rejected' => '<span class="badge bg-danger">Rejected</span>'
    ];
    return $badges[$status] ?? '<span class="badge bg-secondary">' . ucfirst($status) . '</span>';
}

// Get status class for Bootstrap
function getStatusClass($status) {
    $classes = [
        'pending' => 'warning',
        'in_progress' => 'info',
        'resolved' => 'success',
        'rejected' => 'danger'
    ];
    return $classes[$status] ?? 'secondary';
}

// Get priority badge HTML
function getPriorityBadge($priority) {
    $badges = [
        'low' => '<span class="badge bg-secondary">Low</span>',
        'medium' => '<span class="badge bg-primary">Medium</span>',
        'high' => '<span class="badge bg-warning">High</span>',
        'urgent' => '<span class="badge bg-danger">Urgent</span>'
    ];
    return $badges[$priority] ?? '<span class="badge bg-secondary">' . ucfirst($priority) . '</span>';
}

// Get priority class for Bootstrap
function getPriorityClass($priority) {
    $classes = [
        'low' => 'secondary',
        'medium' => 'primary',
        'high' => 'warning',
        'urgent' => 'danger'
    ];
    return $classes[$priority] ?? 'secondary';
}

// Format date nicely
function formatDate($date) {
    return date('M d, Y g:i A', strtotime($date));
}

// Get all categories from database
function getCategories($conn) {
    $stmt = $conn->query("SELECT * FROM categories ORDER BY name ASC");
    return $stmt->fetchAll();
}

// Get report count by status
function getReportCountByStatus($conn, $status) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM reports WHERE status = ?");
    $stmt->execute([$status]);
    return $stmt->fetchColumn();
}

// Redirect helper
function redirect($url) {
    header("Location: $url");
    exit();
}

// XP Configuration - Each level requires progressively more XP
function getXpForLevel($level) {
    // Level 1: 100 XP, Level 2: 250 XP, Level 3: 450 XP, etc.
    // Formula: 100 * level + 50 * (level - 1) * level
    return 100 * $level + 50 * ($level - 1) * $level;
}

// Get rank title based on level
function getRankTitle($level) {
    if ($level >= 50) return 'Legendary Guardian';
    if ($level >= 40) return 'Master Protector';
    if ($level >= 30) return 'Elite Watchman';
    if ($level >= 25) return 'Senior Sentinel';
    if ($level >= 20) return 'Vigilant Defender';
    if ($level >= 15) return 'Trusted Guardian';
    if ($level >= 10) return 'Active Citizen';
    if ($level >= 7) return 'Engaged Reporter';
    if ($level >= 5) return 'Contributing Member';
    if ($level >= 3) return 'Rising Advocate';
    if ($level >= 2) return 'Community Helper';
    return 'New Reporter';
}

// Calculate level from XP
function calculateLevel($xp) {
    $level = 1;
    while ($xp >= getXpForLevel($level)) {
        $xp -= getXpForLevel($level);
        $level++;
    }
    return $level;
}

// Get progress to next level (returns percentage 0-100)
function getLevelProgress($xp, $level) {
    $xpForCurrentLevel = getXpForLevel($level);
    
    // Calculate XP accumulated for current level
    $totalXpForPreviousLevels = 0;
    for ($i = 1; $i < $level; $i++) {
        $totalXpForPreviousLevels += getXpForLevel($i);
    }
    
    $xpIntoCurrentLevel = $xp - $totalXpForPreviousLevels;
    $progress = ($xpIntoCurrentLevel / $xpForCurrentLevel) * 100;
    
    return max(0, min(100, $progress)); // Clamp between 0-100
}

// Get XP remaining for next level
function getXpToNextLevel($xp, $level) {
    $xpForCurrentLevel = getXpForLevel($level);
    
    $totalXpForPreviousLevels = 0;
    for ($i = 1; $i < $level; $i++) {
        $totalXpForPreviousLevels += getXpForLevel($i);
    }
    
    $xpIntoCurrentLevel = $xp - $totalXpForPreviousLevels;
    return $xpForCurrentLevel - $xpIntoCurrentLevel;
}

// Award XP to user and update level
function awardXp($conn, $userId, $xpAmount, $action, $description = '') {
    // Get current user XP and level
    $stmt = $conn->prepare("SELECT xp, level FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    $newXp = max(0, $user['xp'] + $xpAmount); // XP can't go below 0
    $newLevel = calculateLevel($newXp);
    $leveledUp = $newLevel > $user['level'];
    
    // Update user XP and level
    $stmt = $conn->prepare("UPDATE users SET xp = ?, level = ? WHERE id = ?");
    $stmt->execute([$newXp, $newLevel, $userId]);
    
    // Log activity
    $stmt = $conn->prepare("INSERT INTO activity_log (user_id, action, description, xp_earned) VALUES (?, ?, ?, ?)");
    $stmt->execute([$userId, $action, $description, $xpAmount]);
    
    return [
        'leveled_up' => $leveledUp,
        'new_level' => $newLevel,
        'new_xp' => $newXp,
        'xp_gained' => $xpAmount
    ];
}

// Check and award badges to user
function checkAndAwardBadges($conn, $userId) {
    $newBadges = [];
    
    // Get user stats
    $stmt = $conn->prepare("SELECT level, report_streak FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    // Get total report count
    $stmt = $conn->prepare("SELECT COUNT(*) FROM reports WHERE user_id = ?");
    $stmt->execute([$userId]);
    $totalReports = $stmt->fetchColumn();
    
    // Get category counts
    $stmt = $conn->prepare("
        SELECT c.name, COUNT(*) as count 
        FROM reports r 
        JOIN categories c ON r.category_id = c.id 
        WHERE r.user_id = ? 
        GROUP BY c.id
    ");
    $stmt->execute([$userId]);
    $categoryCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Get urgent priority count
    $stmt = $conn->prepare("SELECT COUNT(*) FROM reports WHERE user_id = ? AND priority = 'urgent'");
    $stmt->execute([$userId]);
    $urgentCount = $stmt->fetchColumn();
    
    // Get all badges
    $allBadges = $conn->query("SELECT * FROM badges")->fetchAll();
    
    // Check each badge
    foreach ($allBadges as $badge) {
        // Check if user already has this badge
        $stmt = $conn->prepare("SELECT id FROM user_badges WHERE user_id = ? AND badge_id = ?");
        $stmt->execute([$userId, $badge['id']]);
        if ($stmt->fetch()) {
            continue; // Already has badge
        }
        
        $earned = false;
        
        switch ($badge['badge_type']) {
            case 'report_count':
                if ($totalReports >= $badge['requirement_value']) {
                    $earned = true;
                }
                break;
                
            case 'streak':
                if ($user['report_streak'] >= $badge['requirement_value']) {
                    $earned = true;
                }
                break;
                
            case 'category_pothole':
                if (($categoryCounts['Pothole'] ?? 0) >= $badge['requirement_value']) {
                    $earned = true;
                }
                break;
                
            case 'category_streetlight':
                if (($categoryCounts['Street Light'] ?? 0) >= $badge['requirement_value']) {
                    $earned = true;
                }
                break;
                
            case 'category_trash':
                if (($categoryCounts['Trash Overflow'] ?? 0) >= $badge['requirement_value']) {
                    $earned = true;
                }
                break;
                
            case 'time_night':
            case 'time_morning':
                // These are checked in submit_report.php based on time
                break;
                
            case 'priority_urgent':
                if ($urgentCount >= $badge['requirement_value']) {
                    $earned = true;
                }
                break;
                
            case 'level':
                if ($user['level'] >= $badge['requirement_value']) {
                    $earned = true;
                }
                break;
        }
        
        // Award badge if earned
        if ($earned) {
            $stmt = $conn->prepare("INSERT INTO user_badges (user_id, badge_id) VALUES (?, ?)");
            $stmt->execute([$userId, $badge['id']]);
            $newBadges[] = $badge;
        }
    }
    
    return $newBadges;
}

// Get user's badges
function getUserBadges($conn, $userId) {
    $stmt = $conn->prepare("
        SELECT b.*, ub.earned_at 
        FROM badges b 
        JOIN user_badges ub ON b.id = ub.badge_id 
        WHERE ub.user_id = ? 
        ORDER BY ub.earned_at DESC
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

// Update report streak
function updateReportStreak($conn, $userId) {
    $stmt = $conn->prepare("SELECT last_report_date, report_streak FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    $today = date('Y-m-d');
    $lastReportDate = $user['last_report_date'];
    $currentStreak = $user['report_streak'];
    
    if ($lastReportDate === null) {
        // First report ever
        $newStreak = 1;
    } elseif ($lastReportDate === $today) {
        // Already reported today, no change
        return $currentStreak;
    } else {
        $lastDate = new DateTime($lastReportDate);
        $todayDate = new DateTime($today);
        $diff = $lastDate->diff($todayDate)->days;
        
        if ($diff === 1) {
            // Consecutive day, increment streak
            $newStreak = $currentStreak + 1;
        } else {
            // Streak broken, reset to 1
            $newStreak = 1;
        }
    }
    
    $stmt = $conn->prepare("UPDATE users SET report_streak = ?, last_report_date = ? WHERE id = ?");
    $stmt->execute([$newStreak, $today, $userId]);
    
    return $newStreak;
}

?>