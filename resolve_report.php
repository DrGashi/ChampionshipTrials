<?php
session_start();
require 'db.php';
if(!isset($_SESSION['user_id'])||$_SESSION['role']!=='admin'){header("Location: dashboard.php");exit;}
if(isset($_GET['id'])){
    $id=$_GET['id'];
    $stmt=$conn->prepare("UPDATE reports SET status='resolved' WHERE id=?");
    $stmt->execute([$id]);
}
header("Location: admin.php");
exit;
?>
