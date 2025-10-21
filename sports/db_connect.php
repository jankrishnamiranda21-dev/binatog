<?php
// db_connect.php
$host = "localhost";
$user = "root";   // replace with your DB username
$pass = "";       // replace with your DB password
$db   = "sports_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
