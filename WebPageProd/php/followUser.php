<?php
include 'server_connection.php';
session_start(); // Ensure the session is started

// Check if the logged-in username and the username to follow are set
if (isset($_SESSION['username']) && isset($_POST['username'])) {
    $follower = $_SESSION['username'];
    $following = $_POST['username'];
    $connection = connect_to_database();

    // Check if the user is not trying to follow themselves
    if ($follower !== $following) {
        // Prepare the statement to insert the follow relationship
        $stmt = $connection->prepare("INSERT INTO follow (following, follower) VALUES (?, ?)");
        $stmt->bind_param("ss", $following, $follower);
        $stmt->execute();
        $stmt->close();

        // Prepare the statement to insert the notification
        $notificationQuery = "INSERT INTO notifications (recipient_username, sender_username, activity_type, object_type, content) 
                              VALUES (?, ?, 'follow', 'user', ?)";
        $notificationStmt = mysqli_prepare($connection, $notificationQuery);
        $content = $follower . ' started following you.';
        mysqli_stmt_bind_param($notificationStmt, "sss", $following, $follower, $content);
        mysqli_stmt_execute($notificationStmt);
        mysqli_stmt_close($notificationStmt);

        // Close the connection
        $connection->close();
        echo "Followed successfully";
    } else {
        echo "Error: You cannot follow yourself.";
    }
} else {
    // Return error response if session or username is not set
    echo "Error: Session or username not set";
}
?>
