<?php
session_start();
include 'server_connection.php';
$connection = connect_to_database();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
    $username = $_SESSION['username']; // The logged-in user's username
    $sentTo = $_POST['sentTo']; // The user to whom the message is sent
    $message = mysqli_real_escape_string($connection, $_POST['message']);
    $chatID = $_POST['chatID']; // This should be the chat ID

    // Add the current time and date
    $sendTime = date('H:i:s');
    $sendDate = date('Y-m-d');

    // Set the initial value of is_read to 0 for unread messages
    $isRead = 0;

    $query = "INSERT INTO messages (chatID, username, sender, sentTo, sendTime, sendDate, message, is_read) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($connection, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "issssssi", $chatID, $username, $username, $sentTo, $sendTime, $sendDate, $message, $isRead);
        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_affected_rows($stmt) == 1) {
            echo "Message sent successfully.";
        } else {
            echo "Error sending message.";
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
