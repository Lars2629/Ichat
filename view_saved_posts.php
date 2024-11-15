<?php
// Include database connection
require 'db.php';
session_start();

// Get the logged-in user's ID from the session
$current_user_id = $_SESSION['user_id'] ?? null;

// Check if a user ID is provided in the URL (for viewing other users' saved posts)
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : $current_user_id;

// Ensure we have a valid user ID to proceed
if (!$user_id) {
    echo "<p>User ID is required.</p>";
    exit;
}

// Prepare the SQL query to fetch saved posts along with the counts for likes, comments, shares, and user info
$sql = "
    SELECT p.*, u.username, u.user_id AS post_user_id, 
           (SELECT COUNT(*) FROM likes WHERE post_id = p.post_id) AS likes_count, 
           (SELECT COUNT(*) FROM comments WHERE post_id = p.post_id) AS comments_count, 
           (SELECT COUNT(*) FROM shares WHERE post_id = p.post_id) AS shares_count 
    FROM saved s
    JOIN posts p ON s.post_id = p.post_id
    JOIN users u ON p.user_id = u.user_id
    WHERE s.user_id = ?
    ORDER BY s.saved_at DESC
";

// Prepare and execute the SQL statement
if ($stmt = $conn->prepare($sql)) {
    // Bind the user ID parameter to the query
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if there are saved posts
    if ($result->num_rows > 0) {
        // Start the HTML output
        echo "<h1>Saved Posts</h1>";

        while ($post = $result->fetch_assoc()) {
            // Display each post with likes, comments, and shares
            echo "<div class='post'>";
            
            // User Profile and Username
            echo "<div class='upper_post_tag'>";
            echo "<img class='user_profile' width='50px' height='50px' src='profile_pic5.jpg'>"; // Update with dynamic profile image if needed
            echo "<div class='post_text'>";
            echo "<h3><a href='user_profile.php?id=" . htmlspecialchars($post['post_user_id']) . "'>" . htmlspecialchars($post['username']) . "</a></h3>";
            echo "<p class='post_time'>" . htmlspecialchars(timeAgo($post['created_at'])) . "</p>";
            echo "</div>";
            echo "</div>";
            
            // Post content
            echo "<p>" . nl2br(htmlspecialchars($post['content'])) . "</p>";
            
            // Display image if available
            if ($post['image_url']) {
                echo "<img src='" . htmlspecialchars($post['image_url']) . "' alt='Post Image' style='max-width: 100%;'/>";
            }

            // Display video if available
            if ($post['video_url']) {
                echo "<video controls src='" . htmlspecialchars($post['video_url']) . "' style='max-width: 100%;'></video>";
            }

            // Likes, comments, shares
            echo "<p><strong>Likes:</strong> " . $post['likes_count'] . " | 
                      <strong>Comments:</strong> " . $post['comments_count'] . " | 
                      <strong>Shares:</strong> " . $post['shares_count'] . "</p>";

            // Created at date
            echo "<p><small>Created on: " . date('F j, Y, g:i a', strtotime($post['created_at'])) . "</small></p>";

            // Additional post details
            echo "<p><strong>Privacy:</strong> " . htmlspecialchars($post['privacy']) . "</p>";
            echo "<p><strong>Updated at:</strong> " . date('F j, Y, g:i a', strtotime($post['updated_at'])) . "</p>";
            
            echo "</div><hr>";
        }
    } else {
        // No saved posts found
        echo "<p>No saved posts found.</p>";
    }

    // Close the prepared statement
    $stmt->close();
} else {
    // Error preparing the SQL query
    echo "<p>Error preparing the query.</p>";
}

// Close the database connection
$conn->close();

// Function to format time (you can implement your own or use an existing time ago function)
function timeAgo($datetime) {
    $time_ago = strtotime($datetime);
    $current_time = time();
    $time_difference = $current_time - $time_ago;
    $seconds = $time_difference;
    
    // Calculate time difference and return readable format (you can extend this to include more granular time)
    $minutes = round($seconds / 60);           // value 60 is seconds
    $hours = round($seconds / 3600);           // value 3600 is 60 minutes * 60 sec
    $days = round($seconds / 86400);           // value 86400 is 24 hours * 60 minutes * 60 sec
    $weeks = round($seconds / 604800);         // value 604800 is 7 days * 24 hours * 60 minutes * 60 sec
    $months = round($seconds / 2629440);       // value 2629440 is ((365+365+365+365)/4/12) days * 24 hours * 60 minutes * 60 sec
    $years = round($seconds / 31553280);       // value 31553280 is ((365+365+365+365)/4) days * 24 hours * 60 minutes * 60 sec
    
    if ($seconds <= 60) {
        return "Just Now";
    } else if ($minutes <= 60) {
        return "$minutes minutes ago";
    } else if ($hours <= 24) {
        return "$hours hours ago";
    } else if ($days <= 7) {
        return "$days days ago";
    } else if ($weeks <= 4.3) { // 4.3 == 30/7
        return "$weeks weeks ago";
    } else if ($months <= 12) {
        return "$months months ago";
    } else {
        return "$years years ago";
    }
}
?>
