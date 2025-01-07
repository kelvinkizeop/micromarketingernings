<?php
include('includes/db.php'); // Assuming your db.php is modified for PostgreSQL

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $email = $_POST['email'];

    // Check if username already exists
    $sql = "SELECT * FROM users WHERE username = :username";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // Output error message for username taken
        echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            var errorMessage = document.getElementById('error-message');
            errorMessage.innerText = 'Username already taken.';
            errorMessage.style.display = 'block';
        });
        </script>";
    } else {
        // Insert new user into the database
        $sql = "INSERT INTO users (username, password, email) VALUES (:username, :password, :email)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            // Redirect to login page after successful signup
            header("Location: /backend/login.php");
            exit();
        } else {
            // Output error message for failure during insertion
            echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                var errorMessage = document.getElementById('error-message');
                errorMessage.innerText = 'Error: ' + " . json_encode($conn->errorInfo()) . ";
                errorMessage.style.display = 'block';
            });
            </script>";
        }
    }
}

$conn = null; // Close the connection after execution
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <link rel="stylesheet" href="css/signup.css">
</head>
<body>
<div class="error-message" id="error-message"></div>
<img src="images/MMELOGO.png">
    <div class="signup-container">
    <h2>Create an account</h2>
        <form action="signup.php" method="POST">
            <!-- Username Field -->
            <div class="field-text">
                <input type="text" id="username" name="username" required>
                <label for="username">Username</label>
                <span></span>
            </div>

            <!-- Email Field -->
            <div class="field-text">
                <input type="email" id="email" name="email" required>
                <label for="email">Email</label>
                <span></span>
            </div>

            <!-- Password Field -->
            <div class="field-text">
                <input type="password" id="password" name="password" required>
                <label for="password">Password</label>
                <span></span>
                <button type="button" id="togglePassword" class="togglePassword">👁</button>
            </div>

            <!-- Submit Button -->
            <input type="submit" value="Sign Up">
        </form>
        <div class="login-link">
            Already have an account? <a href="/backend/login.php">Login</a>
        </div>
    </div>
    <script src="js/signup.js"></script>
</body>
</html>
