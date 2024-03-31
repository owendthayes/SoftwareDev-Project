<?php
session_start();
include 'server_connection.php'; // Make sure you have the correct path to your database connection file

// Function to check if there is only one admin in the group
function isSoleAdmin($groupid, $username) {
    $connection = connect_to_database();
    // Check the number of admins other than the user being removed
    $stmt = mysqli_prepare($connection, "SELECT COUNT(*) as admin_count FROM group_participants WHERE groupid = ? AND gpermissions = 'owner' AND username != ?");
    mysqli_stmt_bind_param($stmt, 'is', $groupid, $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    mysqli_close($connection);
    // If there are no other admins, then this user is the sole admin
    return $data['admin_count'] == 0;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve the form data
    $groupid = $_POST['groupid'];
    $username = $_POST['username'];

    // Connect to the database
    $connection = connect_to_database();

    // Check if the current user is the sole admin
    if (isSoleAdmin($groupid, $username)) {
        echo "Cannot delete sole owner. You are the sole admin of the group.";
        mysqli_close($connection);
        exit;
    }

    // Prepare the SQL statement
    $stmt = mysqli_prepare($connection, "DELETE FROM group_participants WHERE groupid = ? AND username = ?");

    // Bind the parameters to the SQL statement
    mysqli_stmt_bind_param($stmt, 'is', $groupid, $username);

    // Execute the SQL statement
    $result = mysqli_stmt_execute($stmt);

    // Check the result and return an appropriate message
    if ($result) {
        header("Location: " . $_SERVER['HTTP_REFERER']); // Redirect back to the previous page
    } else {
        echo "There was an error removing the user {$username} from the group.";
    }

    // Close the statement and the connection
    mysqli_stmt_close($stmt);
    mysqli_close($connection);
} else {
    echo "This script only handles POST requests.";
}
?>
