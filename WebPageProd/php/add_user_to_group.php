<?php
session_start();
include 'server_connection.php';

// Check if the user is logged in and has the permission to add users
if (!isset($_SESSION['username'])) {
    echo "You must be logged in.";
    exit;
}

// Connect to the database
$connection = connect_to_database();

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $groupid = $_POST['groupid'] ?? null;
    $userSearch = trim($_POST['userSearch'] ?? '');

    // Check if the user exists
    $userCheckQuery = "SELECT * FROM profile WHERE username = ?";
    $userCheckStmt = mysqli_prepare($connection, $userCheckQuery);
    mysqli_stmt_bind_param($userCheckStmt, 's', $userSearch);
    mysqli_stmt_execute($userCheckStmt);
    $userCheckResult = mysqli_stmt_get_result($userCheckStmt);
    $userExists = mysqli_num_rows($userCheckResult) > 0;
    mysqli_stmt_close($userCheckStmt);

    if ($userExists) {
        // Check if user is already in the group
        $memberCheckQuery = "SELECT * FROM group_participants WHERE groupid = ? AND username = ?";
        $memberCheckStmt = mysqli_prepare($connection, $memberCheckQuery);
        mysqli_stmt_bind_param($memberCheckStmt, 'is', $groupid, $userSearch);
        mysqli_stmt_execute($memberCheckStmt);
        $memberCheckResult = mysqli_stmt_get_result($memberCheckStmt);
        $isMember = mysqli_num_rows($memberCheckResult) > 0;
        mysqli_stmt_close($memberCheckStmt);

        if ($isMember) {
            // Return JSON response indicating that the user is already a member
            echo json_encode(array("success" => false, "error" => "User is already a member of the group."));
        } else {
            // Begin transaction
            mysqli_begin_transaction($connection);
            try {
                // Add the user to group_participants
                $addUserQuery = "INSERT INTO group_participants (groupid, username, gpermissions, fpermissions) VALUES (?, ?, 'member', 'viewer')";
                $addUserStmt = mysqli_prepare($connection, $addUserQuery);
                mysqli_stmt_bind_param($addUserStmt, 'is', $groupid, $userSearch);
                $addResult = mysqli_stmt_execute($addUserStmt);
                mysqli_stmt_close($addUserStmt);

                if ($addResult) {
                    // Commit transaction
                    mysqli_commit($connection);
                    // Return JSON response indicating success
                    echo json_encode(array("success" => true, "message" => "User added successfully."));
                } else {
                    throw new Exception("Error adding user to the group: " . mysqli_error($connection));
                }
            } catch (Exception $e) {
                // Rollback transaction and return error message
                mysqli_rollback($connection);
                echo json_encode(array("success" => false, "error" => $e->getMessage()));
            }
        }
    } else {
        // Return JSON response indicating that the user does not exist
        echo json_encode(array("success" => false, "error" => "User does not exist."));
    }
}

mysqli_close($connection);
?>
