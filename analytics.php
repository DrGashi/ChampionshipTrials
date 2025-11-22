<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
requireLogin();
requireAdmin();

$report_id = (int)$_GET['id'];

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $status = sanitize($_POST['status']);
    $priority = sanitize($_POST['priority']);
    $admin_notes = sanitize($_POST['admin_notes']);
    
    $resolved_at = ($status == 'resolved') ? date('Y-m-d H:i:s') : null;
    
    $stmt = $conn->prepare("UPDATE reports SET status = ?, priority = ?, admin_notes = ?, resolved_at = ? WHERE id = ?");
    $stmt->execute([$status, $priority, $admin_notes, $resolved_at, $report_id]);
    
    header("Location: view_report.php?id=$report_id&updated=1");
    exit();
}

// Get report details
$stmt = $conn->prepare("SELECT r.*, c.name as category_name, c.icon as category_icon, u.full_name as user_name, u.email as user_email, u.phone as user_phone 
    FROM reports r 
    JOIN categories c ON r.category_id = c.id 
    JOIN users u ON r.user_id = u.id 
    WHERE r.id = ?");
$stmt->execute([$report_id]);
$report = $stmt->fetch();

if (!$report) {
    die("Report not found");
}

// Get comments
$stmt = $conn->prepare("SELECT c.*, u.full_name as user_name 
    FROM comments c 
    JOIN users u ON c.user_id = u.id 
    WHERE c.report_id = ? 
    ORDER BY c.created_at ASC");
$stmt->execute([$report_id]);
$comments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Report - CityCare Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
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
        }
        .sidebar .nav-link:hover {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        .main-content {
            margin-left: 250px;
            padding: 30px;
        }
        #report-map {
            height: 300px;
            border-radius: 15px;
        }
        .comment-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h3 class="text-center mb-4"><i class="fas fa-city"></i> CityCare Admin</h3>
        <nav class="nav flex-column">
            <a class="nav-link" href="dashboard.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a class="nav-link" href="reports.php">
                <i class="fas fa-clipboard-list"></i> All Reports
            </a>
            <a class="nav-link" href="../logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Report Details #<?php echo $report['id']; ?></h2>
            <a href="dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <?php if (isset($_GET['updated'])): ?>
            <div class="alert alert-success">Report updated successfully!</div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <h4>
                            <span style="font-size: 1.8rem;"><?php echo $report['category_icon']; ?></span>
                            <?php echo htmlspecialchars($report['title']); ?>
                        </h4>
                        <p class="text-muted">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($report['user_name']); ?> | 
                            <i class="fas fa-calendar"></i> <?php echo date('M d, Y H:i', strtotime($report['created_at'])); ?>
                        </p>
                        
                        <div class="mb-3">
                            <?php echo getStatusBadge($report['status']); ?>
                            <?php echo getPriorityBadge($report['priority']); ?>
                            <span class="badge bg-secondary"><?php echo $report['category_name']; ?></span>
                        </div>

                        <h5>Description</h5>
                        <p><?php echo nl2br(htmlspecialchars($report['description'])); ?></p>

                        <h5>Location</h5>
                        <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($report['location_address']); ?></p>
                        <div id="report-map" class="mb-3"></div>

                        <?php if ($report['image_path']): ?>
                            <h5>Image</h5>
                            <img src="../<?php echo $report['image_path']; ?>" class="img-fluid rounded" style="max-width: 500px;">
                        <?php endif; ?>

                        <?php if ($report['admin_notes']): ?>
                            <h5 class="mt-4">Admin Notes</h5>
                            <div class="alert alert-info">
                                <?php echo nl2br(htmlspecialchars($report['admin_notes'])); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Comments Section -->
                <div class="card">
                    <div class="card-body">
                        <h5>Comments (<?php echo count($comments); ?>)</h5>
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment-box">
                                <strong><?php echo htmlspecialchars($comment['user_name']); ?></strong>
                                <small class="text-muted">- <?php echo date('M d, Y H:i', strtotime($comment['created_at'])); ?></small>
                                <p class="mb-0 mt-2"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Reporter Information</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($report['user_name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($report['user_email']); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($report['user_phone'] ?: 'N/A'); ?></p>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Update Report</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control" required>
                                    <option value="pending" <?php echo $report['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="in_progress" <?php echo $report['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="resolved" <?php echo $report['status'] == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                    <option value="rejected" <?php echo $report['status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Priority</label>
                                <select name="priority" class="form-control" required>
                                    <option value="low" <?php echo $report['priority'] == 'low' ? 'selected' : ''; ?>>Low</option>
                                    <option value="medium" <?php echo $report['priority'] == 'medium' ? 'selected' : ''; ?>>Medium</option>
                                    <option value="high" <?php echo $report['priority'] == 'high' ? 'selected' : ''; ?>>High</option>
                                    <option value="urgent" <?php echo $report['priority'] == 'urgent' ? 'selected' : ''; ?>>Urgent</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Admin Notes</label>
                                <textarea name="admin_notes" class="form-control" rows="4"><?php echo htmlspecialchars($report['admin_notes']); ?></textarea>
                            </div>

                            <button type="submit" name="update_status" class="btn btn-success w-100">
                                <i class="fas fa-save"></i> Update Report
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
        var reportMap = L.map('report-map').setView([<?php echo $report['latitude']; ?>, <?php echo $report['longitude']; ?>], 15);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(reportMap);

        L.marker([<?php echo $report['latitude']; ?>, <?php echo $report['longitude']; ?>])
            .addTo(reportMap)
            .bindPopup('<?php echo htmlspecialchars($report['location_address']); ?>')
            .openPopup();
    </script>
</body>
</html>