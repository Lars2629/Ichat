<?php
// Include database connection
require 'db.php';
session_start();

// Get the current user's ID from the session
$current_user_id = $_SESSION['user_id'] ?? null;

// Check if the user is logged in
if ($current_user_id === null) {
    echo "<p>You must be logged in to perform this action.</p>";
    echo "<a href='login.php'>Login</a>";
    exit();
}

// Get the request_id and action from POST
$request_id = $_POST['request_id'] ?? null;
$action = $_POST['action'] ?? null;

// Validate input
if ($request_id === null || $action === null) {
    echo "<p>Invalid request.</p>";
    exit();
}

// Prepare SQL statements based on the action
if ($action === 'accept') {
    // Accept the request
    $sqlUpdate = "UPDATE followers SET status = 'accepted' WHERE request_id = ?";
    $sqlInsert = "INSERT INTO following (following_id, followed_id, created_at) VALUES (?, ?, NOW())";
    $sqlInsertFriend = "INSERT INTO friends (user_id, friend_id, status, created_at) VALUES (?, ?, 'accepted', NOW())";

    if ($stmtUpdate = $conn->prepare($sqlUpdate)) {
        $stmtUpdate->bind_param("i", $request_id);
        $stmtUpdate->execute();
        $stmtUpdate->close();

        // Get sender_id from the followers table
        $stmtSelect = $conn->prepare("SELECT sender_id FROM followers WHERE request_id = ?");
        $stmtSelect->bind_param("i", $request_id);
        $stmtSelect->execute();
        $result = $stmtSelect->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $sender_id = $row['sender_id'];

            // Insert into following table
            if ($stmtInsert = $conn->prepare($sqlInsert)) {
                $stmtInsert->bind_param("ii", $current_user_id, $sender_id);
                $stmtInsert->execute();
                $stmtInsert->close();
            }

            // Insert into friends table
            if ($stmtInsertFriend = $conn->prepare($sqlInsertFriend)) {
                $stmtInsertFriend->bind_param("ii", $current_user_id, $sender_id);
                $stmtInsertFriend->execute();
                $stmtInsertFriend->close();
            }
        }
    }

} elseif ($action === 'decline') {
    // Decline the request
    $sqlDecline = "DELETE FROM followers WHERE request_id = ?";

    if ($stmtDecline = $conn->prepare($sqlDecline)) {
        $stmtDecline->bind_param("i", $request_id);
        $stmtDecline->execute();
        $stmtDecline->close();
    }
}

// Redirect back to the friend requests page
header("Location: friend_requests.php");
exit();
?>
