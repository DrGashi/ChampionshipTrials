<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// Get user statistics
$stmt = $conn->prepare("SELECT 
    COUNT(*) as total_reports,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
    SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved
    FROM reports WHERE user_id = ?");
$stmt->execute([$user_id]);
$stats = $stmt->fetch();

// Get user's reports
$stmt = $conn->prepare("SELECT r.*, c.name as category_name, c.icon as category_icon 
    FROM reports r 
    JOIN categories c ON r.category_id = c.id 
    WHERE r.user_id = ? 
    ORDER BY r.created_at DESC");
$stmt->execute([$user_id]);
$user_reports = $stmt->fetchAll();

// Get all categories
$categories = $conn->query("SELECT * FROM categories")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CityCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
        }
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .stat-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        #map {
            height: 500px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        .badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
        }
        .nav-tabs .nav-link.active {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white !important;
            border: none;
        }
        .nav-tabs .nav-link {
            color: #666;
            border: none;
            margin-right: 10px;
            border-radius: 10px;
        }
        .report-card {
            border: none;
            border-radius: 15px;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }
        .report-card:hover {
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-city"></i> CityCare
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link"><i class="fas fa-user"></i> <?php echo $_SESSION['full_name']; ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-primary text-white me-3">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0">Total Reports</h6>
                            <h3 class="mb-0"><?php echo $stats['total_reports']; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-warning text-white me-3">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0">Pending</h6>
                            <h3 class="mb-0"><?php echo $stats['pending']; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-info text-white me-3">
                            <i class="fas fa-spinner"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0">In Progress</h6>
                            <h3 class="mb-0"><?php echo $stats['in_progress']; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-success text-white me-3">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0">Resolved</h6>
                            <h3 class="mb-0"><?php echo $stats['resolved']; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs Section -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="map-tab" data-bs-toggle="tab" data-bs-target="#map-pane" type="button">
                            <i class="fas fa-map-marked-alt"></i> Map View
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="reports-tab" data-bs-toggle="tab" data-bs-target="#reports-pane" type="button">
                            <i class="fas fa-list"></i> My Reports
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="new-tab" data-bs-toggle="tab" data-bs-target="#new-pane" type="button">
                            <i class="fas fa-plus-circle"></i> Submit Report
                        </button>
                    </li>
                </ul>

                <div class="tab-content mt-4" id="myTabContent">
                    <!-- Map Tab -->
                    <div class="tab-pane fade show active" id="map-pane">
                        <div id="map"></div>
                    </div>

                    <!-- My Reports Tab -->
                    <div class="tab-pane fade" id="reports-pane">
                        <h4 class="mb-4">My Submitted Reports</h4>
                        <?php if (empty($user_reports)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> You haven't submitted any reports yet.
                            </div>
                        <?php else: ?>
                            <?php foreach ($user_reports as $report): ?>
                                <div class="report-card card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <h5 class="card-title">
                                                    <span style="font-size: 1.5rem;"><?php echo $report['category_icon']; ?></span>
                                                    <?php echo htmlspecialchars($report['title']); ?>
                                                </h5>
                                                <p class="text-muted mb-2">
                                                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($report['location_address']); ?>
                                                </p>
                                                <p class="card-text"><?php echo htmlspecialchars(substr($report['description'], 0, 150)); ?>...</p>
                                                <div class="mb-2">
                                                    <?php echo getStatusBadge($report['status']); ?>
                                                    <?php echo getPriorityBadge($report['priority']); ?>
                                                    <span class="badge bg-secondary"><?php echo $report['category_name']; ?></span>
                                                </div>
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($report['created_at'])); ?>
                                                </small>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <?php if ($report['image_path']): ?>
                                                    <img src="<?php echo $report['image_path']; ?>" class="img-fluid rounded" style="max-height: 150px;">
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Submit Report Tab -->
                    <div class="tab-pane fade" id="new-pane">
                        <h4 class="mb-4">Submit New Report</h4>
                        <form action="submit_report.php" method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Title</label>
                                        <input type="text" name="title" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Category</label>
                                        <select name="category_id" class="form-control" required>
                                            <option value="">Select Category</option>
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?php echo $cat['id']; ?>">
                                                    <?php echo $cat['icon'] . ' ' . $cat['name']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="4" required></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Location Address</label>
                                <input type="text" name="location_address" id="location_address" class="form-control" required>
                                <small class="text-muted">Click on the map below to select location</small>
                            </div>
                            
                            <div class="mb-3">
                                <div id="submit-map" style="height: 300px; border-radius: 10px;"></div>
                                <input type="hidden" name="latitude" id="latitude" required>
                                <input type="hidden" name="longitude" id="longitude" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Priority</label>
                                        <select name="priority" class="form-control" required>
                                            <option value="low">Low</option>
                                            <option value="medium" selected>Medium</option>
                                            <option value="high">High</option>
                                            <option value="urgent">Urgent</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Upload Image (optional)</label>
                                        <input type="file" name="image" class="form-control" accept="image/*">
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane"></i> Submit Report
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Initialize main map
        var map = L.map('map').setView([42.657362, 21.156723], 12);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Add markers for user's reports
        <?php foreach ($user_reports as $report): ?>
        L.marker([<?php echo $report['latitude']; ?>, <?php echo $report['longitude']; ?>])
            .addTo(map)
            .bindPopup(`
                <strong><?php echo htmlspecialchars($report['title']); ?></strong><br>
                <?php echo htmlspecialchars($report['location_address']); ?><br>
                Status: <?php echo $report['status']; ?>
            `);
        <?php endforeach; ?>

        // Initialize submit map
        var submitMap = L.map('submit-map').setView([42.657362, 21.156723], 10);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(submitMap);

        var marker;
        submitMap.on('click', function(e) {
            if (marker) {
                submitMap.removeLayer(marker);
            }
            
            marker = L.marker(e.latlng).addTo(submitMap);
            document.getElementById('latitude').value = e.latlng.lat;
            document.getElementById('longitude').value = e.latlng.lng;
            
            // Reverse geocoding (simplified - you can use a proper geocoding service)
            document.getElementById('location_address').value = 
                'Lat: ' + e.latlng.lat.toFixed(6) + ', Lng: ' + e.latlng.lng.toFixed(6);
        });
    </script>
</body>
</html>