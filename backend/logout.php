<?php
session_start();
session_destroy(); // Destroy the session
header("Location: homepage.html"); // Redirect to login page
exit();
?>
