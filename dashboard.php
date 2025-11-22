<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

$userId = $_SESSION['user_id'];

// Get user info
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

$currentLevel = $user['level'];
$currentXp = $user['xp'];
$rankTitle = getRankTitle($currentLevel);
$levelProgress = getLevelProgress($currentXp, $currentLevel);
$xpToNextLevel = getXpToNextLevel($currentXp, $currentLevel);
$xpForNextLevel = getXpForLevel($currentLevel);

$userBadges = getUserBadges($conn, $userId);
$badgeCount = count($userBadges);

// Get categories
$categories = getCategories($conn);

// Get user's reports
$stmt = $conn->prepare("
    SELECT r.*, c.name as category_name 
    FROM reports r 
    JOIN categories c ON r.category_id = c.id 
    WHERE r.user_id = ? 
    ORDER BY r.created_at DESC
");
$stmt->execute([$userId]);
$userReports = $stmt->fetchAll();

// Get all reports for map
$stmt = $conn->query("
    SELECT r.*, c.name as category_name, u.username 
    FROM reports r 
    JOIN categories c ON r.category_id = c.id 
    JOIN users u ON r.user_id = u.id 
    ORDER BY r.created_at DESC
");
$allReports = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CityCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map, #submitMap { height: 500px; border-radius: 8px; }
        .tab-content { margin-top: 20px; }
        .report-card { cursor: pointer; transition: transform 0.2s; }
        .report-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .user-location-marker {
            background: #007bff;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 0 10px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php"><i class="bi bi-shield-check"></i> CityCare</a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3">
                    <i class="bi bi-person-circle"></i> <?= clean($user['username']) ?>
                    <span class="badge bg-warning text-dark ms-2">Lv.<?= $currentLevel ?></span>
                </span>
                <a href="logout.php" class="btn btn-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Added XP progress card -->
        <div class="card shadow-sm mb-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="mb-1"><i class="bi bi-trophy-fill"></i> <?= clean($user['username']) ?> - <?= $rankTitle ?></h5>
                        <p class="mb-2">Level <?= $currentLevel ?> • <?= number_format($currentXp) ?> Total XP</p>
                        <div class="progress" style="height: 25px; background: rgba(255,255,255,0.3);">
                            <div class="progress-bar bg-warning" role="progressbar" 
                                 style="width: <?= $levelProgress ?>%;" 
                                 aria-valuenow="<?= $levelProgress ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                <?= round($levelProgress) ?>%
                            </div>
                        </div>
                        <small><?= number_format($xpToNextLevel) ?> XP until Level <?= $currentLevel + 1 ?></small>
                    </div>
                    <div class="col-md-4 text-center">
                        <div style="font-size: 3rem;">
                            <?php if ($currentLevel >= 30): ?>
                                🏆
                            <?php elseif ($currentLevel >= 20): ?>
                                🥇
                            <?php elseif ($currentLevel >= 10): ?>
                                🥈
                            <?php elseif ($currentLevel >= 5): ?>
                                🥉
                            <?php else: ?>
                                ⭐
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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

        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#map-tab"><i class="bi bi-map"></i> Map</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#reports-tab"><i class="bi bi-list-ul"></i> My Reports</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#submit-tab"><i class="bi bi-plus-circle"></i> Submit Report</a>
            </li>
            <!-- Added badges tab -->
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#badges-tab">
                    <i class="bi bi-award"></i> Badges 
                    <span class="badge bg-primary"><?= $badgeCount ?></span>
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Map Tab -->
            <div id="map-tab" class="tab-pane fade show active">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div id="map"></div>
                    </div>
                </div>
            </div>

            <!-- Reports Tab -->
            <div id="reports-tab" class="tab-pane fade">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">My Reports (<?= count($userReports) ?>)</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($userReports)): ?>
                            <p class="text-muted">You haven't submitted any reports yet.</p>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($userReports as $report): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card report-card" onclick="window.location.href='report.php?id=<?= $report['id'] ?>'">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="mb-0"><?= clean($report['title']) ?></h6>
                                                    <span class="badge bg-<?= getStatusClass($report['status']) ?>"><?= ucfirst(str_replace('_', ' ', $report['status'])) ?></span>
                                                </div>
                                                <p class="text-muted small mb-2"><?= clean($report['category_name']) ?> • <?= formatDate($report['created_at']) ?></p>
                                                <p class="mb-0 small"><?= substr(clean($report['description']), 0, 100) ?>...</p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Submit Report Tab -->
            <div id="submit-tab" class="tab-pane fade">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Submit New Report</h5>
                    </div>
                    <div class="card-body">
                        <!-- Added interactive map for location selection -->
                        <div class="mb-3">
                            <label class="form-label">Select Location on Map</label>
                            <div id="submitMap" style="height: 300px; border-radius: 8px; margin-bottom: 10px;"></div>
                            <small class="text-muted">Click on the map to set the report location</small>
                        </div>
                        
                        <form action="submit_report.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">Select a category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['id'] ?>"><?= clean($category['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="4" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Location Address</label>
                                <input type="text" name="location" class="form-control" required placeholder="e.g., 123 Main St, New York, NY">
                            </div>
                            <!-- Made latitude/longitude readonly since they're set by map click -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Latitude</label>
                                    <input type="text" name="latitude" id="latitude" class="form-control" step="any" readonly required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Longitude</label>
                                    <input type="text" name="longitude" id="longitude" class="form-control" step="any" readonly required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Priority</label>
                                <select name="priority" class="form-select">
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Upload Image (Optional)</label>
                                <input type="file" name="image" class="form-control" accept="image/*">
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i> Submit Report</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Added Badges Tab -->
            <div id="badges-tab" class="tab-pane fade">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Your Badges (<?= $badgeCount ?>)</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($userBadges)): ?>
                            <div class="text-center py-5">
                                <div style="font-size: 4rem; opacity: 0.3;">🏆</div>
                                <p class="text-muted">You haven't earned any badges yet.</p>
                                <p class="text-muted small">Submit reports, maintain streaks, and level up to earn badges!</p>
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($userBadges as $badge): ?>
                                    <div class="col-md-4 col-lg-3 mb-3">
                                        <div class="card h-100 text-center border-primary">
                                            <div class="card-body">
                                                <div style="font-size: 3rem;"><?= $badge['icon'] ?></div>
                                                <h6 class="mt-2 mb-1"><?= clean($badge['name']) ?></h6>
                                                <p class="small text-muted mb-2"><?= clean($badge['description']) ?></p>
                                                <small class="text-muted">Earned: <?= date('M d, Y', strtotime($badge['earned_at'])) ?></small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Badge Progress Section -->
                        <hr class="my-4">
                        <h6 class="mb-3">Badge Progress</h6>
                        <?php
                        // Get all badges to show progress
                        $allBadges = $conn->query("SELECT * FROM badges ORDER BY badge_type, requirement_value")->fetchAll();
                        $earnedBadgeIds = array_column($userBadges, 'id');
                        
                        // Group by type
                        $badgesByType = [];
                        foreach ($allBadges as $badge) {
                            if (!in_array($badge['id'], $earnedBadgeIds)) {
                                $badgesByType[$badge['badge_type']][] = $badge;
                            }
                        }
                        ?>
                        
                        <div class="row">
                            <?php foreach ($badgesByType as $type => $badges): ?>
                                <?php if (count($badges) > 0 && count($badges) <= 5): // Only show types with few remaining ?>
                                    <?php foreach ($badges as $badge): ?>
                                        <div class="col-md-4 col-lg-3 mb-3">
                                            <div class="card h-100 text-center" style="opacity: 0.6;">
                                                <div class="card-body">
                                                    <div style="font-size: 2.5rem; filter: grayscale(100%);"><?= $badge['icon'] ?></div>
                                                    <h6 class="mt-2 mb-1 small"><?= clean($badge['name']) ?></h6>
                                                    <p class="small text-muted mb-0"><?= clean($badge['description']) ?></p>
                                                    <small class="badge bg-secondary mt-2">Locked</small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Initialize map
        const map = L.map('map');
        
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const userLat = position.coords.latitude;
                    const userLng = position.coords.longitude;
                    map.setView([userLat, userLng], 13);
                    
                    L.marker([userLat, userLng], {
                        icon: L.divIcon({
                            className: 'user-location-marker',
                            html: '<div style="background: #007bff; width: 16px; height: 16px; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 10px rgba(0,0,0,0.3);"></div>',
                            iconSize: [16, 16]
                        })
                    }).addTo(map).bindPopup('Your Location');
                },
                function(error) {
                    map.setView([40.7128, -74.0060], 13);
                }
            );
        } else {
            map.setView([40.7128, -74.0060], 13);
        }
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Add markers for all reports
        const reports = <?= json_encode($allReports) ?>;
        reports.forEach(report => {
            if (report.latitude && report.longitude) {
                const marker = L.marker([report.latitude, report.longitude]).addTo(map);
                marker.bindPopup(`
                    <strong>${report.title}</strong><br>
                    <em>${report.category_name}</em><br>
                    Status: ${report.status}<br>
                    <a href="report.php?id=${report.id}">View Details</a>
                `);
            }
        });

        let submitMap;
        let submitMarker;
        
        // Initialize submit map when tab is shown
        document.querySelector('a[href="#submit-tab"]').addEventListener('shown.bs.tab', function() {
            if (!submitMap) {
                submitMap = L.map('submitMap');
                
                // Use geolocation or default
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        function(position) {
                            submitMap.setView([position.coords.latitude, position.coords.longitude], 15);
                        },
                        function(error) {
                            submitMap.setView([40.7128, -74.0060], 13);
                        }
                    );
                } else {
                    submitMap.setView([40.7128, -74.0060], 13);
                }
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(submitMap);
                
                // Add click event to set location
                submitMap.on('click', function(e) {
                    const lat = e.latlng.lat.toFixed(6);
                    const lng = e.latlng.lng.toFixed(6);
                    
                    // Update form fields
                    document.getElementById('latitude').value = lat;
                    document.getElementById('longitude').value = lng;
                    
                    // Remove old marker if exists
                    if (submitMarker) {
                        submitMap.removeLayer(submitMarker);
                    }
                    
                    // Add new marker
                    submitMarker = L.marker([lat, lng], {
                        icon: L.icon({
                            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                            iconSize: [25, 41],
                            iconAnchor: [12, 41],
                            popupAnchor: [1, -34],
                            shadowSize: [41, 41]
                        })
                    }).addTo(submitMap);
                    
                    submitMarker.bindPopup(`<strong>Report Location</strong><br>Lat: ${lat}, Lng: ${lng}`).openPopup();
                });
                
                // Fix map display issue
                setTimeout(() => submitMap.invalidateSize(), 100);
            }
        });
    </script>
</body>
</html>
