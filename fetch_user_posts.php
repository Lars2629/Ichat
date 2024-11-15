<?php
// Include database connection
require 'db.php';
session_start();

// Check if user_id is provided
if (isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']); // Convert to integer to ensure it's a valid ID

    // Prepare the SQL statement to fetch posts
    $sql = "SELECT post_id, user_id, content, image_url, video_url, created_at, 
                   likes_count, comments_count, shares_count 
            FROM posts 
            WHERE user_id = ? 
            ORDER BY created_at DESC"; // Order by most recent first

    if ($stmt = $conn->prepare($sql)) {
        // Bind the user_id parameter
        $stmt->bind_param("i", $user_id);

        // Execute the statement
        $stmt->execute();
        $result = $stmt->get_result();

        // Prepare an array to hold the posts
        $posts = [];
        
        while ($post = $result->fetch_assoc()) {
            $posts[] = $post;
        }

        // Return posts as JSON
        echo json_encode(['status' => 'success', 'posts' => $posts]);
        
        // Close the statement
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error preparing statement.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'User ID not provided.']);
}
?>
