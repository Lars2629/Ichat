<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

echo "<p>Welcome, " . htmlspecialchars($_SESSION['username']) . "!</p>";

$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT p.*, u.username FROM posts p JOIN users u ON p.user_id = u.user_id ORDER BY p.created_at DESC");

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<div id='post-" . $row['post_id'] . "'>";
        echo "<h3>" . htmlspecialchars($row['username']) . " (" . $row['created_at'] . ")</h3>";
        echo "<p>" . htmlspecialchars($row['content']) . "</p>";

        if (!empty($row['image_url'])) {
            echo "<img src='" . htmlspecialchars($row['image_url']) . "' alt='Post Image' style='max-width:300px;'><br>";
        }
        echo "<p>Privacy: " . htmlspecialchars($row['privacy']) . "</p>";

        // Check if the user has already liked this post
        $post_id = $row['post_id'];
        $likes_result = $conn->query("SELECT COUNT(*) as like_count FROM likes WHERE post_id = $post_id");
        $likes_data = $likes_result->fetch_assoc();
        
        // Fetch whether the user has liked the post
        $user_like_result = $conn->query("SELECT * FROM likes WHERE post_id = $post_id AND user_id = $user_id");
        $user_has_liked = $user_like_result->num_rows > 0;

        echo "<p id='like-count-$post_id'>Likes: " . $likes_data['like_count'] . "</p>";
        
        // Set the button color based on whether the user has liked the post
        $like_button_color = $user_has_liked ? 'green' : '';
        echo "<button class='like-button' data-post-id='$post_id' style='background-color: $like_button_color;'>Like</button>";
        echo "<hr>";
        echo "</div>";
        
        echo "<button class='comment-icon' data-post-id='" . $row['post_id'] . "'>💬 Comment</button>";
        
        // Comments container (initially hidden)
        echo "<div class='comments-container' id='comments-container-" . $row['post_id'] . "' style='display:none;'>";
        echo "<h4>Comments:</h4>";
        echo "<div class='scrollable-comments' id='comments-" . $row['post_id'] . "' style='max-height: 150px; overflow-y: auto;'>";

        // Fetch and display comments
        $comments_result = $conn->query("SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.user_id WHERE c.post_id = " . $row['post_id'] . " ORDER BY c.created_at DESC");
        if ($comments_result->num_rows > 0) {
            while ($comment = $comments_result->fetch_assoc()) {
                echo "<p><strong>" . htmlspecialchars($comment['username']) . ":</strong> " . htmlspecialchars($comment['content']) . " (" . $comment['created_at'] . ")</p>";
            }
        } else {
            echo "<p>No comments yet.</p>";
        }
        echo "</div>";

        // Comment input field (initially hidden)
        echo "<div class='comment-input' id='comment-input-" . $row['post_id'] . "' style='display:none;'>";
        echo "<textarea name='comment_content' placeholder='Add a comment...' required></textarea>";
        echo "<button class='submit-comment' data-post-id='" . $row['post_id'] . "'>Submit</button>";
        echo "</div>";

        echo "</div>"; // Close comments-container
        echo "<hr>";
        echo "</div>"; // Close post div
    }
} else {
    echo "No posts found.";
}

$conn->close();
?>


<!-- jQuery CDN for easier AJAX handling -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('.like-button').click(function() {
        var button = $(this);
        var postId = button.data('post-id');

        $.ajax({
            url: 'like_post.php',
            method: 'POST',
            data: { post_id: postId },
            success: function(response) {
                try {
                    var data = JSON.parse(response);
                    if (data.status === 'success') {
                        var likeCountElem = $('#like-count-' + postId);
                        likeCountElem.text('Likes: ' + data.like_count);
                        
                        // Toggle button color based on like status
                        if (data.liked) {
                            button.css('background-color', 'green'); // Liked
                        } else {
                            button.css('background-color', ''); // Not liked
                        }
                    } else {
                        alert(data.message);
                    }
                } catch (error) {
                    console.error('Error parsing response:', error);
                }
            },
            error: function() {
                alert('Error processing your request.');
            }
        });
    });
});
$('.comment-icon').click(function() {
        var postId = $(this).data('post-id');
        $('#comments-container-' + postId).toggle(); // Toggle comments container
        $('#comment-input-' + postId).toggle(); // Toggle input field
    });

    // Comment submit event
    $('.submit-comment').click(function() {
        var postId = $(this).data('post-id');
        var commentContent = $('#comment-input-' + postId).find('textarea[name="comment_content"]').val();

        $.ajax({
            url: 'add_comment.php',
            method: 'POST',
            data: { post_id: postId, comment_content: commentContent },
            success: function(response) {
                try {
                    var data = JSON.parse(response); // Parse the response
                    if (data.status === 'success') {
                        // Add the new comment to the comments section
                        $('#comments-' + postId).append('<p><strong>You:</strong> ' + commentContent + ' (Just now)</p>');
                        $('#comment-input-' + postId).find('textarea[name="comment_content"]').val(''); // Clear the textarea
                    } else {
                        alert(data.message); // Show the error message
                    }
                } catch (error) {
                    console.error('Error parsing JSON response:', error);
                    alert('Failed to process response.');
                }
            },
            error: function() {
                alert('Error adding comment.');
            }
        });
    });

</script>

<a href="home.php">Home</a>
