<?php
include 'server_connection.php'; // Adjust path as needed

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['postId'])) {
    session_start();
    if(!isset($_SESSION['username'])) {
        echo json_encode(array("error" => "User not logged in"));
        exit;
    }
    $username = $_SESSION['username']; // The user who likes the post
    $postId = $_POST['postId'];

    $connection = connect_to_database();

    // Start transaction
    mysqli_begin_transaction($connection);

    try {
        // Check if the like already exists
        $like_check_query = "SELECT * FROM likes WHERE username = ? AND postid = ?";
        $stmt_like_check = mysqli_prepare($connection, $like_check_query);
        mysqli_stmt_bind_param($stmt_like_check, "si", $username, $postId);
        mysqli_stmt_execute($stmt_like_check);
        $result_like_check = mysqli_stmt_get_result($stmt_like_check);

        if (mysqli_num_rows($result_like_check) > 0) {
            // If the like exists, remove it
            $query = "DELETE FROM likes WHERE username = ? AND postid = ?";
            $like_action = 'unliked';
        } else {
            // If the like does not exist, add it
            $query = "INSERT INTO likes (username, postid) VALUES (?, ?)";
            $like_action = 'liked';

            // Retrieve the username of the post owner to set as recipient of the notification
            $queryPostOwner = "SELECT username FROM posts WHERE postid = ?";
            $stmtPostOwner = mysqli_prepare($connection, $queryPostOwner);
            mysqli_stmt_bind_param($stmtPostOwner, "i", $postId);
            mysqli_stmt_execute($stmtPostOwner);
            $resultPostOwner = mysqli_stmt_get_result($stmtPostOwner);
            $postOwnerRow = mysqli_fetch_assoc($resultPostOwner);

            if ($postOwnerRow) {
                $recipientUsername = $postOwnerRow['username']; // Recipient of the notification

                // Insert notification for the post owner about the like
                $notificationQuery = "INSERT INTO notifications (recipient_username, sender_username, activity_type, object_type, object_id, content) VALUES (?, ?, 'like', 'post', ?, 'Your post was liked')";
                $notificationStmt = mysqli_prepare($connection, $notificationQuery);
                mysqli_stmt_bind_param($notificationStmt, "ssi", $recipientUsername, $username, $postId);
                mysqli_stmt_execute($notificationStmt);
            }
        }

        $stmt = mysqli_prepare($connection, $query);
        mysqli_stmt_bind_param($stmt, "si", $username, $postId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt_like_check);

        // Commit the transaction
        mysqli_commit($connection);

        // Get the updated like count
        $like_count_query = "SELECT COUNT(*) AS likeCount FROM likes WHERE postid = ?";
        $stmt_like_count = mysqli_prepare($connection, $like_count_query);
        mysqli_stmt_bind_param($stmt_like_count, "i", $postId);
        mysqli_stmt_execute($stmt_like_count);
        $result_like_count = mysqli_stmt_get_result($stmt_like_count);
        $row_like_count = mysqli_fetch_assoc($result_like_count);
        $likeCount = $row_like_count['likeCount'];

        mysqli_stmt_close($stmt_like_count);
        mysqli_close($connection);

        // Respond with the like count and the action taken
        echo json_encode(array("likeCount" => $likeCount, "action" => $like_action));
    } catch (Exception $e) {
        // Rollback the transaction in case of error
        mysqli_rollback($connection);
        echo json_encode(array("error" => "An error occurred"));
    }
} else {
    echo "Error: Invalid request!";
}
?>
