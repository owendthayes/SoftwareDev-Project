<?php
session_start();
include 'server_connection.php'; // Adjust the path as necessary

// Check if the user is logged in and is an admin of the group
if (isset($_SESSION['username']) && isset($_POST['groupid'])) {
    $groupid = $_POST['groupid'];
    $username = $_SESSION['username'];

    $connection = connect_to_database();

    // First, check if the user is an admin of the group
    $adminCheckQuery = "SELECT gpermissions FROM group_participants WHERE groupid = ? AND username = ? AND gpermissions = 'admin'";
    $stmt = mysqli_prepare($connection, $adminCheckQuery);
    mysqli_stmt_bind_param($stmt, "is", $groupid, $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        // The user is an admin, so we can proceed with deletion

        // Begin transaction
        mysqli_begin_transaction($connection);

        // Delete all participants from group_participants
        $deleteParticipantsQuery = "DELETE FROM group_participants WHERE groupid = ?";
        $stmt = mysqli_prepare($connection, $deleteParticipantsQuery);
        mysqli_stmt_bind_param($stmt, "i", $groupid);
        mysqli_stmt_execute($stmt);

        // Delete the group from groups
        $deleteGroupQuery = "DELETE FROM `groups` WHERE groupid = ?";
        $stmt = mysqli_prepare($connection, $deleteGroupQuery);
        mysqli_stmt_bind_param($stmt, "i", $groupid);
        mysqli_stmt_execute($stmt);

        // Check for errors and commit transaction
        if (!mysqli_error($connection)) {
            mysqli_commit($connection);
            echo "Group deleted successfully.";
            header("Location: ../groupFiles.php");
            // Redirect or perform other success actions
        } else {
            mysqli_rollback($connection);
            echo "Error deleting group.";
            // Handle errors
        }
    } else {
        echo "You do not have permission to delete this group.";
        // Handle lack of permissions
    }

    // Close statement and connection
    mysqli_stmt_close($stmt);
    mysqli_close($connection);
} else {
    echo "You must be logged in as an admin to delete a group.";
    // Handle unauthorized access
}
?>
