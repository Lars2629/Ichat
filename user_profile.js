$(document).ready(function() {
    // Handle follow button click
    $(document).on('click', '#follow-btn', function() {
        const userId = $(this).data('userid');
        $.post('send_follow_request.php', { friend_id: userId }, function(response) {
            const result = JSON.parse(response);
            alert(result.message);
            if (result.status === "success") {
                $('#follow-btn').replaceWith("<button id='unfollow-btn' data-userid='" + userId + "'>Unfollow</button>");
            }
        }).fail(function() {
            alert("An error occurred while sending the follow request.");
        });
    });

    // Handle unfollow button click
    $(document).on('click', '#unfollow-btn', function() {
        const userId = $(this).data('userid');
        $.post('unfollow.php', { friend_id: userId }, function(response) {
            const result = JSON.parse(response);
            alert(result.message);
            if (result.status === "success") {
                $('#unfollow-btn').replaceWith("<button id='follow-btn' data-userid='" + userId + "'>Follow</button>");
            }
        }).fail(function() {
            alert("An error occurred while trying to unfollow the user.");
        });
    });
});