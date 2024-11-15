<?php
session_start();
include 'db.php'; // Include your database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "Error: User not logged in.";
    exit();
}

$user_id = $_SESSION['user_id']; // The current user

// Check if post_id and friend_ids are passed in the POST data
if (!isset($_POST['post_id']) || !isset($_POST['friend_ids'])) {
    echo "Error: Missing post_id or friend_ids.";
    exit();
}

$post_id = $_POST['post_id'];
$friend_ids = json_decode($_POST['friend_ids'], true); // Decode the JSON-encoded friend IDs

// Check if there are selected friends
if (empty($friend_ids)) {
    echo "Error: No friends selected to share with.";
    exit();
}

$errors = [];

// Insert the share action for each selected friend
foreach ($friend_ids as $friend_id) {
    // Prepare SQL query to insert the share record into the database
    $stmt = $conn->prepare("INSERT INTO shares (post_id, user_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $post_id, $friend_id);
    
    // Check if the query was successful
    if (!$stmt->execute()) {
        $errors[] = "Error sharing with friend $friend_id: " . $stmt->error;
    }
    
    $stmt->close();
}

// Return a success or error message
if (!empty($errors)) {
    echo json_encode(['status' => 'error', 'errors' => $errors]);
} else {
    echo json_encode(['status' => 'success', 'message' => 'Post shared successfully!']);
}

$conn->close();
?>
