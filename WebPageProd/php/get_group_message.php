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

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $groupID);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            if ($row['sender'] == $username) {
                $output .= '<div class="message my-message">
                                    <img src="Images/defaultAvatar.png" alt="My Profile" class="profile-pic">
                                    <p class="message-content">' . $row['content_text'] . '</p>
                                </div>';
            } else {
                $output .= '<div class="message their-message">
                                    <div class="theirMessage">
                                        <img src="Images/defaultAvatar.png" alt="User Profile" class="profile-pic">
                                        <div class="message-content-container">
                                        <span class="user-name">'. $row['sender'] .'</span>
                                        <p class="message-content">' . $row['content_text'] . '</p>
                                        </div>
                                        <p class="messageDate">' . $row['timestamp'] . '</p>
                                    </div>
                                </div>';
            }
        }
        echo $output;
    } else {
        return "Error: " . mysqli_error($connection);
    }
    mysqli_stmt_close($stmt);

    // Return chatID
    return $output;
} else {
    return "Error preparing statement: " . mysqli_error($connection);
}
?>