<!-- Add this to your dashboard.php file in the tabs section, after the badges tab -->

<!-- Rewards Tab Button (add to tab navigation) -->
<li class="nav-item">
    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#rewards-tab">
        <i class="bi bi-gift-fill"></i> Rewards
    </button>
</li>

<!-- Rewards Tab Content (add to tab content section) -->
<div id="rewards-tab" class="tab-pane fade">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-gift-fill"></i> Clothing Rewards</h5>
            <a href="rewards.php" class="btn btn-primary btn-sm">
                View All Rewards <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        <div class="card-body">
            <?php
            // Get recent unlocked rewards
            $stmt = $conn->prepare("
                SELECT cr.*,
                       ucr.claimed_at,
                       CASE WHEN ucr.id IS NOT NULL THEN 1 ELSE 0 END as is_claimed
                FROM clothing_rewards cr
                LEFT JOIN user_claimed_rewards ucr ON cr.id = ucr.reward_id AND ucr.user_id = ?
                WHERE cr.level_required <= ?
                ORDER BY cr.level_required DESC
                LIMIT 6
            ");
            $stmt->execute([$userId, $currentLevel]);
            $recentRewards = $stmt->fetchAll();
            
            $totalUnlocked = count($recentRewards);
            $totalClaimed = count(array_filter($recentRewards, function($r) { return $r['is_claimed']; }));
            ?>
            
            <div class="alert alert-info mb-3">
                <strong>Level Up to Unlock Rewards!</strong><br>
                You've unlocked <?= $totalUnlocked ?> rewards and claimed <?= $totalClaimed ?> of them.
            </div>

            <?php if (empty($recentRewards)): ?>
                <div class="text-center py-5">
                    <div style="font-size: 4rem; opacity: 0.3;">🎁</div>
                    <p class="text-muted">Keep leveling up to unlock clothing rewards!</p>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($recentRewards as $reward): ?>
                        <div class="col-md-4 col-lg-2">
                            <div class="card text-center h-100 <?= $reward['is_claimed'] ? 'border-success' : 'border-warning' ?>">
                                <div class="card-body p-2">
                                    <div style="font-size: 2.5rem;"><?= $reward['icon'] ?></div>
                                    <h6 class="small mb-1"><?= htmlspecialchars($reward['name']) ?></h6>
                                    <small class="badge bg-primary">Lv.<?= $reward['level_required'] ?></small>
                                    <?php if ($reward['is_claimed']): ?>
                                        <br><span class="badge bg-success mt-1">Claimed</span>
                                    <?php else: ?>
                                        <br><span class="badge bg-warning text-dark mt-1">Available!</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="text-center mt-3">
                    <a href="rewards.php" class="btn btn-primary">
                        <i class="bi bi-gift"></i> View All & Claim Rewards
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
