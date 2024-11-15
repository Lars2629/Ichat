<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Search</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>Search Users</h1>
    <input type="text" id="search" placeholder="Type to search users..." onkeyup="searchUser()">
    <ul id="result"></ul>

    <script>
        function searchUser() {
            var searchText = $("#search").val();
            if (searchText.length > 0) {
                $.ajax({
                    url: 'search_handler.php',
                    type: 'GET',
                    data: { query: searchText },
                    success: function(data) {
                        $("#result").html(data);
                    }
                });
            } else {
                $("#result").html('');  // Clear the result if the search box is empty
            }
        }
    </script>
    
    <a href="home.php">Back to Home</a>
</body>
</html>