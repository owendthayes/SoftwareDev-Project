<?php
session_start();
include 'server_connection.php';
$connection = connect_to_database();

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
    $username = $_SESSION['username'];
    $chatPartner = $_GET['chatPartner']; // The chat partner's username

    // Query to fetch messages for the chat
    $query = "SELECT * FROM messages WHERE (username = ? AND sentTo = ?) OR (username = ? AND sender = ?) ORDER BY sendDate ASC, sendTime ASC";
    $stmt = mysqli_prepare($connection, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssss", $username, $chatPartner, $chatPartner, $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $messages = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $messages[] = $row;
        }

        echo json_encode($messages);
    } else {
        echo "Error: " . mysqli_error($connection);
    }

    mysqli_close($connection);
} else {
    echo "User not logged in";
}
?>
