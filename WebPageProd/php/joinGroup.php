<?php
session_start();
include 'server_connection.php'; // Adjust the path as necessary

// Ensure that only logged-in users can perform actions
if (!isset($_SESSION['username'])) {
    // Redirect to login page or show an error message
    echo "You must be logged in to perform this action.";
    exit;
}

$connection = connect_to_database();

// Extract information from POST request
$groupid = $_POST['groupid'] ?? null;
$username = $_POST['username'] ?? null;
$action = $_POST['action'] ?? null;

// Make sure the action is 'join'
if ($action == 'join' && $groupid && $username) {
    // Prepare a statement to insert the new group member
    $query = "INSERT INTO group_participants (groupid, username, gpermissions, fpermissions) VALUES (?, ?, 'member', 'viewer')";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "is", $groupid, $username);
    
    // Execute the statement and check if it was successful
    if (mysqli_stmt_execute($stmt)) {
        echo "You have successfully joined the group!";
        header("Location: " . $_SERVER['HTTP_REFERER']);
    } else {
        echo "Error: Could not join the group.";
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo "Invalid request.";
}

mysqli_close($connection);

// Redirect back to the group view page or display a message
// header('Location: groupView2.php?groupid=' . $groupid);
// exit;
?>
