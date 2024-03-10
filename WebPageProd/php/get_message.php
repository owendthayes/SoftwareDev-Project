<?php

session_start();
include 'server_connection.php';
include 'getChatId.php';
$connection = connect_to_database();

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
    $username = $_SESSION['username'];
    $sentTo = $_POST['sentTo'];
    $output = "";

    $chatID = getChatId($username, $sentTo, $connection);

    $query = "SELECT * FROM messages WHERE chatID = ? ORDER BY timestamp";
    $stmt = mysqli_prepare($connection, $query);

    if($stmt){
        mysqli_stmt_bind_param($stmt, "s", $chatID);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            if($row['sender'] == $username){
                $output .= '<div class="message my-message">
                                <img src="Images/defaultAvatar.png" alt="My Profile" class="profile-pic">
                                <p class="message-content">'. $row['content_text'] .'</p>
                            </div>';
            }
            else{
                $output .= '<div class="message their-message">
                                <div class="theirMessage">
                                    <img src="Images/defaultAvatar.png" alt="User Profile" class="profile-pic">
                                    <p class="message-content">'. $row['content_text'] .'</p>
                                    <p class="messageDate">'. $row['timestamp'] .'</p>
                                </div>
                            </div>';
            }
        }
        echo $output;
    }else {
        echo "Error: " . mysqli_error($connection);
    }
}
else{
    echo "User is not logged in or bad request.";
}
mysqli_close($connection);
?>