<?php
include 'server_connection.php';
include 'getChatID.php';
$connection = connect_to_database();
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
    $username = $_SESSION['username']; // The logged-in user's username
    $sentTo = $_POST['sentTo']; // The user to who the message is sent
    $message = $_POST['message'];
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
        exit; // Stop further execution if recipient does not exist
    }

    $chatID = getChatId($username, $sentTo, $connection);

    // Check if the message is an image
    if (strpos($message, 'data:image') !== false) {
        // Extract the image data and save it to a file
        $imgData = explode(',', $message)[1];
        $imgData = base64_decode($imgData);
        $imgName = uniqid() . '.png'; // Generate a unique name for the image
        $imgPath = '../Images/' . $imgName; // Path to save the image
        file_put_contents($imgPath, $imgData);

        // Update the message content to store the image file path
        $message = $imgPath;
    }

    $query = "INSERT INTO messages (messageid, sender, chatid, content_text) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($connection, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssss", $messageID, $username, $chatID, $message);
        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_affected_rows($stmt) == 1) {
            // Insert notification for receiving a message
            $notif_query = "INSERT INTO notifications (recipient_username, sender_username, activity_type, object_type, object_id, content) VALUES (?, ?, 'message', 'message', ?, ?)";
            $notif_stmt = mysqli_prepare($connection, $notif_query);
            if ($notif_stmt) {
                mysqli_stmt_bind_param($notif_stmt, "ssis", $sentTo, $username, $messageID, $message);
                mysqli_stmt_execute($notif_stmt);
                mysqli_stmt_close($notif_stmt);
            }

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
