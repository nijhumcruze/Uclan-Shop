<?php
// Database configuration
// Replace these with your Vesta credentials before deploying
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "assignment2"; // Update this to your Vesta DB name if different

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
