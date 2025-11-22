<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();
requireAdmin();

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$priority_filter = isset($_GET['priority']) ? $_GET['priority'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Build query
$sql = "SELECT r.*, c.name as category_name, u.username, u.email 
        FROM reports r 
        LEFT JOIN categories c ON r.category_id = c.id 
        LEFT JOIN users u ON r.user_id = u.id 
        WHERE 1=1";

$params = [];

if ($status_filter) {
    $sql .= " AND r.status = ?";
    $params[] = $status_filter;
}

if ($category_filter) {
    $sql .= " AND r.category_id = ?";
    $params[] = $category_filter;
}

if ($priority_filter) {
    $sql .= " AND r.priority = ?";
    $params[] = $priority_filter;
}

if ($search) {
    $sql .= " AND (r.title LIKE ? OR r.description LIKE ? OR u.username LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$sql .= " ORDER BY r.$sort $order";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$reports = $stmt->fetchAll();

// Get categories for filter dropdown
$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// Get statistics
$stats = [
    'total' => $conn->query("SELECT COUNT(*) FROM reports")->fetchColumn(),
    'pending' => $conn->query("SELECT COUNT(*) FROM reports WHERE status = 'pending'")->fetchColumn(),
    'in_progress' => $conn->query("SELECT COUNT(*) FROM reports WHERE status = 'in_progress'")->fetchColumn(),
    'resolved' => $conn->query("SELECT COUNT(*) FROM reports WHERE status = 'resolved'")->fetchColumn(),
    'rejected' => $conn->query("SELECT COUNT(*) FROM reports WHERE status = 'rejected'")->fetchColumn(),
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Reports - CityCare Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --info-color: #3498db;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }
        
        .main-content {
            padding: 30px 0;
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin: 0;
        }
        
        .stat-label {
            color: #7f8c8d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .filter-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .reports-table {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            border-bottom: 2px solid #dee2e6;
            color: var(--primary-color);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            cursor: pointer;
            user-select: none;
        }
        
        .table thead th:hover {
            background-color: #f8f9fa;
        }
        
        .badge {
            padding: 0.5em 0.8em;
            font-weight: 500;
        }
        
        .badge-pending {
            background-color: #f39c12;
        }
        
        .badge-in_progress {
            background-color: #3498db;
        }
        
        .badge-resolved {
            background-color: #27ae60;
        }
        
        .badge-rejected {
            background-color: #e74c3c;
        }
        
        .priority-urgent {
            color: #e74c3c;
            font-weight: bold;
        }
        
        .priority-high {
            color: #f39c12;
            font-weight: bold;
        }
        
        .priority-medium {
            color: #3498db;
        }
        
        .priority-low {
            color: #95a5a6;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }
        
        .sort-icon {
            font-size: 0.7rem;
            margin-left: 5px;
        }
        
        .action-btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #7f8c8d;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.3;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">
                <i class="bi bi-shield-check"></i> CityCare Admin
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="reports.php">
                            <i class="bi bi-file-text"></i> All Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">
                            <i class="bi bi-people"></i> Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="categories.php">
                            <i class="bi bi-tags"></i> Categories
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="../logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <div class="container">
            <h2 class="text-white mb-4">
                <i class="bi bi-file-text-fill"></i> All Reports Management
            </h2>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-2">
                    <div class="stats-card text-center">
                        <p class="stat-number text-primary"><?php echo $stats['total']; ?></p>
                        <p class="stat-label">Total</p>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="stats-card text-center">
                        <p class="stat-number" style="color: #f39c12;"><?php echo $stats['pending']; ?></p>
                        <p class="stat-label">Pending</p>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="stats-card text-center">
                        <p class="stat-number" style="color: #3498db;"><?php echo $stats['in_progress']; ?></p>
                        <p class="stat-label">In Progress</p>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="stats-card text-center">
                        <p class="stat-number" style="color: #27ae60;"><?php echo $stats['resolved']; ?></p>
                        <p class="stat-label">Resolved</p>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="stats-card text-center">
                        <p class="stat-number" style="color: #e74c3c;"><?php echo $stats['rejected']; ?></p>
                        <p class="stat-label">Rejected</p>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filter-card">
                <form method="GET" action="" id="filterForm">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Search</label>
                            <input type="text" name="search" class="form-control" placeholder="Title, description, user..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="in_progress" <?php echo $status_filter === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="resolved" <?php echo $status_filter === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-select">
                                <option value="">All Priorities</option>
                                <option value="low" <?php echo $priority_filter === 'low' ? 'selected' : ''; ?>>Low</option>
                                <option value="medium" <?php echo $priority_filter === 'medium' ? 'selected' : ''; ?>>Medium</option>
                                <option value="high" <?php echo $priority_filter === 'high' ? 'selected' : ''; ?>>High</option>
                                <option value="urgent" <?php echo $priority_filter === 'urgent' ? 'selected' : ''; ?>>Urgent</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-funnel"></i> Apply Filters
                            </button>
                            <a href="reports.php" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Clear
                            </a>
                        </div>
                    </div>
                    <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
                    <input type="hidden" name="order" value="<?php echo htmlspecialchars($order); ?>">
                </form>
            </div>

            <!-- Reports Table -->
            <div class="reports-table">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul"></i> Reports List (<?php echo count($reports); ?> results)
                    </h5>
                    <a href="dashboard.php" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back to Dashboard
                    </a>
                </div>

                <?php if (count($reports) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th onclick="sortTable('id')">
                                    ID 
                                    <?php if ($sort === 'id'): ?>
                                        <i class="bi bi-caret-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>-fill sort-icon"></i>
                                    <?php endif; ?>
                                </th>
                                <th onclick="sortTable('title')">
                                    Title
                                    <?php if ($sort === 'title'): ?>
                                        <i class="bi bi-caret-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>-fill sort-icon"></i>
                                    <?php endif; ?>
                                </th>
                                <th>Category</th>
                                <th onclick="sortTable('status')">
                                    Status
                                    <?php if ($sort === 'status'): ?>
                                        <i class="bi bi-caret-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>-fill sort-icon"></i>
                                    <?php endif; ?>
                                </th>
                                <th onclick="sortTable('priority')">
                                    Priority
                                    <?php if ($sort === 'priority'): ?>
                                        <i class="bi bi-caret-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>-fill sort-icon"></i>
                                    <?php endif; ?>
                                </th>
                                <th>Reporter</th>
                                <th onclick="sortTable('created_at')">
                                    Created
                                    <?php if ($sort === 'created_at'): ?>
                                        <i class="bi bi-caret-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>-fill sort-icon"></i>
                                    <?php endif; ?>
                                </th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reports as $report): ?>
                            <tr>
                                <td><strong>#<?php echo $report['id']; ?></strong></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($report['title']); ?></strong>
                                    <?php if ($report['image_path']): ?>
                                        <i class="bi bi-image text-primary" title="Has photo"></i>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?php echo htmlspecialchars($report['category_name']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $report['status']; ?>">
                                        <?php echo ucwords(str_replace('_', ' ', $report['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="priority-<?php echo $report['priority']; ?>">
                                        <i class="bi bi-flag-fill"></i>
                                        <?php echo ucfirst($report['priority']); ?>
                                    </span>
                                </td>
                                <td>
                                    <small>
                                        <i class="bi bi-person"></i>
                                        <?php echo htmlspecialchars($report['username']); ?>
                                    </small>
                                </td>
                                <td>
                                    <small><?php echo date('M j, Y', strtotime($report['created_at'])); ?></small>
                                </td>
                                <td>
                                    <a href="view_report.php?id=<?php echo $report['id']; ?>" class="btn btn-sm btn-primary action-btn">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <h4>No Reports Found</h4>
                    <p>Try adjusting your filters or search criteria.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function sortTable(column) {
            const form = document.getElementById('filterForm');
            const sortInput = form.querySelector('input[name="sort"]');
            const orderInput = form.querySelector('input[name="order"]');
            
            if (sortInput.value === column) {
                // Toggle order
                orderInput.value = orderInput.value === 'ASC' ? 'DESC' : 'ASC';
            } else {
                // New column, default to DESC
                sortInput.value = column;
                orderInput.value = 'DESC';
            }
            
            form.submit();
        }
    </script>
</body>
</html>
