<?php
// Include database connection
require 'db.php';
session_start();

// Get the logged-in user's ID from the session
$current_user_id = $_SESSION['user_id'] ?? null;

// Check if a user ID is provided in the URL (for viewing other users' saved posts)
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : $current_user_id;

// Prepare the SQL query to fetch saved posts along with the counts for likes, comments, shares, and user info
$sql = "SELECT p.*, u.username, u.user_id AS post_user_id, 
               (SELECT COUNT(*) FROM likes WHERE post_id = p.post_id) AS likes_count, 
               (SELECT COUNT(*) FROM comments WHERE post_id = p.post_id) AS comments_count, 
               (SELECT COUNT(*) FROM shares WHERE post_id = p.post_id) AS shares_count 
        FROM saved s
        JOIN posts p ON s.post_id = p.post_id
        JOIN users u ON p.user_id = u.user_id
        WHERE s.user_id = ?
        ORDER BY s.saved_at DESC";

// Prepare and execute the SQL statement
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if there are saved posts
    if ($result->num_rows > 0) {
        $posts = [];
        while ($post = $result->fetch_assoc()) {
            $posts[] = $post;
        }
        echo json_encode(['status' => 'success', 'posts' => $posts]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No saved posts found.']);
    }

    // Close the statement
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error preparing the query.']);
}

$conn->close();
?>
