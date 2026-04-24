<?php
ini_set('upload_max_filesize', '8M');
ini_set('post_max_size', '10M');

session_start();

$isLoggedIn = isset($_SESSION['user_id']); // Assume user_id is stored in session

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if the user is not logged in
    header('Location: login.php');
    exit();
}

// Include the database connection
$path = '../../../'; // Adjust path if necessary
require $path . 'dbConnect.inc'; // Include the database connection

// Handle form submission to create a new post
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $title = mysqli_real_escape_string($mysqli, $_POST['title']);
    $content = mysqli_real_escape_string($mysqli, $_POST['content']);

    // Handle image upload (optional)
    $imageData = NULL;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        // Get the file content and encode it as a blob
        $imageData = file_get_contents($_FILES['image']['tmp_name']);
    }

// if ($imageData === null) {
//     echo "Image upload failed or was empty.<br>";
//     if (!isset($_FILES['image'])) echo "No file uploaded.<br>";
//     else echo "Upload error code: " . $_FILES['image']['error'] . "<br>";
// }


    // Insert the new post into the database
    $query = "INSERT INTO blogs (user_id, title, content, image) VALUES ('$userId', '$title', '$content', ?)";
    $stmt = mysqli_prepare($mysqli, $query);

    // Bind the parameters for the image (which might be NULL)
    mysqli_stmt_bind_param($stmt, 'b', $imageData);

    // Execute the query
    if (mysqli_stmt_execute($stmt)) {
        // Redirect back to the blog page after successful post creation
        header('Location: blog.php');
        exit();
    } else {
        // Display an error if the query fails
        echo "Error: " . mysqli_error($mysqli);
    }
}

// Include the header from header.php (you already provided this file)
include 'header2.php';
?>
<!-- Link to styles.css to apply the styles -->
<link rel="stylesheet" href="https://solace.ist.rit.edu/~sp5442/490/alanachat/assets/css/blogstyles.css"> <!-- Ensure the correct path -->

<main>
    <section class="create-post-form">

        <div class="create-post-container">
            <h2>Create a New Post</h2>
            <form class="create-post-form" action="create_post.php" method="POST" enctype="multipart/form-data">
                <label for="post-title">Post Title</label>
                <input type="text" id="post-title" name="title" placeholder="Enter your post title" required>

                <label for="post-content">Post Content</label>
                <textarea id="post-content" name="content" rows="6" placeholder="Write your post content here..." required></textarea>

                <label for="post-image">Post Image</label>
                <input type="file" id="image" name="image" accept="image/*" />

                <div class="form-actions">
                    <button type="submit" class="submit-btn">Publish Post</button>
                    <button type="reset" class="reset-btn">Reset</button>
                </div>
            </form>
        </div>

    </section>
</main>

<?php
// Include footer from footer.html (you already provided this file)
include 'footer.html';
?>
