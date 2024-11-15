<?php
// Include database connection
require 'db.php';
include 'friends_db_handler.php';
session_start();

// Get the logged-in user's ID from the session
$current_user_id = $_SESSION['user_id'] ?? null;

// Check if a user ID is provided in the URL
if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']); // Convert to integer to ensure it's a valid ID
} else {
    // If no ID is provided, use the current user's ID
    $user_id = $current_user_id;
}

// Prepare the SQL statement to retrieve the user's information
$sql = "SELECT * FROM users WHERE user_id = ?";

if ($stmt = $conn->prepare($sql)) {
    // Bind the user_id parameter
    $stmt->bind_param("i", $user_id);

    // Execute the statement
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the user exists
    if ($result->num_rows > 0) {
        // Fetch user data
        $user = $result->fetch_assoc();

        // Display user details
        echo "<h1>User Profile</h1>";
        echo "<p><strong>Username:</strong> " . htmlspecialchars($user['username']) . "</p>";
        
        // Check if the logged-in user is viewing their own profile
        if ($current_user_id === $user_id) {
            echo "<a href='edit_profile.php'>Edit Profile</a>"; // Display 'Edit Profile' if it's the current user's profile
        }

        echo "<p><strong>First Name:</strong> " . htmlspecialchars($user['first_name']) . "</p>";
        echo "<p><strong>Last Name:</strong> " . htmlspecialchars($user['last_name']) . "</p>";
        echo "<p><strong>Email:</strong> " . htmlspecialchars($user['email']) . "</p>";
        echo "<p><strong>Account Created On:</strong> " . htmlspecialchars($user['created_at']) . "</p>";

        // Fetch followers count
        $followers_count_sql = "SELECT COUNT(*) as count FROM following WHERE followed_id = ?";
        $followers_stmt = $conn->prepare($followers_count_sql);
        $followers_stmt->bind_param("i", $user_id);
        $followers_stmt->execute();
        $followers_result = $followers_stmt->get_result();
        $followers_count = $followers_result->fetch_assoc()['count'];

        // Fetch following count
        $following_count_sql = "SELECT COUNT(*) as count FROM following WHERE following_id = ?";
        $following_stmt = $conn->prepare($following_count_sql);
        $following_stmt->bind_param("i", $user_id);
        $following_stmt->execute();
        $following_result = $following_stmt->get_result();
        $following_count = $following_result->fetch_assoc()['count'];

        // Display followers and following counts
        echo "<p><strong>Followers:</strong> " . htmlspecialchars($followers_count) . "</p>";

        // Only show the following count as clickable if it's the current user's profile
        if ($current_user_id === $user_id) {
            echo "<p><strong>Following:</strong> <a href='#' id='following-count' data-userid='" . htmlspecialchars($current_user_id) . "'>" . htmlspecialchars($following_count) . "</a></p>";
        } else {
            // Show the following count but without the link if it's someone else's profile
            echo "<p><strong>Following:</strong> " . htmlspecialchars($following_count) . "</p>";
        }

        // Only show follow/unfollow buttons if the logged-in user is not viewing their own profile
        if ($current_user_id !== $user_id) {
            // Check if the current user is following the user being viewed
            $is_following = false;
            $check_following_sql = "SELECT * FROM following WHERE following_id = ? AND followed_id = ?";
            if ($check_following_stmt = $conn->prepare($check_following_sql)) {
                $check_following_stmt->bind_param("ii", $current_user_id, $user_id);
                $check_following_stmt->execute();
                $check_following_result = $check_following_stmt->get_result();
                $is_following = $check_following_result->num_rows > 0;
                $check_following_stmt->close();
            }

            // Conditional button rendering
            if ($is_following) {
                echo "<button id='unfollow-btn' data-userid='" . htmlspecialchars($user_id) . "'>Unfollow</button>";
            } else {
                echo "<button id='follow-btn' data-userid='" . htmlspecialchars($user_id) . "'>Follow</button>";
            }
        }

        echo "<button id='show-posts-btn'>Show Posts</button>";
        echo "<div id='posts-container' style='display:none; max-height: 300px; overflow-y: auto;'></div>";

        echo "<button id='show-saved-posts-btn'>Show Saved Posts</button>";
        echo "<div id='saved-posts-container' style='display:none; max-height: 300px; overflow-y: auto;'></div>";

    } else {
        echo "<p>User not found.</p>";
    }

    // Close the statement
    $stmt->close();
} else {
    echo "<p>Error preparing statement.</p>";
}

// Navigation Buttons
echo "<br><a href='search.php'>Search Users</a> | ";
echo "<a href='home.php'>Back to Home</a> | ";
echo "<a href='logout.php'>Logout</a>";
?>

<!-- Hidden container to display following users list -->
<div id="following-list-container" style="display:none; max-height: 300px; overflow-y: auto;"></div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="user_profile.js"></script>
<script>
$(document).on('click', '#show-posts-btn', function() {
    const userId = <?php echo json_encode($user_id); ?>;
    $.get('fetch_user_posts.php', { user_id: userId }, function(response) {
        const result = JSON.parse(response);
        if (result.status === "success") {
            $('#posts-container').html(result.posts.map(post => 
                `<div class='post'>
                    <p>${post.content}</p>
                    ${post.image_url ? `<img src='${post.image_url}' alt='Post Image' style='max-width: 100%;'/>` : ''}
                    ${post.video_url ? `<video controls src='${post.video_url}' style='max-width: 100%;'></video>` : ''}
                    <p><strong>Likes:</strong> ${post.likes_count} | <strong>Comments:</strong> ${post.comments_count} | <strong>Shares:</strong> ${post.shares_count}</p>
                </div>`).join(''));
            $('#posts-container').toggle();
        } else {
            alert(result.message);
        }
    }).fail(function() {
        alert("An error occurred while fetching posts.");
    });
});

$(document).on('click', '#show-saved-posts-btn', function() {
    const userId = <?php echo json_encode($user_id); ?>;

    $.get('saved_fetch.php', { user_id: userId }, function(response) {
        const result = JSON.parse(response);
        if (result.status === "success") {
            const postsHtml = result.posts.map(post => 
                `<div class='post'>
                    <p>${post.content}</p>
                    ${post.image_url ? `<img src='${post.image_url}' alt='Post Image' style='max-width: 100%;'/>` : ''}
                    ${post.video_url ? `<video controls src='${post.video_url}' style='max-width: 100%;'></video>` : ''}
                    <p><strong>Likes:</strong> ${post.likes_count} | <strong>Comments:</strong> ${post.comments_count} | <strong>Shares:</strong> ${post.shares_count}</p>
                </div>`).join('');
            $('#saved-posts-container').html(postsHtml);
            $('#saved-posts-container').toggle();
        } else {
            alert(result.message || "An error occurred while fetching saved posts.");
        }
    }).fail(function() {
        alert("An error occurred while fetching saved posts.");
    });
});

// Click event for following count
$(document).on('click', '#following-count', function(e) {
    e.preventDefault(); // Prevent default link behavior

    const userId = $(this).data('userid'); // Get logged-in user ID
    $.get('fetch_user_following.php', { user_id: userId }, function(response) {
        const result = JSON.parse(response);
        if (result.status === "success") {
            const followingHtml = result.following.map(user => 
                `<div class="user">
                    <p>${user.username}</p>
                </div>`).join('');
            $('#following-list-container').html(followingHtml);
            $('#following-list-container').toggle(); // Show the following list container
        } else {
            alert(result.message || "Error fetching following list.");
        }
    }).fail(function() {
        alert("An error occurred while fetching the following list.");
    });
});
</script>
