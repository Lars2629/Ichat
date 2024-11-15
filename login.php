<?php
require 'vendor/autoload.php'; // Load Composer dependencies

session_start(); // Start the session

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Include database connection
include 'db.php'; // Ensure this file contains your database connection logic

// Google Client configuration
$client = new Google_Client();
$client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
$client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
$client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI']);
$client->addScope('email');
$client->addScope('profile');

// Initialize $login_error to avoid undefined variable warning
$login_error = '';

// Handle traditional login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize form data
    $email = $conn->real_escape_string(trim($_POST['email']));
    $password = $_POST['password']; // Do not escape password

    // Prepare SQL query to check user credentials
    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    // Check if the user exists
    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Store user information in session
            $_SESSION['user_id'] = $user['user_id']; // Store user ID
            $_SESSION['username'] = $user['username']; // Store username
            $_SESSION['email'] = $user['email']; // Store email
            header("Location: home.php"); // Redirect to home.php
            exit(); // Stop executing the script after the redirect
        } else {
            $login_error = "Invalid password. Please try again.";
        }
    } 
}

// Check if we have a code returned from Google
if (isset($_GET['code'])) {
    // Exchange authorization code for an access token
    try {
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

        // Check if access_token exists in the response
        if (!isset($token['access_token'])) {
            throw new Exception('Access token not found.');
        }

        $client->setAccessToken($token['access_token']);

        // Get user profile information
        $oauth2 = new Google_Service_Oauth2($client);
        $userInfo = $oauth2->userinfo->get();

        // Ensure we have user information
        if ($userInfo) {
            $email = $userInfo->email;
            $username = $userInfo->name;

            // Check if user already exists in the database
            $sql = "SELECT * FROM users WHERE email='$email'";
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                // User exists, log them in
                $user = $result->fetch_assoc();
                $user_id = $user['user_id']; // Retrieve user_id from the database
            } else {
                // User does not exist, register them
                $sql = "INSERT INTO users (username, email) VALUES ('$username', '$email')";
                if ($conn->query($sql) === TRUE) {
                    $user_id = $conn->insert_id; // Get the newly created user_id
                } else {
                    throw new Exception('Error registering user: ' . $conn->error);
                }
            }

            // Now store user information in session after saving to database
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;

            // Redirect to the home page after login
            header("Location: home.php");
            exit();
        } else {
            throw new Exception('User information not retrieved.');
        }
    } catch (Exception $e) {
        error_log('Error retrieving user information: ' . $e->getMessage());
        exit();
    }
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://kit.fontawesome.com/fa0399c701.js" crossorigin="anonymous"></script>
</head>
<body>
    <main>
       <div class="text_page">
            <div class="text_glass">
                    <h1 class="text_easy">
                        <i class="fa-solid fa-caret-right"></i>
                        Conversations 
                    </h1>
                    <h1 class="text_easy">Made Easy</h1>
                    <h1 class="text_stay">
                        Stay Connected.
                    </h1>
                    <p class="long_text">
                        Stay connected, chat and share moments with friends and family. Your conversations, your way.
                    </p>
            </div>
       </div>
       <form method="POST" action="login.php" class="login_page"> <!-- Updated action to login.php -->
                <img width="100px" height="100px" src="ichat_logo.png" alt="IChat Logo">
                <h1 class="login_title">Login</h1>

                <input name="email" id="email" class="email input_login" type="email" required placeholder="Email" autocapitalize="off">
                <input name="password" id="password" class="password input_login" type="password" required placeholder="Password">

                <div class="div_forgot">
                    <a href="forgot_password.php" class="text_hover">Forgot Password?</a>
                </div>

                <button type="submit" class="login_btn btn">Login</button>

                <a class="text_hover" href="register.php">Create New Account</a>

                <p class="or">Or Sign up with</p>

                <a href="<?php echo htmlspecialchars($client->createAuthUrl()); ?>" class="google_btn">
                    <span class="google-icon">
                        <svg viewBox="0 0 48 48" width="24" height="24" xmlns="http://www.w3.org/2000/svg">
                            <title>Google Logo</title>
                            <clipPath id="g">
                                <path d="M44.5 20H24v8.5h11.8C34.7 33.9 30.1 37 24 37c-7.2 0-13-5.8-13-13s5.8-13 13-13c3.1 0 5.9 1.1 8.1 2.9l6.4-6.4C34.6 4.1 29.6 2 24 2 11.8 2 2 11.8 2 24s9.8 22 22 22c11 0 21-8 21-22 0-1.3-.2-2.7-.5-4z"></path>
                            </clipPath>
                            <g clip-path="url(#g)" class="colors">
                                <path d="M0 37V11l17 13z" fill="#FBBC05"></path>
                                <path d="M0 11l17 13 7-6.1L48 14V0H0z" fill="#EA4335"></path>
                                <path d="M0 37l30-23 7.9 1L48 0v48H0z" fill="#34A853"></path>
                                <path d="M48 48L17 24l-4-3 35-10z" fill="#4285F4"></path>
                            </g>
                        </svg>
                    </span>
                </a>
        </form>
    </main>

    <footer>
        <?php if (!empty($login_error)) : ?>
            <div class="error"><?php echo $login_error; ?></div>
        <?php endif; ?>
    </footer>
</body>
</html>
