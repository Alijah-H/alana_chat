<?php
// Include the database connection file
session_start(); // Use @ to suppress the warning

$path = '../../../'; // Adjust the path if necessary
require $path . 'dbConnect.inc'; // Include the database connection file

// Fetch all blog posts from the database, ordered by created_at (newest to oldest)
$query = "SELECT b.*, u.full_name
          FROM blogs b
          JOIN users u ON b.user_id = u.id
          ORDER BY b.created_at DESC";
$result = mysqli_query($mysqli, $query); // Use $mysqli from dbConnect.inc

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']); // Assume user_id is stored in session

// Include header from header.php (you already provided this file)
include 'header2.php';

?>

<!-- Link to styles.css to apply the styles -->
<link rel="stylesheet" href="https://solace.ist.rit.edu/~sp5442/490/alanachat/assets/css/blogstyles.css"> <!-- Ensure the correct path -->

<main>
    <?php if ($isLoggedIn): ?>
            <!-- Show the "Create New Blog Post" button if the user is logged in -->
            <section class="create-new-post">
                <a href="create_post.php" class="btn-create-post">Create New Blog Post</a>
            </section>
        <?php endif; ?>
    <section class="blog-posts">
        <?php
        // Check if the query executed successfully and fetched results
        if ($result) {
            // Loop through each blog post and display it
            while ($row = mysqli_fetch_assoc($result)) {
                echo '<article class="blog-post">';
                echo '<h2>' . htmlspecialchars($row['title']) . '</h2>';
                echo '<p class="author">By ' . htmlspecialchars($row['full_name']) . '</p>';
                echo '<p class="timestamp">Published on: <span class="date">' . htmlspecialchars($row['created_at']) . '</span></p>';

                // Display the content of the post
                echo '<div class="blog-content">';
                if (!empty($row['image'])) {
                    // If an image exists, display it (as a base64 encoded string for BLOB type)
                    echo '<img src="data:image/jpeg;base64,' . base64_encode($row['image']) . '" alt="Blog Image" class="blog-image">';
                }
                echo '<p>' . nl2br(htmlspecialchars($row['content'])) . '</p>';

                ?>
                <!-- Comments Section -->
                <div class="comments-section">
                    <h3>Comments:</h3>
                    <?php
                    $comment_sql = "SELECT comments.comment, comments.created_at, users.full_name
                                    FROM comments
                                    JOIN users ON comments.user_id = users.id
                                    WHERE comments.blog_id = ?
                                    ORDER BY comments.created_at ASC";

                    if ($stmt = mysqli_prepare($mysqli, $comment_sql)) {
                        mysqli_stmt_bind_param($stmt, "i", $row['id']);
                        mysqli_stmt_execute($stmt);
                        $comment_result = mysqli_stmt_get_result($stmt);

                        if ($comment_result && mysqli_num_rows($comment_result) > 0) {
                            while ($comment = mysqli_fetch_assoc($comment_result)) {
                                echo '<div class="comment">';
                                echo '<p><strong>' . htmlspecialchars($comment['full_name']) . ':</strong> ' . htmlspecialchars($comment['comment']) . '</p>';
                                echo '<small>' . htmlspecialchars($comment['created_at']) . '</small>';
                                echo '</div>';
                            }
                        } else {
                            echo '<p>No comments yet.</p>';
                        }

                        mysqli_stmt_close($stmt);
                    } else {
                        echo '<p style="color:red;">Could not prepare comment query.</p>';
                    }
                    ?>
                </div>


                <!-- Comment Form -->
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="comment-form">
                        <form action="add_comment.php" method="POST">
                            <textarea name="comment" required placeholder="Write your comment..."></textarea>
                            <input type="hidden" name="blog_id" value="<?= $row['id'] ?>">
                            <button type="submit">Post Comment</button>
                        </form>
                    </div>
                <?php else: ?>
                    <p><a href="login.html">Login</a> to post a comment.</p>
                <?php endif; ?>

                <?php
                echo '</div>';
                echo '</article>';
            }
        } else {
            echo "<p>Error fetching data: " . mysqli_error($mysqli) . "</p>";
        }
        ?>
    </section>


</main>

<?php
// Include footer from footer.html (you already provided this file)
include 'footer.html';
?>
