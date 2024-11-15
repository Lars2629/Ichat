<?php
require 'db.php'; // Include database connection

// Get the logged-in user's ID
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($user_id > 0) {
    // Prepare the SQL to get the list of users the logged-in user is following
    $sql = "SELECT u.username FROM users u 
            JOIN following f ON f.followed_id = u.user_id 
            WHERE f.following_id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if any following records are found
        if ($result->num_rows > 0) {
            $following = [];
            while ($row = $result->fetch_assoc()) {
                $following[] = $row; // Collect the following users
            }
            echo json_encode(['status' => 'success', 'following' => $following]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No users found in the following list.']);
        }

        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare the SQL query.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid user ID.']);
}
?>
