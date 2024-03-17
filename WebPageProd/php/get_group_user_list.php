<?php
session_start();
include 'server_connection.php';
$connection = connect_to_database();

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
    $username = $_SESSION['username']; // The logged-in user's username
    $groupID = mysqli_real_escape_string($connection, $_GET['groupid']);

    // SQL query to select distinct users who have conversed with the logged-in user
    $query = "SELECT DISTINCT username FROM group_participants WHERE groupid = ?";

    $stmt = mysqli_prepare($connection, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $groupID);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $chatPartners = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $chatPartners[] = $row;
        }

        echo json_encode($chatPartners);
    } else {
        echo "Error: " . mysqli_error($connection);
    }

    mysqli_close($connection);
} else {
    echo "User not logged in";
}
