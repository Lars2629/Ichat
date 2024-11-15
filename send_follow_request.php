<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $friend_id = intval($_POST['friend_id']); // The user ID of the friend to add
    $user_id = $_SESSION['user_id'] ?? null; // Assuming user ID is stored in session

    // Check if the user is logged in
    if ($user_id === null) {
        echo json_encode(["status" => "error", "message" => "You must be logged in to send a follow request."]);
        exit();
    }

    // Validate that the friend ID is valid and not the same as the current user's ID
    if ($friend_id <= 0 || $friend_id === $user_id) {
        echo json_encode(["status" => "error", "message" => "Invalid follow request."]);
        exit();
    }

    // Prepare to check if the user is already following the target user
    if ($stmt = $conn->prepare("SELECT * FROM following WHERE following_id = ? AND followed_id = ?")) {
        $stmt->bind_param("ii", $user_id, $friend_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo json_encode(["status" => "error", "message" => "You are already following this user."]);
        } else {
            // Send follow request to followers table
            if ($stmt = $conn->prepare("INSERT INTO followers (sender_id, receiver_id, status, created_at) VALUES (?, ?, 'pending', CURRENT_TIMESTAMP)")) {
                $stmt->bind_param("ii", $user_id, $friend_id);
                if ($stmt->execute()) {
                    // Now add to the following table
                    if ($stmt = $conn->prepare("INSERT INTO following (following_id, followed_id) VALUES (?, ?)")) {
                        $stmt->bind_param("ii", $user_id, $friend_id);
                        if ($stmt->execute()) {
                            echo json_encode(["status" => "success", "message" => "Follow request sent and you are now following the user."]);
                        } else {
                            echo json_encode(["status" => "error", "message" => "Failed to follow. Please try again later."]);
                        }
                    }
                } else {
                    echo json_encode(["status" => "error", "message" => "Failed to send follow request. Please try again later."]);
                }
            } else {
                echo json_encode(["status" => "error", "message" => "Database error."]);
            }
        }

        // Close the statement
        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Database error."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}
?>
