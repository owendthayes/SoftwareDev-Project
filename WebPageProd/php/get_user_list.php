<?php
session_start();
include 'server_connection.php';
$connection = connect_to_database();

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
    $username = $_SESSION['username']; // The logged-in user's username

    // SQL query to select distinct users who have conversed with the logged-in user
    $query = "SELECT 
    CASE 
        WHEN c.user_1 = ? THEN c.user_2 
        ELSE c.user_1 
    END AS chat_partner, MAX(m.timestamp) AS latest_timestamp FROM chat c
        INNER JOIN messages m ON c.chatID = m.chatID
            WHERE c.user_1 = ? OR c.user_2 = ? GROUP BY 
                CASE 
                    WHEN c.user_1 = ? THEN c.user_2 
                     ELSE c.user_1 
                END;";

    $stmt = mysqli_prepare($connection, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssss", $username, $username, $username, $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $chatPartners = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $chatPartners[] = $row['chat_partner'];
        }

        echo json_encode($chatPartners);
    } else {
        echo "Error: " . mysqli_error($connection);
    }

    mysqli_close($connection);
} else {
    echo "User not logged in";
}
