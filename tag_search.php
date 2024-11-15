<?php
include 'db.php';

$query = isset($_GET['query']) ? $_GET['query'] : '';

if (!empty($query)) {
    // Prepare a search query for users
    $searchTerm = '%' . $conn->real_escape_string($query) . '%';
    $sql = "SELECT user_id, username, first_name, last_name 
            FROM users 
            WHERE username LIKE ? 
               OR first_name LIKE ? 
               OR last_name LIKE ? 
            LIMIT 10";

    // Prepare statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);

    // Execute and get results
    $stmt->execute();
    $result = $stmt->get_result();
    $users = [];

    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }

    // Return the results as JSON
    echo json_encode($users);
} else {
    echo json_encode([]); // Return empty array if query is empty
}

$conn->close();
?>
