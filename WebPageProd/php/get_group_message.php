<?php
session_start();
include 'server_connection.php';
$connection = connect_to_database();
$groupID = mysqli_real_escape_string($connection, $_POST['groupid']);
$username = $_SESSION['username'];

$query = "SELECT * FROM messages WHERE groupid = ? ORDER BY timestamp";
$stmt = mysqli_prepare($connection, $query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $groupID);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $output = "";

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            if ($row['sender'] == $username) {
                if (strpos($row['content_text'], '../Images') === 0) {
                    // If it's an image, display it as an <img> tag
                    $imagePath = str_replace('../Images', 'Images', $row['content_text']);
                    $output .= '<div class="message my-message">
                                    <div class="myMessage">
                                        <img src="' . $imagePath . '" alt="Image" class="message-image">
                                        <p class="messageDate">' . $row['timestamp'] . '</p>
                                    </div>
                                </div>';
                } else {
                    $output .= '<div class="message my-message">
                                    <img src="Images/defaultAvatar.png" alt="My Profile" class="profile-pic">
                                    <p class="message-content">' . $row['content_text'] . '</p>
                                </div>';
                }
            } else {
                if (strpos($row['content_text'], '../Images') === 0) {
                    // If it's an image, display it as an <img> tag
                    $imagePath = str_replace('../Images', 'Images', $row['content_text']);
                    $output .= '<div class="message their-message">
                                    <div class="theirMessage">
                                        <img src="' . $imagePath . '" alt="Image" class="message-image">
                                        <p class="messageDate">' . $row['timestamp'] . '</p>
                                    </div>
                                </div>';
                } else {
                    $output .= '<div class="message their-message">
                                    <div class="theirMessage">
                                        <img src="Images/defaultAvatar.png" alt="User Profile" class="profile-pic">
                                        <div class="message-content-container">
                                            <span class="user-name">' . $row['sender'] . '</span>
                                            <p class="message-content">' . $row['content_text'] . '</p>
                                        </div>
                                        <p class="messageDate">' . $row['timestamp'] . '</p>
                                    </div>
                                </div>';
                }
            }
        }
        echo $output;
    } else {
        echo "Error fetching messages.";
    }
} else {
    echo "Error preparing statement: " . mysqli_error($connection);
}

mysqli_close($connection);
?>
