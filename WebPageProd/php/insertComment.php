<?php
session_start();
include 'server_connection.php'; // Adjust path as needed

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_SESSION['username']) && isset($_POST['postId']) && isset($_POST['comment'])) {
    $connection = connect_to_database();
    $username = $_SESSION['username']; // Sender of the comment
    $postId = $_POST['postId'];
    $comment = $_POST['comment'];

    // Start transaction to ensure both operations are done successfully
    mysqli_begin_transaction($connection);

    try {
        // Insert the comment into the database
        $query = "INSERT INTO comments (username, postid, content) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($connection, $query);
        mysqli_stmt_bind_param($stmt, "sis", $username, $postId, $comment);
        mysqli_stmt_execute($stmt);

        // Retrieve the username of the post owner to set as recipient of the notification
        $queryPostOwner = "SELECT username FROM posts WHERE postid = ?";
        $stmtPostOwner = mysqli_prepare($connection, $queryPostOwner);
        mysqli_stmt_bind_param($stmtPostOwner, "i", $postId);
        mysqli_stmt_execute($stmtPostOwner);
        $resultPostOwner = mysqli_stmt_get_result($stmtPostOwner);
        $postOwnerRow = mysqli_fetch_assoc($resultPostOwner);
        
        if ($postOwnerRow && $username != $postOwnerRow['username']) {
            $recipientUsername = $postOwnerRow['username']; // Recipient of the notification

            // Insert notification for the post owner about the new comment
            $notificationQuery = "INSERT INTO notifications (recipient_username, sender_username, activity_type, object_type, object_id, content) VALUES (?, ?, ?, ?, ?, ?)";
            $notificationStmt = mysqli_prepare($connection, $notificationQuery);
            $activityType = 'comment';
            $objectType = 'post';
            $content = "New comment on your post";
            mysqli_stmt_bind_param($notificationStmt, "ssssis", $recipientUsername, $username, $activityType, $objectType, $postId, $content);
            mysqli_stmt_execute($notificationStmt);
        }

        // Commit transaction if both operations succeed
        mysqli_commit($connection);
        echo "Comment inserted and notification sent successfully!";
    } catch (Exception $e) {
        // Rollback transaction in case of error
        mysqli_rollback($connection);
        echo "Error: " . $e->getMessage();
    }

    mysqli_stmt_close($stmt);
    if (isset($stmtPostOwner)) mysqli_stmt_close($stmtPostOwner);
    if (isset($notificationStmt)) mysqli_stmt_close($notificationStmt);
    mysqli_close($connection);

} else {
    echo "Error: Invalid request!";
}
?>
