<?php
// Include the database connection
require_once 'db.php';

// Function to check if two users follow each other
function checkIfFollowingEachOther($userId1, $userId2, $conn) {
    // Query to check if user1 follows user2
    $query1 = "SELECT * FROM following WHERE following_id = ? AND followed_id = ?";
    $stmt1 = $conn->prepare($query1);
    $stmt1->bind_param("ii", $userId1, $userId2);
    $stmt1->execute();
    $result1 = $stmt1->get_result();

    // Query to check if user2 follows user1 (reverse follow)
    $query2 = "SELECT * FROM following WHERE following_id = ? AND followed_id = ?";
    $stmt2 = $conn->prepare($query2);
    $stmt2->bind_param("ii", $userId2, $userId1);
    $stmt2->execute();
    $result2 = $stmt2->get_result();

    // Check if both users follow each other
    if ($result1->num_rows > 0 && $result2->num_rows > 0) {
        return true;  // They follow each other
    }
    return false;  // They don't follow each other
}

// Function to check if a friendship already exists
function checkIfFriendshipExists($userId1, $userId2, $conn) {
    // Query to check if the friendship already exists in the friends table
    $query = "SELECT * FROM friends WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiii", $userId1, $userId2, $userId2, $userId1);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->num_rows > 0;  // Return true if the friendship exists
}

// Function to add a friendship in the friends table
function addFriendship($userId1, $userId2, $conn) {
    // Check if the friendship already exists
    if (checkIfFriendshipExists($userId1, $userId2, $conn)) {
        return;
    }

    // Insert a new friendship record in the friends table with 'accepted' status (immediate acceptance)
    $query = "INSERT INTO friends (user_id, friend_id, status) VALUES (?, ?, 'accepted')";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $userId1, $userId2);

    if ($stmt->execute()) {
    } else {
    }
}

// Now we will query the 'following' table for user pairs
$query = "SELECT following_id, followed_id FROM following";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

// Iterate through each following pair
while ($row = $result->fetch_assoc()) {
    $userId1 = $row['following_id'];
    $userId2 = $row['followed_id'];

    // Check if the two users follow each other
    if (checkIfFollowingEachOther($userId1, $userId2, $conn)) {
        // If they follow each other, add them as friends
        addFriendship($userId1, $userId2, $conn);
    }
}
?>
