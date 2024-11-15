<?php
require 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = intval($_POST['user_id']);
    $profile_picture = $_FILES['profile_picture'];

    // Validate and process the uploaded file
    if ($profile_picture['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($profile_picture['name']);
        
        // Move the uploaded file
        if (move_uploaded_file($profile_picture['tmp_name'], $target_file)) {
            // Update the database
            $sql = "UPDATE users SET profile_picture = ? WHERE user_id = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("si", $target_file, $user_id);
                $stmt->execute();
                $stmt->close();
                header("Location: user_profile.php?id=" . $user_id); // Redirect to profile page
            } else {
                echo "Error updating profile picture.";
            }
        } else {
            echo "Error uploading file.";
        }
    }
}
?>