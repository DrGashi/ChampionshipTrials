<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

// Only allow admin to access this page
if($_SESSION['role'] !== 'admin'){
    // You can redirect users somewhere else (like map.php or profile.php)
    header("Location: map.php");
    exit;
}

require 'db.php';

// Stats queries
$totalUsers = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalLocations = $conn->query("SELECT COUNT(*) FROM locations")->fetchColumn();
$totalReports = $conn->query("SELECT COUNT(*) FROM reports WHERE status='pending'")->fetchColumn();
$users = $conn->query("SELECT id,username,role FROM users")->fetchAll();
$locations = $conn->query("SELECT name,latitude,longitude FROM locations")->fetchAll();

// Fetch reports for dashboard tab
$reports = $conn->query("SELECT r.id, r.type, r.description, r.status, r.latitude, r.longitude, r.image, r.created_at, u.username
                         FROM reports r
                         JOIN users u ON r.user_id = u.id
                         ORDER BY r.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - CityCare</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<nav>
    <a href="dashboard.php" class="active">Dashboard</a>
    <a href="map.php">Map</a>
    <a href="profile.php">Profile</a>
    <a href="logout.php">Logout</a>
</nav>

<div class="container">
<h1>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></h1>

<div class="tabs">
    <div class="tab active" onclick="showTab('stats')">Stats</div>
    <div class="tab" onclick="showTab('users')">Users</div>
    <div class="tab" onclick="showTab('locations')">Locations</div>
    <div class="tab" onclick="showTab('reports')">Reports</div>
</div>

<!-- Stats Tab -->
<div id="stats" class="tab-content">
    <div>Total Users: <?= $totalUsers ?></div>
    <div>Total Locations: <?= $totalLocations ?></div>
    <div>Pending Reports: <?= $totalReports ?></div>
</div>

<!-- Users Tab -->
<div id="users" class="tab-content" style="display:none;">
    <h3>All Users</h3>
    <ul>
        <?php foreach($users as $user): ?>
            <li><?= htmlspecialchars($user['username']) ?> (<?= htmlspecialchars($user['role']) ?>)</li>
        <?php endforeach; ?>
    </ul>
</div>

<!-- Locations Tab -->
<div id="locations" class="tab-content" style="display:none;">
    <h3>All Locations</h3>
    <ul>
        <?php foreach($locations as $loc): ?>
            <li><?= htmlspecialchars($loc['name']) ?> (<?= $loc['latitude'] ?>, <?= $loc['longitude'] ?>)</li>
        <?php endforeach; ?>
    </ul>
</div>

<!-- Reports Tab -->
<div id="reports" class="tab-content" style="display:none;">
    <h3>All Reports</h3>
    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>Description</th>
                <th>Location</th>
                <th>Image</th>
                <th>Status</th>
                <th>Reported By</th>
                <th>Time</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($reports as $rep): ?>
            <tr>
                <td><?= htmlspecialchars($rep['type']) ?></td>
                <td><?= htmlspecialchars($rep['description']) ?></td>
                <td><?= $rep['latitude'] && $rep['longitude'] ? "Lat: {$rep['latitude']}, Lng: {$rep['longitude']}" : 'N/A' ?></td>
                <td>
                    <?php if($rep['image']): ?>
                        <img src="<?= htmlspecialchars($rep['image']) ?>" style="max-width:100px; border-radius:5px;">
                    <?php else: ?>
                        N/A
                    <?php endif; ?>
                </td>
                <td style="color:<?php
                    if($rep['status']=='pending') echo 'orange';
                    elseif($rep['status']=='resolved') echo 'green';
                    elseif($rep['status']=='rejected') echo 'red';
                ?>; font-weight:bold;">
                    <?= htmlspecialchars($rep['status']) ?>
                </td>
                <td><?= htmlspecialchars($rep['username']) ?></td>
                <td><?= date('d M Y H:i', strtotime($rep['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
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
