<?php
// Include database connection
require 'db.php';
session_start();

// Get the current user's ID from the session
$current_user_id = $_SESSION['user_id'] ?? null;

// Check if the user is logged in
if ($current_user_id === null) {
    echo "<p>You must be logged in to view friend requests.</p>";
    echo "<a href='login.php'>Login</a>";
    exit();
}

// Prepare the SQL statement to retrieve friend requests for the current user
$sql = "SELECT fr.request_id, u.user_id, u.username, u.first_name, u.last_name 
        FROM followers fr
        JOIN users u ON fr.sender_id = u.user_id 
        WHERE fr.receiver_id = ? AND fr.status = 'pending'";

if ($stmt = $conn->prepare($sql)) {
    // Bind the current user's ID
    $stmt->bind_param("i", $current_user_id);

    // Execute the statement
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if there are any friend requests
    if ($result->num_rows > 0) {
        echo "<h1>Followers</h1>";
        echo "<ul>";

        // Loop through the friend requests
        while ($request = $result->fetch_assoc()) {
            $request_id = $request['request_id'];
            $user_id = $request['user_id'];
            $full_name = htmlspecialchars($request['first_name'] . " " . $request['last_name']);
            $username = htmlspecialchars($request['username']);

            echo "<li>";
            echo "<strong>$full_name (@" . $username . ")</strong>";
            echo " <form action='follow_requests_handler.php' method='POST' style='display:inline;'>";
            echo "<input type='hidden' name='request_id' value='" . htmlspecialchars($request_id) . "' />";
            echo "<input type='hidden' name='action' value='accept' />";
            echo "<button type='submit'>Follow back</button>";
            echo "</form>";
            echo " <form action='follow_requests_handler.php' method='POST' style='display:inline;'>";
            echo "<input type='hidden' name='request_id' value='" . htmlspecialchars($request_id) . "' />";
            echo "<input type='hidden' name='action' value='decline' />";
            echo "<button type='submit'>Decline</button>";
            echo "</form>";
            echo "</li>";
        }

        echo "</ul>";
    } else {
        echo "<p>No friend requests found.</p>";
    }

    // Close the statement
    $stmt->close();
} else {
    echo "<p>Error preparing statement.</p>";
}

// Navigation Buttons
echo "<a href='home.php'>Back to Home</a> | ";
echo "<a href='logout.php'>Logout</a>";
?>
