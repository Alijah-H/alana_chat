<?php
$path = '../../../';
require $path . 'dbConnect.inc';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize form inputs
    $full_name = trim($_POST['new-name']);
    $username = trim($_POST['new-username']);
    $password = trim($_POST['new-password']);

    // Validate inputs
    if (empty($full_name) || empty($username) || empty($password)) {
        die("All fields are required.");
    }

    // Check if the username already exists
    $stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        die("Username already taken. Please choose another.");
    }
    $stmt->close();

    // Hash the password before storing
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // Insert new user into database
    $stmt = $mysqli->prepare("INSERT INTO users (full_name, username, password_hash) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $full_name, $username, $password_hash);

    if ($stmt->execute()) {
        echo "Registration successful! <a href='login.html'>Go to login</a>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $mysqli->close();
} else {
    die("Invalid request.");
}
?>
