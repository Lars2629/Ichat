<?php
session_start();
include 'db.php'; // Include your database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User is not logged in']);
    exit();
}

$user_id = $_SESSION['user_id']; // Get the current user ID

// Get data from the request body
$data = json_decode(file_get_contents('php://input'), true);
$post_id = isset($data['post_id']) ? (int)$data['post_id'] : 0;
$friend_ids = isset($data['friend_ids']) ? $data['friend_ids'] : [];

if (empty($friend_ids) || $post_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid data provided']);
    exit();
}

// Start a transaction to ensure all shares are inserted correctly
$conn->begin_transaction();

try {
    // Iterate through each friend_id and insert a share record
    foreach ($friend_ids as $friend_id) {
        // Ensure the friend_id is valid
        if ((int)$friend_id > 0) {
            $stmt = $conn->prepare("INSERT INTO shares (post_id, user_id, friend_id) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $post_id, $user_id, $friend_id);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Commit the transaction
    $conn->commit();
    
    // Respond back to the client indicating success
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // If any error occurs, rollback the transaction
    $conn->rollback();
    
    // Return an error response
    echo json_encode(['success' => false, 'message' => 'An error occurred while sharing the post']);
}

// Close the database connection
$conn->close();
?>
