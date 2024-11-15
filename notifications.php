<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch notifications for the logged-in user
$sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="style.css"> <!-- Optional: Include your CSS -->
</head>
<body>
    <div class="container">
        <h1>Your Notifications</h1>
        
        <button onclick="document.getElementById('notifications').style.display='block'">Show Notifications</button>

        <div id="notifications" style="display:none;">
            <h2>Recent Activities</h2>
            <?php if (!empty($notifications)): ?>
                <ul>
                    <?php foreach ($notifications as $notification): ?>
                        <li>
                            <strong><?php echo htmlspecialchars($notification['type']); ?>:</strong>
                            <?php echo htmlspecialchars($notification['message']); ?>
                            <em>(<?php echo $notification['created_at']; ?>)</em>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No notifications found.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>