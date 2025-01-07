<?php
// Database connection parameters
$host = 'dpg-ctu8tqt2ng1s739c0o70-a.frankfurt-postgres.render.com';  // PostgreSQL hostname
$port = '5432';  // Default PostgreSQL port
$dbname = 'mme_database';  // Database name
$username = 'mme_database_user';  // Username
$password = '7IxEipvaBxtGRIJHVel3tLEi0hPE0L0E';  // Password

// Create connection to PostgreSQL
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$username password=$password");

// Check connection
if (!$conn) {
    die("Connection failed: " . pg_last_error());
}
?>

