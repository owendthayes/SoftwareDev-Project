<?php
include 'server_connection.php';
include 'getChatID.php';
$connection = connect_to_database();
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
    $username = $_SESSION['username']; // The logged-in user's username
    $groupID = $_POST['groupid']; // The group to which the message is sent
    $message = mysqli_real_escape_string($connection, $_POST['message']);
    $messageID = hexdec(uniqid());

    // Add the current time and date
    $sendTime = date('H:i:s');
    $sendDate = date('Y-m-d');

    // Set the initial value of is_read to 0 for unread messages
    $isRead = 0;

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

    $query = "INSERT INTO messages (messageid, sender, groupid, content_text, timestamp) VALUES (?, ?, ?, ?, NOW())";
    $stmt = mysqli_prepare($connection, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssis", $messageID, $username, $groupID, $message);
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
