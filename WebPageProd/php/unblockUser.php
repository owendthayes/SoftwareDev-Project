<?php
session_start();
include 'server_connection.php'; // Make sure this path is correct
$connection = connect_to_database();

// Check if the user is logged in and the 'unblocked' POST variable is set
if (isset($_SESSION['username']) && isset($_POST['unblocked'])) {
    $blocker = $_SESSION['username']; // The logged-in user
    $unblocked = $_POST['unblocked']; // The user to unblock

    // Prepare a statement to delete the block entry
    $unblockQuery = "DELETE FROM block WHERE blocker = ? AND blocked = ?";
    $unblockStmt = mysqli_prepare($connection, $unblockQuery);
    
    // Check if the statement was prepared successfully
    if ($unblockStmt) {
        mysqli_stmt_bind_param($unblockStmt, "ss", $blocker, $unblocked);
        $success = mysqli_stmt_execute($unblockStmt);
        mysqli_stmt_close($unblockStmt);
        
        // Check if the unblock was successful
        if ($success) {
            // Redirect to the profile or output success message
            $_SESSION['unblock_success_message'] = "User successfully unblocked.";
            //header("Location: profile.php?user_id=" . urlencode($unblocked));
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit;
        } else {
            // Handle errors, such as the user not being in the block list
            $_SESSION['unblock_error_message'] = "Could not unblock user.";
            // header("Location: profile.php?user_id=" . urlencode($unblocked));
            //header("Location: " . $_SERVER['HTTP_REFERER']);
            //header("Location: profile.php");
            exit;
        }
    } else {
        // Handle statement preparation errors
        echo "Error preparing statement: " . mysqli_error($connection);
    }
    
    // Close the database connection
    mysqli_close($connection);
} else {
    // Handle invalid requests
    echo "Invalid request.";
}
?>
