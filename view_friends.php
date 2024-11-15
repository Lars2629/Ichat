<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$current_user_id = $_SESSION['user_id'];

// Fetch accepted friends
$friends_sql = "SELECT DISTINCT u.user_id, u.username, u.first_name, u.last_name, u.gender, u.contact_no
                FROM friends f
                JOIN users u ON (f.friend_id = u.user_id OR f.user_id = u.user_id)
                WHERE (f.user_id = ? OR f.friend_id = ?) 
                  AND f.status = 'accepted' 
                  AND u.user_id != ?";
$stmt = $conn->prepare($friends_sql);
$stmt->bind_param("iii", $current_user_id, $current_user_id, $current_user_id);
$stmt->execute();
$result = $stmt->get_result();

echo "<h1>Your Friends</h1><ul>";

while ($friend = $result->fetch_assoc()) {
    // Check if a conversation exists
    $conversation_sql = "SELECT conversation_id FROM conversations 
                         WHERE (user1_id = ? AND user2_id = ?) 
                            OR (user1_id = ? AND user2_id = ?)";
    $conv_stmt = $conn->prepare($conversation_sql);
    $conv_stmt->bind_param("iiii", $current_user_id, $friend['user_id'], $friend['user_id'], $current_user_id);
    $conv_stmt->execute();
    $conv_result = $conv_stmt->get_result();

    if ($conv_result->num_rows > 0) {
        $conversation = $conv_result->fetch_assoc();
        $conversation_id = $conversation['conversation_id'];
    } else {
        // Create new conversation if not found
        $insert_conv_sql = "INSERT INTO conversations (user1_id, user2_id) VALUES (?, ?)";
        $insert_conv_stmt = $conn->prepare($insert_conv_sql);
        $insert_conv_stmt->bind_param("ii", $current_user_id, $friend['user_id']);
        $insert_conv_stmt->execute();
        $conversation_id = $conn->insert_id;
    }

    // Display friend information with link to conversation
    echo "<li>";
    echo "<a href='user_profile.php?id=" . htmlspecialchars($friend['user_id']) . "'>" . htmlspecialchars($friend['first_name']) . " " . htmlspecialchars($friend['last_name']) . "</a>";
    echo " - " . htmlspecialchars($friend['username']) . " (" . ucfirst(htmlspecialchars($friend['gender'])) . ")";
    echo " - Contact: " . htmlspecialchars($friend['contact_no']);
    echo " - <a href='conversation.php?conversation_id=" . htmlspecialchars($conversation_id) . "'>Send Message</a>";
    echo "</li>";
}

echo "</ul>";
?>

<a href="home.php">Back to Home</a>
