<?php
include 'server_connection.php';
session_start();
$connection = connect_to_database();

// Check if the search term is set
if (isset($_SESSION['username'])) {
    if (isset($_GET['searchTerm'])) {
        $searchTerm = mysqli_real_escape_string($connection, $_GET['searchTerm']);
        $username = $_SESSION['username'];
        // Prepare the SELECT statement to find users matching the search term
        //$query = "SELECT username FROM profile WHERE username LIKE CONCAT('%', ?, '%')";

        $query = "SELECT DISTINCT following 
            FROM follow WHERE follower = ?  AND following IN (
                SELECT follower FROM follow WHERE following = ? AND follower IN (
                    SELECT following FROM follow WHERE follower = ?))
            AND following LIKE CONCAT('%', ?, '%')";

        $stmt = mysqli_prepare($connection, $query);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssss", $username, $username, $username, $searchTerm);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            $users = array();
            while ($row = mysqli_fetch_assoc($result)) {
                $users[] = $row["following"];
            }

            mysqli_stmt_close($stmt);
            // Return the search results as JSON
            echo json_encode($users);
        } else {
            echo "Error preparing statement: " . mysqli_error($connection);
        }
    } else {
        echo "No search term provided.";
    }
} else {
    echo "User not logged in.";
}

mysqli_close($connection);
