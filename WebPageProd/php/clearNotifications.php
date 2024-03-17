<?php
session_start();
include 'server_connection.php';

if (isset($_SESSION['username'])) {
    $loggedInUser = $_SESSION['username'];
    $connection = connect_to_database();

    // Prepare the SQL query to delete notifications for the logged-in user
    $query = "DELETE FROM notifications WHERE recipient_username = ?";
    $stmt = mysqli_prepare($connection, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $loggedInUser);
        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_affected_rows($stmt) > 0) {
            echo "Notifications cleared successfully.";
            header("Location: " . $_SERVER['HTTP_REFERER']);
        } else {
            echo "No notifications to clear or error occurred.";
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "Error preparing statement: " . mysqli_error($connection);
    }
    mysqli_close($connection);
} else {
    echo "User is not logged in.";
}
?>
