<?php
session_start();
require_once 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to upload a story.");
}

$user_id = $_SESSION['user_id']; // Get the logged-in user ID

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['story'])) {
    $text_caption = $_POST['text_caption'] ?? '';
    $visibility = $_POST['visibility'] ?? 'friends';
    
    // File upload path
    $target_dir = "uploads/stories/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_name = basename($_FILES["story"]["name"]);
    $target_file = $target_dir . $file_name;
    
    // Check if file is a valid image or video
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    if (in_array($file_type, ['jpg', 'jpeg', 'png', 'gif', 'mp4'])) {
        if (move_uploaded_file($_FILES["story"]["tmp_name"], $target_file)) {
            $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));
            
            // Insert story into the database
            $stmt = $conn->prepare("INSERT INTO stories (user_id, content_url, expires_at, visibility, text_caption) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $user_id, $target_file, $expires_at, $visibility, $text_caption);
            
            if ($stmt->execute()) {
                echo "Story uploaded successfully!";
            } else {
                echo "Error: " . $stmt->error;
            }
            
            $stmt->close();
        } else {
            echo "Error uploading file.";
        }
    } else {
        echo "Invalid file type. Only images and videos are allowed.";
    }
} else {
    echo "No file or user data provided.";
}

$conn->close();
?>