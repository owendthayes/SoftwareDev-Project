<?php
session_start();
include 'server_connection.php';
$connection = connect_to_database();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
    $username = $_SESSION['username'];
    $sentTo = $_POST['sentTo'];

    // SQL query to update the is_read status
    $query = "UPDATE messages SET is_read = 1 WHERE sentTo = ? AND is_read = 0";
    $stmt = mysqli_prepare($connection, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $sentTo);
        mysqli_stmt_execute($stmt);
        if (mysqli_stmt_affected_rows($stmt) > 0) {
            echo "Message status updated successfully.";
        } else {
            echo "No messages to update.";
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "Error preparing statement: " . mysqli_error($connection);
    }
} else {
    echo "User is not logged in or bad request.";
}

mysqli_close($connection);
?>
