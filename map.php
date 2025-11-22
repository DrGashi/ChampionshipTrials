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
    #map {
        height: 600px;
        margin-top: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    /* Popup Form Styling */
    .popup-form {
        display: flex;
        flex-direction: column;
        width: 260px;
        font-family: Arial, sans-serif;
    }

    .popup-form select,
    .popup-form input,
    .popup-form textarea,
    .popup-form button {
        margin-bottom: 8px;
        padding: 8px 10px;
        border-radius: 6px;
        border: 1px solid #ccc;
        font-size: 14px;
        box-sizing: border-box;
        transition: border-color 0.3s, box-shadow 0.3s;
    }

    .popup-form select:focus,
    .popup-form input:focus,
    .popup-form textarea:focus {
        border-color: #3498db;
        box-shadow: 0 0 5px rgba(52,152,219,0.5);
        outline: none;
    }

    .popup-form textarea {
        resize: vertical;
        min-height: 60px;
    }

    .popup-form input[type="text"] {
        font-size: 13px;
    }

    .popup-form button {
        background-color: #3498db;
        color: white;
        border: none;
        font-weight: bold;
        cursor: pointer;
        transition: background-color 0.3s, transform 0.2s;
    }

    .popup-form button:hover {
        background-color: #2980b9;
        transform: scale(1.05);
    }

    /* Image preview inside popup */
    .popup-form img {
        max-width: 100%;
        border-radius: 6px;
        margin-top: 5px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
</style>
</head>
<body>
<nav>
    <?php if($_SESSION['role'] === 'admin'){?>
        <a href="dashboard.php" class="active">Dashboard</a>
    <?php }?>
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
            <select id="type">
                <option value="Pothole">Pothole</option>
                <option value="Streetlight">Streetlight Issue</option>
                <option value="Trash Overflow">Trash Overflow</option>
                <option value="Road Blocked">Road Blocked</option>
                <option value="Other">Other</option>
            </select>
            <textarea id="description" placeholder="Describe the problem" required></textarea>
            <input type="text" id="image" placeholder="Image URL (optional)">
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
    var type = btn.parentNode.querySelector('#type').value;
    var desc = btn.parentNode.querySelector('#description').value;
    var image = btn.parentNode.querySelector('#image').value;

    if(desc.trim()===''){ alert('Description is required'); return; }

    $.post('submit_report.php', {
        title: type,
        type: type,
        description: desc,
        latitude: lat,
        longitude: lng,
        image: image
    }, function(res){
        alert(res.message);
        if(res.success){
            var popupHtml = `<b>${type}</b><br>${desc}<br>Status: pending<br>By: You`;
            if(image) popupHtml += `<br><img src="${image}" alt="Report Image">`;

            L.marker([lat, lng],{
                icon:L.icon({iconUrl:'https://cdn-icons-png.flaticon.com/512/565/565547.png', iconSize:[30,30]})
            }).addTo(map)
            .bindPopup(popupHtml);

            map.closePopup();
        }
    }, 'json');
}

</script>
</body>
</html>
