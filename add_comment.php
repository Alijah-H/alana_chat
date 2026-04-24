<?php
session_start();

// Load database connection
$path = '../../../';
require $path . 'dbConnect.inc'; // This should define $mysqli

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_SESSION['user_id'], $_POST['blog_id'], $_POST['comment'])) {
        $user_id = $_SESSION['user_id'];
        $blog_id = intval($_POST['blog_id']);
        $comment = trim($_POST['comment']);

        if ($comment === '') {
            echo "Comment cannot be empty.";
            exit();
        }

        // Use $mysqli instead of $conn
        $insert_sql = "INSERT INTO comments (blog_id, user_id, comment) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($mysqli, $insert_sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "iis", $blog_id, $user_id, $comment);
            if (mysqli_stmt_execute($stmt)) {
                header("Location: blog.php");
                exit();
            } else {
                echo "Error inserting comment.";
            }
            mysqli_stmt_close($stmt);
        } else {
            echo "Database statement failed.";
        }
    } else {
        echo "Invalid form data.";
    }
} else {
    header("Location: blog.php");
    exit();
}
