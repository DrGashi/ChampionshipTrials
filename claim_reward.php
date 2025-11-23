<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: rewards.php');
    exit;
}

$userId = $_SESSION['user_id'];
$rewardId = (int)$_POST['reward_id'];

// Verify the reward exists and user hasn't already claimed it
$stmt = $conn->prepare("
    SELECT cr.*, 
           (SELECT level FROM users WHERE id = ?) as user_level,
           (SELECT COUNT(*) FROM user_claimed_rewards WHERE user_id = ? AND reward_id = ?) as already_claimed
    FROM clothing_rewards cr
    WHERE cr.id = ?
");
$stmt->execute([$userId, $userId, $rewardId, $rewardId]);
$reward = $stmt->fetch();

if (!$reward) {
    $_SESSION['error'] = "Reward not found.";
    header('Location: rewards.php');
    exit;
}

if ($reward['already_claimed'] > 0) {
    $_SESSION['error'] = "You have already claimed this reward.";
    header('Location: rewards.php');
    exit;
}

if ($reward['user_level'] < $reward['required_level']) {
    $_SESSION['error'] = "You need to reach Level {$reward['required_level']} to claim this reward.";
    header('Location: rewards.php');
    exit;
}

// Claim the reward
try {
    $stmt = $conn->prepare("INSERT INTO user_claimed_rewards (user_id, reward_id) VALUES (?, ?)");
    $stmt->execute([$userId, $rewardId]);
    
    $_SESSION['success'] = "Congratulations! You claimed the {$reward['icon']} {$reward['name']}!";
} catch (Exception $e) {
    $_SESSION['error'] = "Failed to claim reward. Please try again.";
}

header('Location: rewards.php');
exit;
