<?php
session_start();
$path = '../../../';
require $path . 'dbConnect.inc';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get input values
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validate inputs
    if (empty($username) || empty($password)) {
        die("Username and password are required.");
    }

    // Check if username exists
    $stmt = $mysqli->prepare("SELECT id, password_hash FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        die("Invalid username or password.");
    }

    $stmt->bind_result($user_id, $password_hash);
    $stmt->fetch();

    // Verify password
    if (password_verify($password, $password_hash)) {
        // Start session and store user data
        $_SESSION["user_id"] = $user_id;
        $_SESSION["username"] = $username;

        // Redirect to home page
        header("Location: index.html");
        exit();
    } else {
        die("Invalid username or password.");
    }

    $stmt->close();
    $mysqli->close();
} else {
    die("Invalid request.");
}
?>
