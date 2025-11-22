<?php
session_start();
if(!isset($_SESSION['user_id'])||$_SESSION['role']!=='admin'){header("Location: dashboard.php");exit;}
require 'db.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $name=$_POST['name'];$lat=$_POST['latitude'];$lng=$_POST['longitude'];$desc=$_POST['description'];
    $stmt=$conn->prepare("INSERT INTO locations(name,latitude,longitude,description) VALUES(?,?,?,?)");
    $stmt->execute([$name,$lat,$lng,$desc]); header("Location: admin.php"); exit;
}
$locations=$conn->query("SELECT * FROM locations")->fetchAll();
$reports=$conn->query("SELECT r.id,r.title,r.description,r.status,u.username FROM reports r JOIN users u ON r.user_id=u.id ORDER BY r.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head><title>Admin - CityCare</title><link rel="stylesheet" href="style.css"></head>
<body>
<nav><a href="dashboard.php">Dashboard</a><a href="logout.php">Logout</a></nav>
<div class="container">
<h1>Admin Panel</h1>
<h2>Add Location</h2>
<form method="post">
<input type="text" name="name" placeholder="Location Name" required>
<input type="text" name="latitude" placeholder="Latitude" required>
<input type="text" name="longitude" placeholder="Longitude" required>
<textarea name="description" placeholder="Description"></textarea>
<button type="submit">Add Location</button>
</form>

<h2>All Locations</h2>
<ul><?php foreach($locations as $loc) echo "<li>{$loc['name']} ({$loc['latitude']}, {$loc['longitude']})</li>"; ?></ul>

<h2>All Reports</h2>
<table>
<tr><th>ID</th><th>User</th><th>Title</th><th>Description</th><th>Status</th><th>Action</th></tr>
<?php foreach($reports as $r): ?>
<tr>
<td><?= $r['id'] ?></td>
<td><?= htmlspecialchars($r['username']) ?></td>
<td><?= htmlspecialchars($r['title']) ?></td>
<td><?= htmlspecialchars($r['description']) ?></td>
<td><?= $r['status'] ?></td>
<td><?php if($r['status']=='pending'): ?><a href="resolve_report.php?id=<?= $r['id'] ?>">Mark Resolved</a><?php else: ?>Resolved<?php endif; ?></td>
</tr>
<?php endforeach; ?>
</table>
</div>
</body>
</html>
