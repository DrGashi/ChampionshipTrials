<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

$userId = $_SESSION['user_id'];

// Handle reward claim
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['claim_reward'])) {
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
    
    if ($reward && $reward['already_claimed'] == 0 && $reward['user_level'] >= $reward['level_required']) {
        // Claim the reward
        $stmt = $conn->prepare("INSERT INTO user_claimed_rewards (user_id, reward_id) VALUES (?, ?)");
        if ($stmt->execute([$userId, $rewardId])) {
            $_SESSION['success'] = "🎉 You claimed the {$reward['icon']} {$reward['name']}!";
        } else {
            $_SESSION['error'] = "Failed to claim reward. Please try again.";
        }
    } else {
        $_SESSION['error'] = "Unable to claim this reward.";
    }
    
    header('Location: rewards.php');
    exit;
}

// Get user info
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

$currentLevel = $user['level'];
$rankTitle = getRankTitle($currentLevel);

// Get all rewards with claim status
$stmt = $conn->prepare("
    SELECT cr.*,
           ucr.claimed_at,
           CASE WHEN ucr.id IS NOT NULL THEN 1 ELSE 0 END as is_claimed,
           CASE WHEN ? >= cr.level_required THEN 1 ELSE 0 END as is_unlocked
    FROM clothing_rewards cr
    LEFT JOIN user_claimed_rewards ucr ON cr.id = ucr.reward_id AND ucr.user_id = ?
    ORDER BY cr.level_required ASC, cr.id ASC
");
$stmt->execute([$currentLevel, $userId]);
$rewards = $stmt->fetchAll();

// Count stats
$totalRewards = count($rewards);
$claimedRewards = count(array_filter($rewards, function($r) { return $r['is_claimed']; }));
$unlockedRewards = count(array_filter($rewards, function($r) { return $r['is_unlocked'] && !$r['is_claimed']; }));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clothing Rewards - CityCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding-bottom: 3rem;
        }
        .reward-card {
            transition: transform 0.2s, box-shadow 0.2s;
            border: none;
            height: 100%;
        }
        .reward-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        .reward-icon {
            font-size: 4rem;
            filter: grayscale(100%);
            opacity: 0.3;
        }
        .reward-card.unlocked .reward-icon {
            filter: grayscale(0%);
            opacity: 1;
        }
        .reward-card.claimed .reward-icon {
            filter: grayscale(0%);
            opacity: 1;
        }
        .reward-card.claimed {
            border: 3px solid #28a745;
        }
        .rarity-common { border-left: 4px solid #6c757d; }
        .rarity-rare { border-left: 4px solid #0dcaf0; }
        .rarity-epic { border-left: 4px solid #6f42c1; }
        .rarity-legendary { border-left: 4px solid #ffc107; }
        .locked-overlay {
            position: relative;
        }
        .locked-overlay::after {
            content: '🔒';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 2rem;
            opacity: 0.8;
        }
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-shield-check"></i> CityCare
            </a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3">
                    <i class="bi bi-person-circle"></i> <?= htmlspecialchars($user['username']) ?>
                    <span class="badge bg-warning text-dark ms-2">Lv.<?= $currentLevel ?></span>
                </span>
                <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2">
                    <i class="bi bi-house"></i> Dashboard
                </a>
                <a href="logout.php" class="btn btn-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= $_SESSION['success'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= $_SESSION['error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="stats-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-3">
                        <i class="bi bi-gift-fill text-primary"></i> Clothing Rewards
                    </h2>
                    <p class="lead mb-2">Unlock exclusive clothing items as you level up!</p>
                    <p class="text-muted">Level up by submitting reports and helping your community.</p>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <h5>Your Progress</h5>
                        <div class="d-flex justify-content-around mt-3">
                            <div>
                                <h3 class="text-success"><?= $claimedRewards ?></h3>
                                <small class="text-muted">Claimed</small>
                            </div>
                            <div>
                                <h3 class="text-warning"><?= $unlockedRewards ?></h3>
                                <small class="text-muted">Available</small>
                            </div>
                            <div>
                                <h3 class="text-secondary"><?= $totalRewards - $claimedRewards - $unlockedRewards ?></h3>
                                <small class="text-muted">Locked</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <?php foreach ($rewards as $reward): ?>
                <div class="col-md-6 col-lg-4 col-xl-3">
                    <div class="card reward-card rarity-<?= $reward['rarity'] ?> <?= $reward['is_unlocked'] ? 'unlocked' : '' ?> <?= $reward['is_claimed'] ? 'claimed' : '' ?>">
                        <div class="card-body text-center">
                            <div class="<?= !$reward['is_unlocked'] ? 'locked-overlay' : '' ?>">
                                <div class="reward-icon mb-3">
                                    <?= $reward['icon'] ?>
                                </div>
                            </div>
                            
                            <h5 class="card-title"><?= htmlspecialchars($reward['name']) ?></h5>
                            <p class="card-text small text-muted"><?= htmlspecialchars($reward['description']) ?></p>
                            
                            <div class="mt-3">
                                <span class="badge bg-primary">Level <?= $reward['level_required'] ?></span>
                                <span class="badge bg-<?= 
                                    $reward['rarity'] == 'legendary' ? 'warning' : 
                                    ($reward['rarity'] == 'epic' ? 'purple' : 
                                    ($reward['rarity'] == 'rare' ? 'info' : 'secondary')) 
                                ?> text-<?= $reward['rarity'] == 'legendary' ? 'dark' : 'white' ?>">
                                    <?= ucfirst($reward['rarity']) ?>
                                </span>
                            </div>

                            <?php if ($reward['is_claimed']): ?>
                                <div class="mt-3">
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle-fill"></i> Claimed
                                    </span>
                                    <br>
                                    <small class="text-muted">
                                        <?= date('M d, Y', strtotime($reward['claimed_at'])) ?>
                                    </small>
                                </div>
                            <?php elseif ($reward['is_unlocked']): ?>
                                <form method="POST" class="mt-3">
                                    <input type="hidden" name="reward_id" value="<?= $reward['id'] ?>">
                                    <button type="submit" name="claim_reward" class="btn btn-success w-100">
                                        <i class="bi bi-gift"></i> Claim Now
                                    </button>
                                </form>
                            <?php else: ?>
                                <div class="mt-3">
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-lock-fill"></i> Locked
                                    </span>
                                    <br>
                                    <small class="text-muted">
                                        <?= $reward['level_required'] - $currentLevel ?> levels to go
                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($unlockedRewards > 0): ?>
            <div class="alert alert-warning mt-4 text-center">
                <h5><i class="bi bi-gift-fill"></i> You have <?= $unlockedRewards ?> reward<?= $unlockedRewards > 1 ? 's' : '' ?> ready to claim!</h5>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
