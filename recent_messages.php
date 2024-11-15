<?php
// Include the database connection file
include 'db.php'; // Adjust the path as necessary

session_start();

if (!isset($_SESSION['user_id'])) {
    echo "<p>You must be logged in to view messages.</p>";
    exit();
}

$loggedInUserId = $_SESSION['user_id']; // Make sure this is set upon user login

// SQL query to get the latest message for each unique user
$sql = "
    SELECT 
        u.username AS recipient_username, 
        m.message, 
        m.created_at,
        c.conversation_id,
        u.user_id as recipient_user_id
    FROM 
        conversations c
    JOIN 
        messages m ON m.conversation_id = c.conversation_id
    JOIN 
        users u ON (u.user_id = c.user1_id OR u.user_id = c.user2_id)
    WHERE 
        (c.user1_id = ? OR c.user2_id = ?) 
        AND u.user_id != ? 
        AND m.created_at = (
            SELECT MAX(created_at) 
            FROM messages 
            WHERE conversation_id = c.conversation_id
        )
    ORDER BY 
        m.created_at DESC";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Database error: " . htmlspecialchars($conn->error)); // Handle prepare error
}
$stmt->bind_param("iii", $loggedInUserId, $loggedInUserId, $loggedInUserId);
$stmt->execute();
$result = $stmt->get_result();

if ($result === false) {
    die("Database error: " . htmlspecialchars($conn->error)); // Handle execution error
}

$recentMessages = $result->fetch_all(MYSQLI_ASSOC);

// Handle search query (if any)
$searchResults = [];
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : ''; // Ensure any leading/trailing spaces are removed

if (!empty($searchQuery)) {
    // Search for users that match the query and exclude the logged-in user
    $searchSql = "SELECT * FROM users WHERE (username LIKE ? OR first_name LIKE ? OR last_name LIKE ?) AND user_id != ?";
    $stmt = $conn->prepare($searchSql);
    $searchParam = "%" . $searchQuery . "%";
    $stmt->bind_param("sssi", $searchParam, $searchParam, $searchParam, $loggedInUserId);
    $stmt->execute();
    $searchResult = $stmt->get_result();
    while ($user = $searchResult->fetch_assoc()) {
        $searchResults[] = $user;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recent Messages</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .message-item {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin: 5px 0;
            text-decoration: none;
            color: black;
            display: block; /* Make the anchor a block-level element */
        }
        .message-item:hover {
            background-color: #f0f0f0; /* Change background on hover */
        }
        .search-bar {
            margin-bottom: 20px;
        }
        .search-bar input {
            padding: 10px;
            width: 100%;
            max-width: 300px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .search-results {
            margin-top: 20px;
        }
        .search-result-item {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <h1>Recent Messages</h1>

    <!-- Search Bar -->
    <div class="search-bar">
        <form method="GET" action="recent_messages.php">
            <input type="text" name="search" placeholder="Search users..." value="<?php echo htmlspecialchars($searchQuery); ?>">
        </form>
    </div>

    <!-- Search Results (if any) -->
    <?php if (!empty($searchQuery)): ?>
        <div id="searchResults" class="search-results">
            <h3>Search Results:</h3>
            <?php if (!empty($searchResults)): ?>
                <?php foreach ($searchResults as $user): ?>
                    <div class="search-result-item">
                        <?php
                            // Get the conversation_id for the searched user
                            $otherUserId = $user['user_id'];
                            $conversationSql = "SELECT conversation_id FROM conversations WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)";
                            $conversationStmt = $conn->prepare($conversationSql);
                            $conversationStmt->bind_param("iiii", $loggedInUserId, $otherUserId, $otherUserId, $loggedInUserId);
                            $conversationStmt->execute();
                            $conversationResult = $conversationStmt->get_result();
                            $conversationId = null;
                            if ($conversationResult->num_rows > 0) {
                                $conversation = $conversationResult->fetch_assoc();
                                $conversationId = $conversation['conversation_id'];
                            }
                        ?>
                        <?php if ($conversationId): ?>
                            <a href="conversation.php?conversation_id=<?php echo $conversationId; ?>">
                                <?php echo htmlspecialchars($user['first_name'] . " " . $user['last_name']) . " (@" . htmlspecialchars($user['username']) . ")"; ?>
                            </a>
                        <?php else: ?>
                            <span><?php echo htmlspecialchars($user['first_name'] . " " . $user['last_name']) . " (@" . htmlspecialchars($user['username']) . ")"; ?></span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No users found matching your search.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Recent Messages List -->
    <h2>Your Recent Messages</h2>
    <ul>
        <?php if (empty($recentMessages)): ?>
            <li>No recent messages.</li>
        <?php else: ?>
            <?php foreach ($recentMessages as $message): ?>
                <li>
                    <!-- Link to conversation page with the correct conversation_id -->
                    <a href="conversation.php?conversation_id=<?php echo htmlspecialchars($message['conversation_id']); ?>" class="message-item">
                        <strong><?php echo htmlspecialchars($message['recipient_username']); ?></strong><br>
                        <p><?php echo htmlspecialchars($message['message']); ?></p>
                        <small><?php echo date("F j, Y, g:i a", strtotime($message['created_at'])); ?></small>
                    </a>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>

</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
