<?php
session_start();
// Include the database connection file.
// The require_once statement makes sure we only include the file once.
require_once 'db.php'; 

// Check if the form was submitted using POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- REGISTRATION LOGIC ---
    if (isset($_POST['register'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        // Basic validation: Check if fields are empty
        if (empty($name) || empty($email) || empty($password)) {
            $_SESSION['error'] = "All fields are required.";
            header("Location: register.php");
            exit();
        }

        // Check if email already exists in the database.
        // We use prepared statements to prevent SQL Injection attacks.
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email); // 's' means string
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) > 0) {
            // Email is already taken
            $_SESSION['error'] = "Email is already registered.";
            header("Location: register.php");
            exit();
        }
        mysqli_stmt_close($stmt);

        // Hash the password for security. NEVER store plain text passwords!
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user into the database
        $stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sss", $name, $email, $hashed_password);
        
        if (mysqli_stmt_execute($stmt)) {
            // Registration successful
            $_SESSION['success'] = "Registration successful! You can now login.";
            header("Location: login.php");
            exit();
        } else {
            // Something went wrong
            $_SESSION['error'] = "Registration failed. Please try again.";
            header("Location: register.php");
            exit();
        }
        mysqli_stmt_close($stmt);
    }
    
    // --- LOGIN LOGIC ---
    if (isset($_POST['login'])) {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        if (empty($email) || empty($password)) {
            $_SESSION['error'] = "Email and password are required.";
            header("Location: login.php");
            exit();
        }

        // Search for user by email
        $stmt = mysqli_prepare($conn, "SELECT id, name, password, budget FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            // User found. Now verify if the provided password matches the hashed password
            if (password_verify($password, $row['password'])) {
                // Correct password! Create session variables to keep user logged in.
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_name'] = $row['name'];
                $_SESSION['budget'] = $row['budget'];
                
                header("Location: dashboard.php");
                exit();
            } else {
                $_SESSION['error'] = "Incorrect password.";
                header("Location: login.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "No account found with that email.";
            header("Location: login.php");
            exit();
        }
        mysqli_stmt_close($stmt);
    }
}
?>
