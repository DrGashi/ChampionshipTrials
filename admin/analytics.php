<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

// Get statistics
$stats = [];

// Total reports by status
$stmt = $conn->query("SELECT status, COUNT(*) as count FROM reports GROUP BY status");
$stats['by_status'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Total reports by category
$stmt = $conn->query("SELECT c.name, COUNT(r.id) as count 
                      FROM categories c 
                      LEFT JOIN reports r ON c.id = r.category_id 
                      GROUP BY c.id 
                      ORDER BY count DESC");
$stats['by_category'] = $stmt->fetchAll();

// Total reports by priority
$stmt = $conn->query("SELECT priority, COUNT(*) as count FROM reports GROUP BY priority");
$stats['by_priority'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Reports over time (last 7 days)
$stmt = $conn->query("SELECT DATE(created_at) as date, COUNT(*) as count 
                      FROM reports 
                      WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
                      GROUP BY DATE(created_at) 
                      ORDER BY date");
$stats['over_time'] = $stmt->fetchAll();

// Top reporters
$stmt = $conn->query("SELECT u.username, u.full_name, COUNT(r.id) as report_count 
                      FROM users u 
                      LEFT JOIN reports r ON u.id = r.user_id 
                      GROUP BY u.id 
                      ORDER BY report_count DESC 
                      LIMIT 10");
$stats['top_reporters'] = $stmt->fetchAll();

// Average resolution time
$stmt = $conn->query("SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_hours 
                      FROM reports 
                      WHERE status = 'resolved'");
$avgResolutionTime = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistics - CityCare Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .admin-container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 15px;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            border: none;
            margin-bottom: 20px;
        }
        .stat-card {
            text-align: center;
            padding: 20px;
        }
        .stat-card h3 {
            font-size: 2.5rem;
            font-weight: bold;
            margin: 10px 0;
        }
        .chart-container {
            position: relative;
            height: 300px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-white"><i class="fas fa-chart-bar"></i> Statistics & Analytics</h1>
            <a href="dashboard.php" class="btn btn-light"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>

        <!-- Status Overview -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stat-card bg-warning text-white">
                    <i class="fas fa-clock fa-2x"></i>
                    <h3><?= $stats['by_status']['pending'] ?? 0 ?></h3>
                    <p>Pending</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-info text-white">
                    <i class="fas fa-spinner fa-2x"></i>
                    <h3><?= $stats['by_status']['in_progress'] ?? 0 ?></h3>
                    <p>In Progress</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-success text-white">
                    <i class="fas fa-check-circle fa-2x"></i>
                    <h3><?= $stats['by_status']['resolved'] ?? 0 ?></h3>
                    <p>Resolved</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-danger text-white">
                    <i class="fas fa-times-circle fa-2x"></i>
                    <h3><?= $stats['by_status']['rejected'] ?? 0 ?></h3>
                    <p>Rejected</p>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Reports by Category</h5>
                        <div class="chart-container">
                            <canvas id="categoryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Reports by Priority</h5>
                        <div class="chart-container">
                            <canvas id="priorityChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Reports Over Time (Last 7 Days)</h5>
                        <div class="chart-container">
                            <canvas id="timeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Reporters -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Top Reporters</h5>
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Reports</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats['top_reporters'] as $reporter): ?>
                                <tr>
                                    <td><?= htmlspecialchars($reporter['full_name']) ?> (@<?= htmlspecialchars($reporter['username']) ?>)</td>
                                    <td><span class="badge bg-primary"><?= $reporter['report_count'] ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Performance Metrics</h5>
                        <div class="p-4">
                            <div class="mb-4">
                                <h6>Average Resolution Time</h6>
                                <h3 class="text-primary">
                                    <?= $avgResolutionTime ? round($avgResolutionTime, 1) . ' hours' : 'N/A' ?>
                                </h3>
                            </div>
                            <div class="mb-4">
                                <h6>Total Reports</h6>
                                <h3 class="text-success">
                                    <?= array_sum($stats['by_status']) ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Category Chart
        const categoryData = <?= json_encode($stats['by_category']) ?>;
        new Chart(document.getElementById('categoryChart'), {
            type: 'doughnut',
            data: {
                labels: categoryData.map(c => c.name),
                datasets: [{
                    data: categoryData.map(c => c.count),
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Priority Chart
        const priorityData = <?= json_encode($stats['by_priority']) ?>;
        new Chart(document.getElementById('priorityChart'), {
            type: 'bar',
            data: {
                labels: Object.keys(priorityData),
                datasets: [{
                    label: 'Reports',
                    data: Object.values(priorityData),
                    backgroundColor: ['#28a745', '#ffc107', '#fd7e14', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Time Chart
        const timeData = <?= json_encode($stats['over_time']) ?>;
        new Chart(document.getElementById('timeChart'), {
            type: 'line',
            data: {
                labels: timeData.map(t => t.date),
                datasets: [{
                    label: 'Reports',
                    data: timeData.map(t => t.count),
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
