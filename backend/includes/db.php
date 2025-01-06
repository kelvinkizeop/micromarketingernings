<?php
$host = 'localhost';       // Host
$username = 'root';        // Database username
$password = '';            // Database password
$dbname = 'sample'; // Database name (change this to your actual DB name)

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


?>
