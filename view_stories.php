<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to view stories.");
}

$current_user_id = $_SESSION['user_id'];

// Get users with active stories and check if the logged-in user has viewed them
$sql = "SELECT DISTINCT users.user_id, users.username, users.profile_picture,
        (SELECT COUNT(1) 
         FROM story_views 
         JOIN stories ON story_views.story_id = stories.story_id
         WHERE story_views.user_id = $current_user_id 
           AND story_views.story_id = stories.story_id 
           AND stories.user_id = users.user_id) AS viewed
        FROM stories
        JOIN users ON stories.user_id = users.user_id
        WHERE stories.status = 'active' AND stories.expires_at > NOW()
        ORDER BY stories.created_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Stories</title>
    <link rel="stylesheet" href="view_stories.css">
</head>
<body>

<div class="profile-row">
    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Add CSS class based on whether the story is viewed
            $borderClass = ($row['viewed'] == 0) ? "not-viewed" : "viewed";
            echo "<img src='" . $row['profile_picture'] . "' 
                      alt='" . $row['username'] . "' 
                      class='profile-picture $borderClass' 
                      data-user-id='" . $row['user_id'] . "'>";
        }
    } else {
        echo "No active stories available.";
    }
    ?>
</div>

<!-- Story Overlay -->
<div class="story-overlay" id="storyOverlay">
    <span class="close-btn" onclick="closeStory()">&#10006;</span>
    <img id="storyContent" class="story-content" src="" alt="Story Content">
    <p id="storyCaption"></p>
</div>

<script>
// JavaScript to handle story display

// Array to store user stories
let userStories = {};

// Fetch stories for each user when a profile picture is clicked
document.querySelectorAll('.profile-picture').forEach(pic => {
    pic.addEventListener('click', function () {
        let userId = this.getAttribute('data-user-id');
        showStories(userId);
    });
});

// Function to load and show stories
function showStories(userId) {
    if (!userStories[userId]) {
        // Fetch stories via AJAX if not already fetched
        fetch(`fetch_stories.php?user_id=${userId}`)
            .then(response => response.json())
            .then(data => {
                userStories[userId] = data;
                displayStory(userId, 0);
            });
    } else {
        // Display already fetched stories
        displayStory(userId, 0);
    }
}

// Function to display a single story and handle timing
function displayStory(userId, storyIndex) {
    const stories = userStories[userId];
    if (stories && storyIndex < stories.length) {
        const story = stories[storyIndex];
        
        // Set story content and show overlay
        document.getElementById('storyContent').src = story.content_url;
        document.getElementById('storyCaption').innerText = story.text_caption;
        document.getElementById('storyOverlay').style.display = 'flex';

        // Auto-close after 5 seconds or show the next story
        setTimeout(() => {
            if (storyIndex + 1 < stories.length) {
                displayStory(userId, storyIndex + 1);
            } else {
                closeStory();
            }
        }, 5000);
    }
}

// Function to close the story overlay
function closeStory() {
    document.getElementById('storyOverlay').style.display = 'none';
}
</script>

</body>
</html>