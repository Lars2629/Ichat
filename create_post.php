<?php
// Start the session
session_start();

// Ensure that the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login page or show an error
    header("Location: login.php");
    exit();
}

// Assuming $_SESSION['user_id'] holds the logged-in user's ID
$loggedInUserId = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Post with Tagging</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .tag-section { display: none; margin-top: 10px; }
        .tag-list { list-style-type: none; padding: 0; max-height: 150px; overflow-y: auto; }
        .tag-list li { cursor: pointer; padding: 5px; border-bottom: 1px solid #ccc; }
        .tag-list li:hover { background-color: #f0f0f0; }
        .tag-list button { background-color: red; color: white; border: none; padding: 2px 8px; font-size: 12px; cursor: pointer; margin-left: 10px; }
        .tag-list button:hover { background-color: darkred; }
    </style>
</head>
<body>

    <h1>Create a New Post</h1>
    <form id="create-post-form" enctype="multipart/form-data">
        <textarea name="content" placeholder="Write your post..." required></textarea><br>
        <label>Upload Image:</label>
        <input type="file" name="image" accept="image/*"><br>
        
        <label>Upload Video:</label>
        <input type="file" name="video" accept="video/*"><br>
        <select name="privacy">
            <option value="public">Public</option>
            <option value="friends">Friends</option>
            <option value="private">Private</option>
        </select><br>

        <!-- Tagging Functionality -->
        <button type="button" onclick="toggleTagSection()">Tag</button><br>
        <div id="tagSection" class="tag-section">
            <input type="text" id="searchField" placeholder="Search to tag..." autocomplete="off">
            <ul id="searchResults" class="tag-list"></ul>
        </div>
        <input type="hidden" name="tagged_names" id="taggedNames">

        <button type="submit">Create Post</button>
    </form>

    <div id="response-message"></div>

    <!-- Display Tagged Users with remove functionality -->
    <div>
        <h3>Tagged Users:</h3>
        <ul id="taggedUsersList" class="tag-list">
            <!-- Tagged users will appear here -->
        </ul>
    </div>

    <!-- Hidden Field for Logged-in User ID -->
    <input type="hidden" id="loggedInUserId" value="<?php echo $loggedInUserId; ?>">

    <script>
        // Toggle visibility of the tag section
        function toggleTagSection() {
            const tagSection = document.getElementById('tagSection');
            tagSection.style.display = tagSection.style.display === 'none' ? 'block' : 'none';
        }

        // Search for users to tag
        $('#searchField').on('input', function() {
            const searchQuery = $(this).val();
            const loggedInUserId = $('#loggedInUserId').val();  // Get the logged-in user ID

            if (searchQuery.length > 0) {
                $.ajax({
                    url: 'tag_search.php',  // Assuming you have a PHP file to search users
                    method: 'GET',
                    data: { query: searchQuery },
                    dataType: 'json',
                    success: function(data) {
                        const results = $('#searchResults');
                        results.empty();

                        // Filter out the logged-in user from the search results
                        const filteredData = data.filter(user => user.user_id !== parseInt(loggedInUserId));

                        if (filteredData.length > 0) {
                            filteredData.forEach(user => {
                                const listItem = $('<li></li>')
                                    .text(`${user.first_name} ${user.last_name} (${user.username})`)
                                    .on('click', () => tagUser(user.user_id, user.username));
                                results.append(listItem);
                            });
                        } else {
                            results.append('<li>No users found</li>');
                        }
                    }
                });
            } else {
                $('#searchResults').empty();
            }
        });

        // Tag a user and add their name to the hidden field
        function tagUser(userId, username) {
            const taggedNamesInput = $('#taggedNames');
            let taggedNames = taggedNamesInput.val().split(',').filter(Boolean);

            // Avoid adding the same tag more than once
            if (!taggedNames.includes(username)) {
                taggedNames.push(username);
                taggedNamesInput.val(taggedNames.join(','));

                // Add the tagged user to the list with a remove option
                const tagItem = $('<li></li>')
                    .text(`${username}`)
                    .append($('<button>X</button>').on('click', function() {
                        removeTag(userId, username);
                    }));

                $('#taggedUsersList').append(tagItem);
            }
        }

        // Remove a tagged user from the list and update the hidden input
        function removeTag(userId, username) {
            const taggedNamesInput = $('#taggedNames');
            let taggedNames = taggedNamesInput.val().split(',').filter(Boolean);

            // Remove the user from the tagged users array
            taggedNames = taggedNames.filter(name => name !== username);
            taggedNamesInput.val(taggedNames.join(','));

            // Remove the user from the displayed list
            $('#taggedUsersList').find(`li:contains(${username})`).remove();
        }

        $(document).ready(function() {
            // Form submission
            $('#create-post-form').submit(function(e) {
                e.preventDefault();
                var formData = new FormData(this);

                $.ajax({
                    url: 'create_post_handler.php',  // PHP handler for creating posts
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        $('#response-message').html('<p>' + response.message + '</p>');
                        if (response.status === 'success') {
                            $('#create-post-form')[0].reset();
                            $('#taggedUsersList').empty(); // Clear tagged users list on success
                        }
                    },
                    error: function(jqXHR) {
                        // Show error message
                        $('#response-message').html('<p>An error occurred while creating the post: ' + jqXHR.responseText + '</p>');
                    }
                });
            });
        });
    </script>

    <a href="home.php">Posts</a>
    <a href="home.php">Home</a>

</body>
</html>

