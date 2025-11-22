<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

$success = '';
$error = '';

// Get current settings (you can expand this with a settings table)
$stmt = $conn->query("SELECT COUNT(*) as total_reports FROM reports");
$totalReports = $stmt->fetchColumn();

$stmt = $conn->query("SELECT COUNT(*) as total_users FROM users");
$totalUsers = $stmt->fetchColumn();

$stmt = $conn->query("SELECT COUNT(*) as total_admins FROM users WHERE is_admin = 1");
$totalAdmins = $stmt->fetchColumn();

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'clear_resolved') {
            // Delete all resolved reports older than specified days
            $days = (int)$_POST['days'];
            $stmt = $conn->prepare("DELETE FROM reports WHERE status = 'resolved' AND updated_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
            $stmt->execute([$days]);
            $deleted = $stmt->rowCount();
            $success = "Deleted {$deleted} resolved reports older than {$days} days.";
        } elseif ($_POST['action'] === 'update_profile') {
            $userId = $_SESSION['user_id'];
            $fullName = trim($_POST['full_name']);
            $email = trim($_POST['email']);
            
            if (!empty($fullName) && !empty($email)) {
                $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
                $stmt->execute([$fullName, $email, $userId]);
                $_SESSION['full_name'] = $fullName;
                $success = 'Profile updated successfully!';
            }
        } elseif ($_POST['action'] === 'change_password') {
            $userId = $_SESSION['user_id'];
            $currentPassword = $_POST['current_password'];
            $newPassword = $_POST['new_password'];
            $confirmPassword = $_POST['confirm_password'];
            
            // Verify current password
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            if (password_verify($currentPassword, $user['password'])) {
                if ($newPassword === $confirmPassword) {
                    if (strlen($newPassword) >= 6) {
                        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                        $stmt->execute([$hashedPassword, $userId]);
                        $success = 'Password changed successfully!';
                    } else {
                        $error = 'New password must be at least 6 characters!';
                    }
                } else {
                    $error = 'New passwords do not match!';
                }
            } else {
                $error = 'Current password is incorrect!';
            }
        }
    }
}

// Get current admin info
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$admin = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - CityCare Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .admin-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 15px;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            border: none;
            margin-bottom: 20px;
        }
        .stat-box {
            text-align: center;
            padding: 20px;
            border-radius: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            margin-bottom: 15px;
        }
        .stat-box h3 {
            margin: 10px 0 0 0;
            font-size: 2rem;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-white"><i class="fas fa-cog"></i> Settings</h1>
            <a href="dashboard.php" class="btn btn-light"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- System Overview -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stat-box">
                    <i class="fas fa-flag fa-2x"></i>
                    <h3><?= $totalReports ?></h3>
                    <p>Total Reports</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-box">
                    <i class="fas fa-users fa-2x"></i>
                    <h3><?= $totalUsers ?></h3>
                    <p>Total Users</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-box">
                    <i class="fas fa-user-shield fa-2x"></i>
                    <h3><?= $totalAdmins ?></h3>
                    <p>Administrators</p>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Profile Settings -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-user"></i> Profile Settings</h5>
                        <form method="POST">
                            <input type="hidden" name="action" value="update_profile">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($admin['username']) ?>" disabled>
                                <small class="text-muted">Username cannot be changed</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($admin['full_name']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($admin['email']) ?>" required>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Change Password -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-lock"></i> Change Password</h5>
                        <form method="POST">
                            <input type="hidden" name="action" value="change_password">
                            <div class="mb-3">
                                <label class="form-label">Current Password</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" name="new_password" class="form-control" required minlength="6">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" required minlength="6">
                            </div>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-key"></i> Change Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Maintenance -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-tools"></i> System Maintenance</h5>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> <strong>Warning:</strong> These actions are permanent and cannot be undone!
                        </div>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="action" value="clear_resolved">
                            <div class="row align-items-end">
                                <div class="col-md-6">
                                    <label class="form-label">Clear resolved reports older than:</label>
                                    <select name="days" class="form-select">
                                        <option value="30">30 days</option>
                                        <option value="60">60 days</option>
                                        <option value="90" selected>90 days</option>
                                        <option value="180">180 days</option>
                                        <option value="365">1 year</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete old resolved reports?')">
                                        <i class="fas fa-trash"></i> Clear Old Reports
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Information -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-info-circle"></i> System Information</h5>
                        <table class="table">
                            <tr>
                                <td><strong>PHP Version:</strong></td>
                                <td><?= phpversion() ?></td>
                            </tr>
                            <tr>
                                <td><strong>Database:</strong></td>
                                <td>MySQL with PDO</td>
                            </tr>
                            <tr>
                                <td><strong>Server Time:</strong></td>
                                <td><?= date('Y-m-d H:i:s') ?></td>
                            </tr>
                            <tr>
                                <td><strong>CityCare Version:</strong></td>
                                <td>1.0.0</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
