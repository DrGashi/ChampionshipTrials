<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit();
}

$report_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$report_id) {
    header('Location: dashboard.php');
    exit();
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $admin_notes = $_POST['admin_notes'];
    
    $stmt = $conn->prepare("UPDATE reports SET status = ?, admin_notes = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$new_status, $admin_notes, $report_id]);
    
    $success = "Report updated successfully!";
}

// Handle report deletion
if (isset($_POST['delete_report'])) {
    $stmt = $conn->prepare("DELETE FROM reports WHERE id = ?");
    $stmt->execute([$report_id]);
    header('Location: dashboard.php?deleted=1');
    exit();
}

// Fetch report details
$stmt = $conn->prepare("
    SELECT r.*, u.username, u.email, c.name as category_name 
    FROM reports r 
    JOIN users u ON r.user_id = u.id 
    JOIN categories c ON r.category_id = c.id 
    WHERE r.id = ?
");
$stmt->execute([$report_id]);
$report = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$report) {
    header('Location: dashboard.php');
    exit();
}

$pageTitle = "View Report #" . $report_id;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - CityCare Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .main-container {
            padding: 2rem 0;
        }
        .report-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .report-image {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        #map {
            height: 300px;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        .status-badge {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
        }
        .priority-badge {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
        }
        .info-row {
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }
        .info-label {
            font-weight: 600;
            color: #666;
            margin-bottom: 0.25rem;
        }
        .info-value {
            color: #333;
            font-size: 1.1rem;
        }
        .btn-back {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 1rem;
        }
        .btn-back:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body>
    <div class="container main-container">
        <a href="dashboard.php" class="btn-back">← Back to Dashboard</a>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="report-card">
            <div class="row">
                <div class="col-md-8">
                    <h2>Report #<?php echo $report['id']; ?>: <?php echo htmlspecialchars($report['title']); ?></h2>
                    
                    <div class="mb-3">
                        <span class="status-badge bg-<?php 
                            echo $report['status'] === 'pending' ? 'warning' : 
                                ($report['status'] === 'in_progress' ? 'info' : 
                                ($report['status'] === 'resolved' ? 'success' : 'danger')); 
                        ?> text-white">
                            <?php echo ucfirst(str_replace('_', ' ', $report['status'])); ?>
                        </span>
                        <span class="priority-badge bg-<?php 
                            echo $report['priority'] === 'low' ? 'secondary' : 
                                ($report['priority'] === 'medium' ? 'primary' : 
                                ($report['priority'] === 'high' ? 'warning' : 'danger')); 
                        ?> text-white">
                            Priority: <?php echo ucfirst($report['priority']); ?>
                        </span>
                    </div>

                    <div class="info-row">
                        <div class="info-label">Category</div>
                        <div class="info-value"><?php echo htmlspecialchars($report['category_name']); ?></div>
                    </div>

                    <div class="info-row">
                        <div class="info-label">Description</div>
                        <div class="info-value"><?php echo nl2br(htmlspecialchars($report['description'])); ?></div>
                    </div>

                    <div class="info-row">
                        <div class="info-label">Location</div>
                        <div class="info-value"><?php echo htmlspecialchars($report['location_address']); ?></div>
                        <small class="text-muted">Coordinates: <?php echo $report['latitude']; ?>, <?php echo $report['longitude']; ?></small>
                    </div>

                    <div class="info-row">
                        <div class="info-label">Reported By</div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($report['username']); ?> 
                            (<?php echo htmlspecialchars($report['email']); ?>)
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-label">Submitted</div>
                        <div class="info-value"><?php echo date('F j, Y g:i A', strtotime($report['created_at'])); ?></div>
                    </div>

                    <?php if ($report['image_path']): ?>
                        <div class="info-row">
                            <div class="info-label">Photo Evidence</div>
                            <img src="../<?php echo htmlspecialchars($report['image_path']); ?>" 
                                 alt="Report Image" class="report-image">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Update Report</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select" required>
                                        <option value="pending" <?php echo $report['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="in_progress" <?php echo $report['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                        <option value="resolved" <?php echo $report['status'] === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                        <option value="rejected" <?php echo $report['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Admin Notes</label>
                                    <textarea name="admin_notes" class="form-control" rows="4"><?php echo htmlspecialchars($report['admin_notes'] ?? ''); ?></textarea>
                                </div>

                                <button type="submit" name="update_status" class="btn btn-primary w-100 mb-2">
                                    Update Report
                                </button>
                            </form>

                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this report? This cannot be undone.');">
                                <button type="submit" name="delete_report" class="btn btn-danger w-100">
                                    Delete Report
                                </button>
                            </form>
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
        const map = L.map('map').setView([42.657362, 21.156723], 15);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Add marker for report location
        const marker = L.marker([<?php echo $report['latitude']; ?>, <?php echo $report['longitude']; ?>]).addTo(map);
        marker.bindPopup("<b><?php echo htmlspecialchars($report['title']); ?></b><br><?php echo htmlspecialchars($report['location']); ?>").openPopup();
    </script>
</body>
</html>
