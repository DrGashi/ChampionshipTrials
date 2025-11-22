<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
requireLogin();
requireAdmin();

// Get overall statistics
$stats = $conn->query("SELECT 
    COUNT(*) as total_reports,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
    SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
    FROM reports")->fetch();

$total_users = $conn->query("SELECT COUNT(*) FROM users WHERE is_admin = 0")->fetchColumn();

// Get recent reports
$recent_reports = $conn->query("SELECT r.*, c.name as category_name, c.icon as category_icon, u.full_name as user_name 
    FROM reports r 
    JOIN categories c ON r.category_id = c.id 
    JOIN users u ON r.user_id = u.id 
    ORDER BY r.created_at DESC 
    LIMIT 10")->fetchAll();

// Get all reports for map
$all_reports = $conn->query("SELECT r.*, c.name as category_name, c.icon as category_icon, c.color 
    FROM reports r 
    JOIN categories c ON r.category_id = c.id")->fetchAll();

// Get category statistics
$category_stats = $conn->query("SELECT c.name, c.icon, COUNT(r.id) as count 
    FROM categories c 
    LEFT JOIN reports r ON c.id = r.category_id 
    GROUP BY c.id 
    ORDER BY count DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CityCare</title>
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
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            position: fixed;
            width: 250px;
            padding: 20px;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            margin: 5px 0;
            border-radius: 10px;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        .main-content {
            margin-left: 250px;
            padding: 30px;
        }
        .stat-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s;
            background: white;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
        }
        #admin-map {
            height: 500px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        .report-table {
            background: white;
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
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h3 class="text-center mb-4"><i class="fas fa-city"></i> CityCare Admin</h3>
        <nav class="nav flex-column">
            <a class="nav-link active" href="dashboard.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a class="nav-link" href="reports.php">
                <i class="fas fa-clipboard-list"></i> All Reports
            </a>
            <a class="nav-link" href="users.php">
                <i class="fas fa-users"></i> Users
            </a>
            <a class="nav-link" href="categories.php">
                <i class="fas fa-tags"></i> Categories
            </a>
            <a class="nav-link" href="analytics.php">
                <i class="fas fa-chart-bar"></i> Analytics
            </a>
        </nav>
        <hr class="my-4" style="border-color: rgba(255,255,255,0.3);">
        <div class="mt-auto">
            <p class="mb-1"><i class="fas fa-user-shield"></i> <?php echo $_SESSION['full_name']; ?></p>
            <a href="../logout.php" class="nav-link">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h2 class="mb-4">Dashboard Overview</h2>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-2 col-md-4 mb-3">
                <div class="stat-card card">
                    <div class="card-body text-center">
                        <div class="stat-icon bg-primary text-white mx-auto mb-3">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <h6 class="text-muted">Total Reports</h6>
                        <h3><?php echo $stats['total_reports']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 mb-3">
                <div class="stat-card card">
                    <div class="card-body text-center">
                        <div class="stat-icon bg-warning text-white mx-auto mb-3">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h6 class="text-muted">Pending</h6>
                        <h3><?php echo $stats['pending']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 mb-3">
                <div class="stat-card card">
                    <div class="card-body text-center">
                        <div class="stat-icon bg-info text-white mx-auto mb-3">
                            <i class="fas fa-spinner"></i>
                        </div>
                        <h6 class="text-muted">In Progress</h6>
                        <h3><?php echo $stats['in_progress']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 mb-3">
                <div class="stat-card card">
                    <div class="card-body text-center">
                        <div class="stat-icon bg-success text-white mx-auto mb-3">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h6 class="text-muted">Resolved</h6>
                        <h3><?php echo $stats['resolved']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 mb-3">
                <div class="stat-card card">
                    <div class="card-body text-center">
                        <div class="stat-icon bg-danger text-white mx-auto mb-3">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <h6 class="text-muted">Rejected</h6>
                        <h3><?php echo $stats['rejected']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 mb-3">
                <div class="stat-card card">
                    <div class="card-body text-center">
                        <div class="stat-icon bg-secondary text-white mx-auto mb-3">
                            <i class="fas fa-users"></i>
                        </div>
                        <h6 class="text-muted">Total Users</h6>
                        <h3><?php echo $total_users; ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs Section -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <ul class="nav nav-tabs" id="adminTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="map-tab" data-bs-toggle="tab" data-bs-target="#map-pane" type="button">
                            <i class="fas fa-map-marked-alt"></i> Reports Map
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="recent-tab" data-bs-toggle="tab" data-bs-target="#recent-pane" type="button">
                            <i class="fas fa-list"></i> Recent Reports
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="stats-tab" data-bs-toggle="tab" data-bs-target="#stats-pane" type="button">
                            <i class="fas fa-chart-pie"></i> Category Stats
                        </button>
                    </li>
                </ul>

                <div class="tab-content mt-4" id="adminTabContent">
                    <!-- Map Tab -->
                    <div class="tab-pane fade show active" id="map-pane">
                        <div id="admin-map"></div>
                    </div>

                    <!-- Recent Reports Tab -->
                    <div class="tab-pane fade" id="recent-pane">
                        <h4 class="mb-4">Recent Reports</h4>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Reporter</th>
                                        <th>Status</th>
                                        <th>Priority</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_reports as $report): ?>
                                    <tr>
                                        <td><?php echo $report['id']; ?></td>
                                        <td>
                                            <span style="font-size: 1.2rem;"><?php echo $report['category_icon']; ?></span>
                                            <?php echo htmlspecialchars($report['title']); ?>
                                        </td>
                                        <td><?php echo $report['category_name']; ?></td>
                                        <td><?php echo htmlspecialchars($report['user_name']); ?></td>
                                        <td><?php echo getStatusBadge($report['status']); ?></td>
                                        <td><?php echo getPriorityBadge($report['priority']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($report['created_at'])); ?></td>
                                        <td>
                                            <a href="view_report.php?id=<?php echo $report['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Category Stats Tab -->
                    <div class="tab-pane fade" id="stats-pane">
                        <h4 class="mb-4">Reports by Category</h4>
                        <div class="row">
                            <?php foreach ($category_stats as $cat): ?>
                            <div class="col-md-3 mb-3">
                                <div class="card stat-card">
                                    <div class="card-body text-center">
                                        <div style="font-size: 3rem;"><?php echo $cat['icon']; ?></div>
                                        <h6 class="mt-2"><?php echo $cat['name']; ?></h6>
                                        <h4><?php echo $cat['count']; ?> reports</h4>
                                    </div>
                                </div>
                            </div>
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
        // Initialize admin map
        var adminMap = L.map('admin-map').setView([42.657362, 21.156723], 12);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(adminMap);

        // Define marker colors based on status
        var statusColors = {
            'pending': 'orange',
            'in_progress': 'blue',
            'resolved': 'green',
            'rejected': 'red'
        };

        // Add markers for all reports
        <?php foreach ($all_reports as $report): ?>
        var marker = L.marker([<?php echo $report['latitude']; ?>, <?php echo $report['longitude']; ?>])
            .addTo(adminMap)
            .bindPopup(`
                <strong><?php echo htmlspecialchars($report['title']); ?></strong><br>
                <span style="font-size: 1.2rem;"><?php echo $report['category_icon']; ?></span> 
                <?php echo $report['category_name']; ?><br>
                Status: <?php echo $report['status']; ?><br>
                <a href="view_report.php?id=<?php echo $report['id']; ?>" class="btn btn-sm btn-primary mt-2">View Details</a>
            `);
        <?php endforeach; ?>
    </script>
</body>
</html>