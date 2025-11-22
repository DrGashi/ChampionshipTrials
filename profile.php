<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

require 'db.php'; // your PDO connection

$stmt = $conn->prepare("SELECT username, role FROM users WHERE id=?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$success = '';
$error = '';

// Count reports submitted by this user
$reportStmt = $conn->prepare("SELECT COUNT(*) FROM reports WHERE user_id = ?");
$reportStmt->execute([$_SESSION['user_id']]);
$reportCount = $reportStmt->fetchColumn();

// Handle password change
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['current_password'], $_POST['new_password'], $_POST['confirm_password'])){
    
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    // Get current password hash from DB
    $stmt = $conn->prepare("SELECT password FROM users WHERE id=?");
    $stmt->execute([$_SESSION['user_id']]);
    $row = $stmt->fetch();

    if(!$row || !password_verify($current, $row['password'])){
        $error = "Current password is incorrect.";
    } elseif($new !== $confirm){
        $error = "New passwords do not match.";
    } elseif(strlen($new) < 6){
        $error = "Password must be at least 6 characters.";
    } else {
        // Update password
        $hash = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->execute([$hash, $_SESSION['user_id']]);
        $success = "Password updated successfully!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profile - CityCare</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<nav>
    <?php if($_SESSION['role'] === 'admin'){?>
        <a href="dashboard.php" class="active">Dashboard</a>
    <?php }?>
    <a href="map.php">Map</a>
    <a href="profile.php">Profile</a>
    <?php if($_SESSION['role']==='admin') echo '<a href="admin.php">Admin Panel</a>'; ?>
    <a href="logout.php">Logout</a>
</nav>

<div class="container">
    <h1>Your Profile</h1>

    <div class="card">
        <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
        <p><strong>Role:</strong> <?= htmlspecialchars($user['role']) ?></p>
        <p><strong>Reports Submitted:</strong> <?= $reportCount ?></p>
    </div>

    <div class="card">
        <h2>Change Password</h2>
        <?php if($error) echo "<p class='error'>$error</p>"; ?>
        <?php if($success) echo "<p class='success'>$success</p>"; ?>
        <form method="post">
            <label>Current Password</label>
            <input type="password" name="current_password" required>

            <label>New Password</label>
            <input type="password" name="new_password" required>

            <label>Confirm New Password</label>
            <input type="password" name="confirm_password" required>

            <button type="submit">Update Password</button>
        </form>
    </div>
</div>
</body>
</html>
