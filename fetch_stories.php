<?php
session_start();
require_once 'db.php';

if (!isset($_GET['user_id']) || !isset($_SESSION['user_id'])) {
    die("Invalid request.");
}

$current_user_id = $_SESSION['user_id'];
$user_id = intval($_GET['user_id']);

// Fetch active stories for the user
$sql = "SELECT story_id, content_url, text_caption
        FROM stories
        WHERE user_id = ? AND status = 'active' AND expires_at > NOW()
        ORDER BY created_at ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$stories = [];
while ($row = $result->fetch_assoc()) {
    // Track view (if not already viewed)
    $checkViewSql = "SELECT 1 FROM story_views WHERE user_id = ? AND story_id = ?";
    $checkViewStmt = $conn->prepare($checkViewSql);
    $checkViewStmt->bind_param("ii", $current_user_id, $row['story_id']);
    $checkViewStmt->execute();
    $viewExists = $checkViewStmt->get_result()->num_rows > 0;

    if (!$viewExists) {
        $insertViewSql = "INSERT INTO story_views (user_id, story_id) VALUES (?, ?)";
        $insertViewStmt = $conn->prepare($insertViewSql);
        $insertViewStmt->bind_param("ii", $current_user_id, $row['story_id']);
        $insertViewStmt->execute();
    }

    $stories[] = $row;
}

echo json_encode($stories);

$stmt->close();
$conn->close();
?>