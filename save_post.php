<?php
session_start();
include 'db.php'; // Include your DB connection file

// Check if the user is logged in and if the required data is provided
if (isset($_POST['post_id']) && isset($_POST['user_id']) && is_numeric($_POST['post_id']) && is_numeric($_POST['user_id'])) {
    $post_id = $_POST['post_id'];
    $user_id = $_POST['user_id'];

    // Check if the post is already saved by the user
    $check_query = "SELECT * FROM saved WHERE post_id = ? AND user_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $post_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // If the post is not already saved, insert it into the 'saved' table
    if ($result->num_rows === 0) {
        // Insert the post into the 'saved' table
        $insert_query = "INSERT INTO saved (post_id, saved_at, user_id) VALUES (?, NOW(), ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ii", $post_id, $user_id);

        if ($stmt->execute()) {
            // Respond with success
            echo json_encode(['success' => true]);
        } else {
            // Respond with error if the query fails
            echo json_encode(['success' => false, 'message' => 'Failed to save the post. Please try again.']);
        }
    } else {
        // Respond with an error if the post is already saved
        echo json_encode(['success' => false, 'message' => 'Post already saved.']);
    }

    $stmt->close();
    $conn->close();
} else {
    // Respond with an error if the data is invalid
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>
