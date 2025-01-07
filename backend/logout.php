<?php
session_start();

// Destroy the session
session_unset();  // Clear session variables
session_destroy();  // Destroy the session

// Redirect to the homepage or login page (adjust URL if necessary)
header("Location: homepage.html"); // Redirect to the homepage or login page
exit();
?>

