<?php
/*
  signup.php
  - Uses DB if available (dbConnect.inc sets $mysqli).
  - Falls back to a session-backed mock store when DB is not available.
  - On success redirects to signup-success.html?name=... (keeps your existing success page).
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

/* Accept only POST */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Invalid request method.";
    exit;
}

/* Sanitize inputs */
$full_name = isset($_POST['new-name']) ? trim($_POST['new-name']) : '';
$username  = isset($_POST['new-username']) ? trim($_POST['new-username']) : '';
$password  = isset($_POST['new-password']) ? trim($_POST['new-password']) : '';

if ($full_name === '' || $username === '' || $password === '') {
    echo "All fields are required. <a href='login.html'>Back</a>";
    exit;
}

/* Helper: redirect target (keeps your existing success page) */
function success_redirect($displayName) {
    // Prefer signup-success.php if you created it; otherwise use signup-success.html
    if (file_exists(__DIR__ . '/signup-success.php')) {
        return 'signup-success.php';
    }
    return 'signup-success.html?name=' . rawurlencode($displayName);
}

/* If DB is available, attempt to use it */
if ($mysqli_available) {
    // Check if username exists
    $stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->close();
            echo "Username already taken. Please choose another. <a href='login.html'>Back</a>";
            exit;
        }
        $stmt->close();
    } else {
        // Prepare failed; fallback to session mock
        error_log("signup.php: DB prepare failed: " . $mysqli->error);
        $mysqli_available = false;
    }
}

if ($mysqli_available) {
    // Insert user into DB
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $mysqli->prepare("INSERT INTO users (full_name, username, password_hash) VALUES (?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("sss", $full_name, $username, $password_hash);
        if ($stmt->execute()) {
            // Optionally set session value for success page
            $_SESSION['last_registered_name'] = $full_name;
            $stmt->close();
            if (isset($mysqli) && ($mysqli instanceof mysqli)) {
                $mysqli->close();
            }
            header("Location: " . success_redirect($full_name));
            exit;
        } else {
            $err = $stmt->error;
            $stmt->close();
            echo "Error: " . htmlspecialchars($err, ENT_QUOTES, 'UTF-8') . " <a href='login.html'>Back</a>";
            exit;
        }
    } else {
        // Insert prepare failed; fallback to session mock
        error_log("signup.php: DB insert prepare failed: " . $mysqli->error);
        $mysqli_available = false;
    }
}

/* Session-backed mock fallback (no DB) */
if (!isset($_SESSION['mock_users']) || !is_array($_SESSION['mock_users'])) {
    $_SESSION['mock_users'] = [];
}

// Duplicate username check in session store
if (array_key_exists($username, $_SESSION['mock_users'])) {
    echo "Username already taken in this session. <a href='login.html'>Try another</a>";
    exit;
}

// Hash and store
$password_hash = password_hash($password, PASSWORD_BCRYPT);
$_SESSION['mock_users'][$username] = [
    'full_name' => $full_name,
    'password_hash' => $password_hash,
    'created_at' => date('c')
];

// Save last registered name for success page
$_SESSION['last_registered_name'] = $full_name;

// Redirect to success page
header("Location: " . success_redirect($full_name));
exit;
?>


<!-- UPDATED PHP W/ DB CONNECTION -->
<!-- <?php
// $path = '../../../';
// require $path . 'dbConnect.inc';

// if ($_SERVER["REQUEST_METHOD"] == "POST") {
//     // Get and sanitize form inputs
//     $full_name = trim($_POST['new-name']);
//     $username = trim($_POST['new-username']);
//     $password = trim($_POST['new-password']);

//     // Validate inputs
//     if (empty($full_name) || empty($username) || empty($password)) {
//         die("All fields are required.");
//     }

//     // Check if the username already exists
//     $stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
//     $stmt->bind_param("s", $username);
//     $stmt->execute();
//     $stmt->store_result();

//     if ($stmt->num_rows > 0) {
//         die("Username already taken. Please choose another.");
//     }
//     $stmt->close();

//     // Hash the password before storing
//     $password_hash = password_hash($password, PASSWORD_BCRYPT);

//     // Insert new user into database
//     $stmt = $mysqli->prepare("INSERT INTO users (full_name, username, password_hash) VALUES (?, ?, ?)");
//     $stmt->bind_param("sss", $full_name, $username, $password_hash);

//     if ($stmt->execute()) {
//         // Redirect to a friendly success page with the user's display name (URL-encoded)
//         $displayName = urlencode($full_name);
//         header("Location: /signup-success.html?name={$displayName}");
//         exit;
//     } else {
//         echo "Error: " . $stmt->error;
//     }

//     $stmt->close();
//     $mysqli->close();
// } else {
//     die("Invalid request.");
// }
?> -->



<!-- OG VERSION -->
<!-- <?php
// $path = '../../../';
// require $path . 'dbConnect.inc';

// if ($_SERVER["REQUEST_METHOD"] == "POST") {
//     // Get and sanitize form inputs
//     $full_name = trim($_POST['new-name']);
//     $username = trim($_POST['new-username']);
//     $password = trim($_POST['new-password']);

//     // Validate inputs
//     if (empty($full_name) || empty($username) || empty($password)) {
//         die("All fields are required.");
//     }

//     // Check if the username already exists
//     $stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
//     $stmt->bind_param("s", $username);
//     $stmt->execute();
//     $stmt->store_result();

//     if ($stmt->num_rows > 0) {
//         die("Username already taken. Please choose another.");
//     }
//     $stmt->close();

//     // Hash the password before storing
//     $password_hash = password_hash($password, PASSWORD_BCRYPT);

//     // Insert new user into database
//     $stmt = $mysqli->prepare("INSERT INTO users (full_name, username, password_hash) VALUES (?, ?, ?)");
//     $stmt->bind_param("sss", $full_name, $username, $password_hash);

//     if ($stmt->execute()) {
//         echo "Registration successful! <a href='login.html'>Go to login</a>";
//     } else {
//         echo "Error: " . $stmt->error;
//     }

//     $stmt->close();
//     $mysqli->close();
// } else {
//     die("Invalid request.");
// }
?> -->
