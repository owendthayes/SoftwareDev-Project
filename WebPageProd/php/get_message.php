<?php

session_start();
include 'server_connection.php';
include 'getChatId.php';
$connection = connect_to_database();

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
    $username = $_SESSION['username'];
    $sentTo = $_POST['sentTo'];
    $output = "";

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