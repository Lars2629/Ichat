<?php
// Start the session
session_start();

// Include the database connection file
include 'db.php';

// Initialize error message variables
$email_error = '';
$message = '';

// Initialize input fields
$first_name = '';
$last_name = '';
$email = '';

// Function to register a user
function registerUser($first_name, $last_name, $email, $password) {
    global $conn;

    // Generate a username
    $username = $first_name . ' ' . $last_name; // Combine first and last name

    // Validate and sanitize input
    $first_name = $conn->real_escape_string($first_name);
    $last_name = $conn->real_escape_string($last_name);
    $email = $conn->real_escape_string($email);
    $password = password_hash($password, PASSWORD_DEFAULT); // Hash the password

    // Check if the email already exists
    $email_check_query = "SELECT * FROM users WHERE email='$email' LIMIT 1";
    $result = $conn->query($email_check_query);
    
    if ($result && $result->num_rows > 0) {
        return "Email already exists. Please use a different email.";
    }

    // Prepare SQL query to insert user data
    $sql = "INSERT INTO users (first_name, last_name, email, password, username, created_at)
            VALUES ('$first_name', '$last_name', '$email', '$password', '$username', CURRENT_TIMESTAMP)";

    if ($conn->query($sql) === TRUE) {
        return true;
    } else {
        return "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $message = "Passwords do not match!";
    } else {
        $is_registered = registerUser($first_name, $last_name, $email, $password);

        if ($is_registered === true) {
            // Registration successful, redirect to login page
            header("Location: login.php");
            exit();
        } else {
            // Store the error message in the session
            $_SESSION['email_error'] = $is_registered;
            // Retain input values in case of an error
            $first_name = htmlspecialchars($first_name);
            $last_name = htmlspecialchars($last_name);
            $email = htmlspecialchars($email);
        }
    }
}

// Check for error message in session
if (isset($_SESSION['email_error'])) {
    $email_error = $_SESSION['email_error'];
    unset($_SESSION['email_error']); // Clear the error message after displaying
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <link rel="stylesheet" href="style.css">
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
                <h1 class="text_stay">Stay Connected.</h1>
                <p class="long_text">
                    Stay connected, chat, and share moments with friends and family. Your conversations, your way.
                </p>
            </div>
       </div>

       <form method="POST" action="register.php" class="login_page">
            <h1 class="login_title">Create your iChat account</h1>

            <input name="first_name" id="first_name" class="first_name input_login" type="text" required placeholder="First Name" value="<?php echo htmlspecialchars($first_name); ?>">
            
            <input name="last_name" id="last_name" class="last_name input_login" type="text" required placeholder="Last Name" value="<?php echo htmlspecialchars($last_name); ?>">

            <input name="email" id="email" class="email input_login" type="email" required placeholder="Email" autocapitalize="off" value="<?php echo htmlspecialchars($email); ?>">
            <?php if ($email_error): ?>
                <p style="color:red;"><?php echo htmlspecialchars($email_error); ?></p>
            <?php endif; ?>

            <input name="password" id="password" class="password input_login" type="password" required placeholder="Password">

            <input name="confirm_password" id="confirm_password" class="confirm_password input_login" type="password" required placeholder="Confirm Password">

            <button type="submit" class="create_btn btn">Create Account</button>
            
            <div class="sign_in">
                <p class="have_acc">Have an account?</p>
                <a class="text_hover" href="login.php">Sign in</a>
            </div>
       </form>

       <?php if ($message): ?>
           <p style="color:red;"><?php echo htmlspecialchars($message); ?></p>
       <?php endif; ?>
    </main>
</body>
</html>
