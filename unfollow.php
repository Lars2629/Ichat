<?php
// Include database connection
require 'db.php';
session_start();

// Get the logged-in user's ID from the session
$current_user_id = $_SESSION['user_id'] ?? null;

// Check if friend_id is provided and the user is logged in
if (isset($_POST['friend_id']) && $current_user_id) {
    $friend_id = intval($_POST['friend_id']); // Ensure it's an integer

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Prepare to delete from the following table
        $sqlFollowing = "DELETE FROM following WHERE following_id = ? AND followed_id = ?";
        if ($stmtFollowing = $conn->prepare($sqlFollowing)) {
            $stmtFollowing->bind_param("ii", $current_user_id, $friend_id);
            $stmtFollowing->execute();
            $stmtFollowing->close();
        }

        // Prepare to delete from the followers table (the unfollow request)
        $sqlFollowers = "DELETE FROM followers WHERE sender_id = ? AND receiver_id = ?";
        if ($stmtFollowers = $conn->prepare($sqlFollowers)) {
            $stmtFollowers->bind_param("ii", $current_user_id, $friend_id); // Changed to correctly represent the unfollow scenario
            $stmtFollowers->execute();
            $stmtFollowers->close();
        }

        // Prepare to delete from the friends table if exists
        $sqlFriends = "DELETE FROM friends WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)";
        if ($stmtFriends = $conn->prepare($sqlFriends)) {
            $stmtFriends->bind_param("iiii", $current_user_id, $friend_id, $friend_id, $current_user_id);
            $stmtFriends->execute();
            $stmtFriends->close();
        }

        // Commit the transaction
        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'You have unfollowed the user.']);
    } catch (Exception $e) {
        // Rollback the transaction on error
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => 'Failed to unfollow the user: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
?>
