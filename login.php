<?php
/*
  login.php
  - Attempts DB authentication if dbConnect.inc and $mysqli exist.
  - Falls back to session-backed mock users ($_SESSION['mock_users']) when DB is not available.
  - On success sets session user_id/username (for DB) or username (for mock) and redirects to index.html.
*/

session_start();

/* Path to your dbConnect.inc (adjust if needed) */
$path = '../../../';
$mysqli_available = false;

// Try to include DB connect if present
if (@file_exists($path . 'dbConnect.inc')) {
    @include_once $path . 'dbConnect.inc';
    if (isset($mysqli) && ($mysqli instanceof mysqli)) {
        $mysqli_available = true;
    }
}

/* Only accept POST */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Invalid request method.";
    exit;
}

/* Sanitize inputs */
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

if ($username === '' || $password === '') {
    echo "Username and password are required. <a href='login.html'>Back</a>";
    exit;
}

/* If DB available, try DB auth */
if ($mysqli_available) {
    $stmt = $mysqli->prepare("SELECT id, password_hash FROM users WHERE username = ?");
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 0) {
            $stmt->close();
            echo "Invalid username or password. <a href='login.html'>Back</a>";
            exit;
        }
        $stmt->bind_result($user_id, $password_hash);
        $stmt->fetch();
        $stmt->close();

        if (password_verify($password, $password_hash)) {
            // DB-authenticated user
            $_SESSION["user_id"] = $user_id;
            $_SESSION["username"] = $username;
            if (isset($mysqli) && ($mysqli instanceof mysqli)) {
                $mysqli->close();
            }
            header("Location: index.html");
            exit;
        } else {
            echo "Invalid username or password. <a href='login.html'>Back</a>";
            exit;
        }
    } else {
        // Prepare failed; fallback to session mock
        error_log("login.php: DB prepare failed: " . $mysqli->error);
        $mysqli_available = false;
    }
}

/* Session-backed mock fallback */
if (!isset($_SESSION['mock_users']) || !is_array($_SESSION['mock_users'])) {
    $_SESSION['mock_users'] = [];
}

// Check username in mock store
if (!array_key_exists($username, $_SESSION['mock_users'])) {
    echo "Invalid username or password. <a href='login.html'>Back</a>";
    exit;
}

$stored = $_SESSION['mock_users'][$username];
$stored_hash = isset($stored['password_hash']) ? $stored['password_hash'] : '';

if (password_verify($password, $stored_hash)) {
    // Mock-authenticated user: set session values similar to DB flow
    $_SESSION['username'] = $username;
    // Optionally set a mock user_id
    if (!isset($_SESSION['mock_user_ids'])) {
        $_SESSION['mock_user_ids'] = [];
    }
    if (!isset($_SESSION['mock_user_ids'][$username])) {
        $_SESSION['mock_user_ids'][$username] = time(); // simple unique-ish id
    }
    $_SESSION['user_id'] = $_SESSION['mock_user_ids'][$username];

    header("Location: index.html");
    exit;
} else {
    echo "Invalid username or password. <a href='login.html'>Back</a>";
    exit;
}
?>



<!-- <?php
// session_start();
// $path = '../../../';
// require $path . 'dbConnect.inc';

// if ($_SERVER["REQUEST_METHOD"] == "POST") {
//     // Get input values
//     $username = trim($_POST['username']);
//     $password = trim($_POST['password']);

//     // Validate inputs
//     if (empty($username) || empty($password)) {
//         die("Username and password are required.");
//     }

//     // Check if username exists
//     $stmt = $mysqli->prepare("SELECT id, password_hash FROM users WHERE username = ?");
//     $stmt->bind_param("s", $username);
//     $stmt->execute();
//     $stmt->store_result();

//     if ($stmt->num_rows == 0) {
//         die("Invalid username or password.");
//     }

//     $stmt->bind_result($user_id, $password_hash);
//     $stmt->fetch();

//     // Verify password
//     if (password_verify($password, $password_hash)) {
//         // Start session and store user data
//         $_SESSION["user_id"] = $user_id;
//         $_SESSION["username"] = $username;

//         // Redirect to home page
//         header("Location: index.html");
//         exit();
//     } else {
//         die("Invalid username or password.");
//     }

//     $stmt->close();
//     $mysqli->close();
// } else {
//     die("Invalid request.");
// }
?> -->
