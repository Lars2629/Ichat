<?php
// Include database connection
require 'db.php';
session_start();

$current_user_id = $_SESSION['user_id'] ?? null;

// Redirect if the user is not logged in
if (!$current_user_id) {
    header("Location: login.php");
    exit;
}

// Fetch the current user's data
$sql = "SELECT * FROM users WHERE user_id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $current_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $profile_picture = $_FILES['profile_picture'] ?? null;

    // Update username
    $sql = "UPDATE users SET username = ? WHERE user_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("si", $username, $current_user_id);
        $stmt->execute();
        $stmt->close();
    }

    // Handle profile picture upload
    if ($profile_picture && $profile_picture['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($profile_picture["name"]);
        move_uploaded_file($profile_picture["tmp_name"], $target_file);

        // Update profile picture path in the database
        $sql = "UPDATE users SET profile_picture = ? WHERE user_id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("si", $target_file, $current_user_id);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Redirect back to the profile page
    header("Location: user_profile.php?id=" . $current_user_id);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
</head>
<body>
    <h1>Edit Profile</h1>
    <form action="edit_profile.php" method="POST" enctype="multipart/form-data">
        <label for="username">New Username:</label>
        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
        <br>
        <label for="profile_picture">Profile Picture:</label>
        <input type="file" name="profile_picture" accept="image/*">
        <br>
        <button type="submit">Update Profile</button>
    </form>
    <a href="user_profile.php?">Back to Profile</a>
</body>
</html>
