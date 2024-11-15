<?php
require_once 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['story_id']) && isset($_SESSION['user_id'])) {
    $story_id = $data['story_id'];
    $user_id = $_SESSION['user_id'];

    // Insert the view into the story_views table
    $query = "INSERT INTO story_views (user_id, story_id) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $story_id);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['message' => 'Story view recorded']);
} else {
    echo json_encode(['message' => 'Invalid data']);
}
?>