<?php
session_start();
if(!isset($_SESSION['user_id'])){header("Location: login.php");exit;}
require 'db.php';
$totalUsers=$conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalLocations=$conn->query("SELECT COUNT(*) FROM locations")->fetchColumn();
$totalReports=$conn->query("SELECT COUNT(*) FROM reports WHERE status='pending'")->fetchColumn();
$users=$conn->query("SELECT id,username,role FROM users")->fetchAll();
$locations=$conn->query("SELECT name,latitude,longitude FROM locations")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head><title>Dashboard - CityCare</title><link rel="stylesheet" href="style.css"></head>
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
<h1>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></h1>
<div class="tabs">
<div class="tab active" onclick="showTab('stats')">Stats</div>
<div class="tab" onclick="showTab('users')">Users</div>
<div class="tab" onclick="showTab('locations')">Locations</div>
</div>
<div id="stats" class="tab-content">
<div class="card">Total Users: <?= $totalUsers ?></div>
<div class="card">Total Locations: <?= $totalLocations ?></div>
<div class="card">Pending Reports: <?= $totalReports ?></div>
</div>
<div id="users" class="tab-content" style="display:none;">
<div class="card">
<h3>All Users</h3>
<ul><?php foreach($users as $user) echo "<li>{$user['username']} ({$user['role']})</li>"; ?></ul>
</div>
</div>
<div id="locations" class="tab-content" style="display:none;">
<div class="card">
<h3>All Locations</h3>
<ul><?php foreach($locations as $loc) echo "<li>{$loc['name']} ({$loc['latitude']}, {$loc['longitude']})</li>"; ?></ul>
</div>
</div>
</div>
<script>
function showTab(tabName){
document.querySelectorAll('.tab-content').forEach(tc=>tc.style.display='none');
document.querySelectorAll('.tab').forEach(t=>t.classList.remove('active'));
document.getElementById(tabName).style.display='block';
event.target.classList.add('active');
}
</script>
</body>
</html>
