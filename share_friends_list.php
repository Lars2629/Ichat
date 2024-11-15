<?php
session_start();
include 'db.php'; // Include your database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch friends list where status is 'accepted' and we consider both directions of the friendship
$query = "SELECT u.user_id, u.username, u.profile_picture 
          FROM friends f
          JOIN users u ON (f.friend_id = u.user_id OR f.user_id = u.user_id)
          WHERE (f.user_id = ? OR f.friend_id = ?) 
          AND f.status = 'accepted'
          AND u.user_id != ?";  // Make sure not to include the logged-in user in their own friend list

$stmt = $conn->prepare($query);
$stmt->bind_param('iii', $user_id, $user_id, $user_id); // Bind the user_id for both conditions
$stmt->execute();
$result = $stmt->get_result();

$friends = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $friends[] = $row;
    }
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Share with Friends</title>
    <link rel="stylesheet" href="styles.css"> <!-- Include your CSS -->
</head>
<body>
    <h1>Select Friends to Share With</h1>
    <div id="friend-list">
        <?php if (!empty($friends)): ?>
            <ul>
                <?php foreach ($friends as $friend): ?>
                    <li>
                        <img src="<?php echo htmlspecialchars($friend['profile_picture']); ?>" alt="Profile Picture" width="50">
                        <?php echo htmlspecialchars($friend['username']); ?>
                        <button class="share-button" data-friend-id="<?php echo $friend['user_id']; ?>">Share</button>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No friends found.</p>
        <?php endif; ?>
    </div>
    <button id="close-friends-modal">Close</button>

    <script>
        document.getElementById('close-friends-modal').addEventListener('click', function() {
            window.location.href = 'your_previous_page.php'; // Redirect back to the previous page
        });

        // Add functionality for sharing with friends if needed
        document.querySelectorAll('.share-button').forEach(button => {
            button.addEventListener('click', function() {
                const friendId = this.getAttribute('data-friend-id');
                // Handle share logic here (e.g., make an AJAX request to share the post)
                alert(`Post shared with friend ID: ${friendId}`);
            });
        });
    </script>
</body>
</html>
