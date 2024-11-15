<?php
session_start();
require 'db.php';

$response = ['status' => 'error', 'message' => 'Invalid request'];

if (isset($_POST['post_id']) && isset($_SESSION['user_id'])) {
    $post_id = $_POST['post_id'];
    $user_id = $_SESSION['user_id'];

    // Check if the user has already liked the post
    $check_like = $conn->query("SELECT * FROM likes WHERE post_id = $post_id AND user_id = $user_id");
    
    if ($check_like->num_rows > 0) {
        // User already liked the post; remove the like
        $conn->query("DELETE FROM likes WHERE post_id = $post_id AND user_id = $user_id");
        $response['status'] = 'success';
        $response['liked'] = false; // Not liked anymore
    } else {
        // User has not liked the post; add a like
        $conn->query("INSERT INTO likes (post_id, user_id) VALUES ($post_id, $user_id)");
        $response['status'] = 'success';
        $response['liked'] = true; // Now liked
    }

    // Fetch the updated like count
    $like_count_result = $conn->query("SELECT COUNT(*) as like_count FROM likes WHERE post_id = $post_id");
    $like_count = $like_count_result->fetch_assoc()['like_count'];
    $response['like_count'] = $like_count;
}

echo json_encode($response);
?>
