<?php
include 'server_connection.php';
include 'getChatId.php';
$connection = connect_to_database();
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
    $username = $_SESSION['username']; // The logged-in user's username
    $sentTo = $_POST['sentTo']; // The user to whom the message is sent
    $message = mysqli_real_escape_string($connection, $_POST['message']);
    $messageID = hexdec(uniqid());

    // Check if the recipient exists in the profile database
    $query_check_user = "SELECT * FROM profile WHERE username = ?";
    $stmt_check_user = mysqli_prepare($connection, $query_check_user);
    mysqli_stmt_bind_param($stmt_check_user, "s", $sentTo);
    mysqli_stmt_execute($stmt_check_user);
    mysqli_stmt_store_result($stmt_check_user);
    $user_exists = mysqli_stmt_num_rows($stmt_check_user) > 0;
    mysqli_stmt_close($stmt_check_user);

    if (!$user_exists) {
        //echo "Error: Recipient does not exist.";
        exit; // Stop further execution
    }

    $chatID = getChatId($username, $sentTo, $connection);

    // Add the current time and date
    $sendTime = date('H:i:s');
    $sendDate = date('Y-m-d');

    // Set the initial value of is_read to 0 for unread messages
    $isRead = 0;

    $query = "INSERT INTO messages (messageid, sender, chatid, content_text) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($connection, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssss", $messageID, $username, $chatID, $message);
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
