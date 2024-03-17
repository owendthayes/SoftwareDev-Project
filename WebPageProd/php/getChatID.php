<?php

function getChatId($user1, $user2, $connection)
{
    // Sanitize user inputs
    $user1 = mysqli_real_escape_string($connection, $user1);
    $user2 = mysqli_real_escape_string($connection, $user2);

    $chatId = hexdec(uniqid());

    // Check if the chat already exists
    $query = "SELECT chatID FROM chat 
                WHERE (user_1 = ? AND user_2 = ? ) 
                OR (user_1 = ? AND user_2 = ?);";
    $stmt = mysqli_prepare($connection, $query);

    if ($stmt) {

        mysqli_stmt_bind_param($stmt, "ssss", $user1, $user2, $user2, $user1);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            // Chat already exists
            $row = mysqli_fetch_assoc($result);
            $chatId = $row['chatID'];
        } else {
            // Create a new chat
            $query = "INSERT INTO chat (chatid, user_1, user_2) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($connection, $query);
            if ($stmt) {

                mysqli_stmt_bind_param($stmt, "sss", $chatId, $user1, $user2);
                mysqli_stmt_execute($stmt);

                // Get the chat ID
                $chatId = mysqli_insert_id($connection);
            } else {
                return "Error preparing statement: " . mysqli_error($connection);
            }
        }
        mysqli_stmt_close($stmt);

        // Return chatID
        return $chatId;
    } else {
        return "Error preparing statement: " . mysqli_error($connection);
    }
}
?>