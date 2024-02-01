<?php
session_start();
include 'server_connection.php';
$connection = connect_to_database();

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
    $username = $_SESSION['username']; // The logged-in user's username

    // SQL query to select distinct users who have conversed with the logged-in user
    $query = "SELECT DISTINCT IF(username = ?, sentTo, username) AS chatPartner, 
              MAX(sendDate) AS latestDate, MAX(sendTime) AS latestTime,
              MAX(is_read) AS isRead
              FROM messages
              WHERE username = ? OR sentTo = ?
              GROUP BY chatPartner
              ORDER BY latestDate DESC, latestTime DESC";

    $stmt = mysqli_prepare($connection, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sss", $username, $username, $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $chatPartners = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $chatPartners[] = [
                'username' => $row['chatPartner'],
                'is_read' => $row['isRead']
            ];
        }
        
        echo json_encode($chatPartners);
    } else {
        echo "Error: " . mysqli_error($connection);
    }

    mysqli_close($connection);
} else {
    echo "User not logged in";
}
?>
