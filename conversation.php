<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['conversation_id'])) {
    echo "<p>Invalid access.</p>";
    exit();
}

$current_user_id = $_SESSION['user_id'];
$conversation_id = $_GET['conversation_id'];

// Fetch conversation history by conversation_id
$sql = "SELECT * FROM messages WHERE conversation_id = ? ORDER BY created_at ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $conversation_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Conversation</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>Conversation</h1>
    
    <div id="chat-box" style="border:1px solid #ccc; padding:10px; height:300px; overflow-y:scroll;">
        <?php while ($message = $result->fetch_assoc()): ?>
            <p><strong><?php echo $message['sender_id'] == $current_user_id ? 'You' : 'Friend'; ?>:</strong> <?php echo htmlspecialchars($message['message']); ?></p>
        <?php endwhile; ?>
    </div>
    
    <form id="message-form">
        <input type="hidden" id="conversation_id" value="<?php echo $conversation_id; ?>">
        <input type="text" id="message" placeholder="Type your message..." required>
        <button type="submit">Send</button>
    </form>

    <script>
        $('#message-form').submit(function(event) {
            event.preventDefault();
            $.ajax({
                url: 'send_message.php',
                type: 'POST',
                data: {
                    conversation_id: $('#conversation_id').val(),
                    message: $('#message').val()
                },
                success: function(response) {
                    $('#message').val('');
                    $('#chat-box').append(response);
                    $('#chat-box').scrollTop($('#chat-box')[0].scrollHeight);
                }
            });
        });

        function loadMessages() {
            $.ajax({
                url: 'load_messages.php',
                type: 'GET',
                data: { conversation_id: $('#conversation_id').val() },
                success: function(data) {
                    $('#chat-box').html(data);
                    $('#chat-box').scrollTop($('#chat-box')[0].scrollHeight);
                }
            });
        }

        setInterval(loadMessages, 2000);
    </script>
</body>
</html>