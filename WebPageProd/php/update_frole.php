<?php
session_start();
include 'server_connection.php'; // Make sure you have the correct path to your database connection file

//Function to check if there is only one admin in the group
function isSoleAdmin($groupid, $username) {
    $connection = connect_to_database();
    $stmt = mysqli_prepare($connection, "SELECT COUNT(*) as admin_count FROM group_participants WHERE groupid = ? AND fpermissions = 'editor'");
    mysqli_stmt_bind_param($stmt, 'i', $groupid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    mysqli_close($connection);
    return $data['admin_count'] == 1;
}

// Function to update the user role in the group_participants table
function updateUserRole($groupid, $username, $role) {
    // Connect to the database
    $connection = connect_to_database();
    
    // Check if the current user is the sole admin
    if ($role !== 'editor' && isSoleAdmin($groupid, $username)) {
        echo "Cannot change role. You are the sole editor of the group.";
        mysqli_close($connection);
        exit;
    }
    
    // Prepare the SQL statement
    $stmt = mysqli_prepare($connection, "UPDATE group_participants SET fpermissions = ? WHERE groupid = ? AND username = ?");
    
    // Bind the parameters to the SQL statement
    mysqli_stmt_bind_param($stmt, 'sis', $role, $groupid, $username);
    
    // Execute the SQL statement
    $result = mysqli_stmt_execute($stmt);
    
    // Close the statement and the connection
    mysqli_stmt_close($stmt);
    mysqli_close($connection);

    // Return the result of the execution
    return $result;
}

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve the form data
    $groupid = $_POST['groupid'];
    $username = $_POST['username'];
    $role = $_POST['frole'];

    // Call the function to update the user role
    $result = updateUserRole($groupid, $username, $role);

    // Check the result and return an appropriate message
    if ($result) {
        header("Location: " . $_SERVER['HTTP_REFERER']);
    } else {
        echo "There was an error updating the role of user {$username}.";
    }
} else {
    echo "This script only handles POST requests.";
}
?>
