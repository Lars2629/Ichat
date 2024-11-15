<?php
session_start(); // Start session at the top
require 'db.php';

// Check if user is logged in and conversation_id is set
if (!isset($_SESSION['user_id']) || !isset($_GET['conversation_id'])) {
    echo "<p>Invalid access.</p>";
    exit();
}

$current_user_id = $_SESSION['user_id'];
$conversation_id = $_GET['conversation_id'];

// Fetch conversation history by conversation_id
$sql = "SELECT * FROM messages 
        WHERE conversation_id = ? 
        ORDER BY created_at ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $conversation_id);
$stmt->execute();
$result = $stmt->get_result();

while ($message = $result->fetch_assoc()) {
    echo "<p><strong>" . ($message['sender_id'] == $current_user_id ? 'You' : 'Friend') . ":</strong> " . htmlspecialchars($message['message']) . "</p>";
}

$stmt->close();
$conn->close();
?>