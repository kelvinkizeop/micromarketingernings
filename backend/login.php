<?php 
include('includes/db.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if the user exists in PostgreSQL
    $sql = "SELECT * FROM users WHERE username = $1";
    $stmt = pg_query_params($conn, $sql, array($username));  // Execute the query with parameter binding

    if ($stmt) {
        $user = pg_fetch_assoc($stmt);  // Fetch user data

        if ($user) {
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session variable for user login
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['is_admin'] = $user['is_admin'];

                // Redirect to the admin dashboard if the user is an admin
                if ($user['is_admin'] == 1) {
                    header("Location: /backend/admin.php"); // Admin page
                    exit();
                } else {
                    // Redirect to the regular user dashboard
                    header("Location: /backend/dashboard.php");
                    exit();
                }
            } else {
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var errorMessage = document.getElementById('error-message');
                        errorMessage.innerText = 'Invalid credentials.';
                        errorMessage.style.display = 'block';
                    });
                </script>";
            }
        } else {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    var errorMessage = document.getElementById('error-message');
                    errorMessage.innerText = 'No user found with that username.';
                    errorMessage.style.display = 'block';
                });
              </script>";
        }
    } else {
        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    var errorMessage = document.getElementById('error-message');
                    errorMessage.innerText = 'Error in database query.';
                    errorMessage.style.display = 'block';
                });
              </script>";
    }
}

pg_close($conn);  // Close PostgreSQL connection
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
<div class="error-message" id="error-message"></div>
<img src="images/MMELOGO.png">
   
    <!-- Login container -->
    <div class="login-container">
        <h2>Login</h2>
        <form action="/backend/login.php" method="POST">
            <!-- Username/Email Field -->
            <div class="field-text">
                <input type="text" name="username" required>
                <label for="username">Username or Email</label>
                <span></span>
            </div>

            <!-- Password Field -->
            <div class="field-text">
                <input type="password" name="password" id="password" required>
                <label for="password">Password</label>
                <span></span>
                <button type="button"  id="togglePassword" class="togglePassword" onclick="togglePassword()">👁</button>
            </div>

            <!-- Submit Button -->
            <input type="submit" value="Login">
        </form>

        <!-- Signup Link -->
        <div class="signup-link">
            Don't have an account? <a href="/backend/signup.php">Sign up</a>
        </div>
    </div>
    <script src="js/login.js"></script>
</body>
</html>
