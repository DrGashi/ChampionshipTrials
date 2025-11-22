<?php
session_start();
if(!isset($_SESSION['user_id'])){header("Location: login.php");exit;}
require 'db.php';
$stmt=$conn->prepare("SELECT username,role FROM users WHERE id=?");
$stmt->execute([$_SESSION['user_id']]);
$user=$stmt->fetch();
?>
<!DOCTYPE html>
<html>
<head><title>Profile - CityCare</title><link rel="stylesheet" href="style.css"></head>
<body>
<nav>
<a href="dashboard.php">Dashboard</a>
<a href="map.php">Map</a>
<a href="report.php">Report Problem</a>
<a href="profile.php">Profile</a>
<?php if($_SESSION['role']==='admin') echo '<a href="admin.php">Admin Panel</a>'; ?>
<a href="logout.php">Logout</a>
</nav>
<div class="container">
<h1>Your Profile</h1>
<div class="card">
<p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
<p><strong>Role:</strong> <?= htmlspecialchars($user['role']) ?></p>
</div>
</div>
</body>
</html>
