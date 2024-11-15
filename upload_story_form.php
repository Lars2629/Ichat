<form action="upload_story.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="user_id" value="1"> <!-- Replace 1 with the logged-in user's ID -->
    <input type="file" name="story" required>
    <input type="text" name="text_caption" placeholder="Add a caption">
    <select name="visibility">
        <option value="public">Public</option>
        <option value="friends" selected>Friends</option>
        <option value="private">Private</option>
    </select>
    <button type="submit">Upload Story</button>
</form>