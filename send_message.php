<?php
session_start(); // Start session
require 'db.php';

// Check if user is logged in and required POST data is set
if (!isset($_SESSION['user_id']) || !isset($_POST['conversation_id']) || !isset($_POST['message'])) {
    echo "<p>Invalid access: Missing data.</p>";
    exit();
}

$current_user_id = $_SESSION['user_id'];
$conversation_id = $_POST['conversation_id'];
$message = trim($_POST['message']);

// Validate inputs
if (empty($message)) {
    echo "<p>Error: Message cannot be empty.</p>";
    exit();
}

try {
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, message, conversation_id) VALUES (?, ?, ?)");
    if (!$stmt) {
        throw new Exception("Error in preparing statement: " . $conn->error);
    }

    $stmt->bind_param("isi", $current_user_id, $message, $conversation_id);

    if (!$stmt->execute()) {
        throw new Exception("Error in executing statement: " . $stmt->error);
    }

    // Return the new message to be displayed
    echo "<p><strong>You:</strong> " . htmlspecialchars($message) . "</p>";

} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}

$stmt->close();
$conn->close();
?>