<?php
session_start();
if(!isset($_SESSION['user_id'])){header("Location: login.php");exit;}
require 'db.php';

$reports = $conn->query("SELECT r.id,r.title,r.description,r.status,r.latitude,r.longitude,u.username 
FROM reports r 
JOIN users u ON r.user_id=u.id 
WHERE r.latitude IS NOT NULL AND r.longitude IS NOT NULL")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>CityCare Map</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        #map { height: 600px; margin-top:20px; border-radius:10px; }

        /* Popup form styling */
        .popup-form {
            display: flex;
            flex-direction: column;
            width: 250px;
        }
        .popup-form input, .popup-form textarea, .popup-form button {
            margin-bottom: 8px;
            padding: 6px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 14px;
            width: 100%;
            box-sizing: border-box;
        }
        .popup-form textarea { resize: vertical; min-height: 50px; }
        .popup-form button {
            background-color: #3498db;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }
        .popup-form button:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
<nav>
    <a href="dashboard.php">Dashboard</a>
    <a href="map.php">Map</a>
    <a href="profile.php">Profile</a>
    <?php if ($_SESSION['role'] === 'admin') echo '<a href="admin.php">Admin Panel</a>'; ?>
    <a href="logout.php">Logout</a>
</nav>

<div class="container">
    <h1>CityCare Map</h1>
    <p>Click on the map to report a new problem.</p>
    <div id="map"></div>
</div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
var map = L.map('map').setView([42.6629, 21.1655], 13); // Pristina

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{
    attribution:'© OpenStreetMap contributors'
}).addTo(map);

// Show existing reports
var reports = <?= json_encode($reports) ?>;
reports.forEach(function(r){
    L.marker([r.latitude, r.longitude],{
        icon:L.icon({iconUrl:'https://cdn-icons-png.flaticon.com/512/565/565547.png', iconSize:[30,30]})
    }).addTo(map)
    .bindPopup('<b>'+r.title+'</b><br>'+r.description+'<br>Status: '+r.status+'<br>By: '+r.username);
});

// Click to report new problem
map.on('click', function(e){
    var lat = e.latlng.lat.toFixed(6);
    var lng = e.latlng.lng.toFixed(6);

    var popupContent = `
        <div class="popup-form">
            <input type="text" id="title" placeholder="Problem Title" required>
            <textarea id="description" placeholder="Describe the problem" required></textarea>
            <button onclick="submitReport(${lat}, ${lng}, this)">Submit</button>
        </div>
    `;

    L.popup()
        .setLatLng(e.latlng)
        .setContent(popupContent)
        .openOn(map);
});

// AJAX submission
function submitReport(lat, lng, btn){
    var title = btn.parentNode.querySelector('#title').value;
    var desc = btn.parentNode.querySelector('#description').value;
    if(title.trim()==='' || desc.trim()===''){ alert('Fill all fields'); return; }

    $.post('submit_report.php', {title:title, description:desc, latitude:lat, longitude:lng}, function(res){
        alert(res.message);
        if(res.success){
            // Add marker immediately
            L.marker([lat, lng],{
                icon:L.icon({iconUrl:'https://cdn-icons-png.flaticon.com/512/565/565547.png', iconSize:[30,30]})
            }).addTo(map)
            .bindPopup('<b>'+title+'</b><br>'+desc+'<br>Status: pending<br>By: You');
            map.closePopup();
        }
    }, 'json');
}
</script>
</body>
</html>
