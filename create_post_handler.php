<?php
session_start();
ini_set("log_errors", 1);
ini_set("error_log", "php-error.log");

error_reporting(E_ALL);
include 'db.php'; // Ensure this points to your database connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
        exit();
    }

    // Get the logged-in user's ID
    $user_id = $_SESSION['user_id'];
    $content = $_POST['content'] ?? '';
    $privacy = $_POST['privacy'] ?? 'public';

    $image_url = null;

    // Allowed file types and extensions for image upload
    $allowed_image_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $allowed_image_extensions = ['jpeg', 'jpg', 'png', 'gif', 'webp'];

    $upload_dir = 'uploads/';

    // Create the upload directory if it doesn't exist
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $image_tmp = $_FILES['image']['tmp_name'];
        $image_name = basename($_FILES['image']['name']);
        $image_size = $_FILES['image']['size'];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $image_type = finfo_file($finfo, $image_tmp);
        finfo_close($finfo);

        $image_extension = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

        if (in_array($image_type, $allowed_image_types) && in_array($image_extension, $allowed_image_extensions) && $image_size <= 5 * 1024 * 1024) {
            $image_url = $upload_dir . uniqid('img_', true) . '.' . $image_extension;
            move_uploaded_file($image_tmp, $image_url);
        }
    }

    // Insert post into the database
    $stmt = $conn->prepare("INSERT INTO posts (user_id, content, image_url, privacy) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $content, $image_url, $privacy);

    if ($stmt->execute()) {
        $post_id = $stmt->insert_id; // Get the new post ID

        // Handle tagged users by their names (tag_name) instead of user_id
        $tagged_names = isset($_POST['tagged_names']) ? explode(',', $_POST['tagged_names']) : [];

        // Insert tagged users into the post_tags table
        if (!empty($tagged_names)) {
            $tagStmt = $conn->prepare("INSERT INTO post_tags (post_id, tag_name, user_id) VALUES (?, ?, ?)");
            foreach ($tagged_names as $tag_name) {
                if (!empty($tag_name)) {
                    $tagStmt->bind_param("isi", $post_id, $tag_name, $user_id); // Insert tag_name
                    $tagStmt->execute();
                }
            }
            $tagStmt->close();
        }

        echo json_encode(['status' => 'success', 'message' => 'Post created successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to create post.']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
