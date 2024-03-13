<?php
session_start();
include 'server_connection.php';

// Function to save the uploaded image and return the path
function saveUploadedImage($fileKey, $uploadDir = '../Images/') {
    if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES[$fileKey]['tmp_name'];
        $fileName = time() . '_' . basename($_FILES[$fileKey]['name']); // Prefix the file name with the current timestamp to avoid overwriting files
        $path = $uploadDir . $fileName;
        if (move_uploaded_file($tmpName, $path)) {
            return $fileName; // Return just the file name as the path is fixed
        }
    }
    return null;
}

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    die("You must be logged in to create a group.");
}

$username = $_SESSION['username']; // The logged-in user's username

// Function to save the uploaded image and return the path...
// Assume this function exists as per your previous code

$connection = connect_to_database();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect post data
    $groupName = $_POST['groupname'] ?? '';
    $groupDesc = $_POST['groupdesc'] ?? '';
    $isPrivPub = isset($_POST['is_privpub']) && $_POST['is_privpub'] === 'private' ? 'private' : 'public';
    $groupImageName = saveUploadedImage('groupdp');

    if ($groupImageName !== null) {
        $groupImagePath = "../Images/" . $groupImageName;

        // Start transaction
        mysqli_begin_transaction($connection);

        // Insert the group into the groups table
        $insertGroupQuery = "INSERT INTO groups (groupname, groupdp, groupdesc, is_privpub, host) VALUES (?, ?, ?, ?, ?)";
        $groupStmt = mysqli_prepare($connection, $insertGroupQuery);
        mysqli_stmt_bind_param($groupStmt, "sssss", $groupName, $groupImagePath, $groupDesc, $isPrivPub, $username);
        $groupExecuted = mysqli_stmt_execute($groupStmt);
        $groupId = mysqli_stmt_insert_id($groupStmt); // Retrieve the last inserted group id
        mysqli_stmt_close($groupStmt);

        if ($groupExecuted && $groupId) {
            // Insert the user as a participant with admin and editor permissions
            $insertParticipantQuery = "INSERT INTO group_participants (groupid, username, gpermissions, fpermissions) VALUES (?, ?, 'admin', 'editor')";
            $participantStmt = mysqli_prepare($connection, $insertParticipantQuery);
            mysqli_stmt_bind_param($participantStmt, "is", $groupId, $username);
            $participantExecuted = mysqli_stmt_execute($participantStmt);
            mysqli_stmt_close($participantStmt);

            if ($participantExecuted) {
                // Commit the transaction if both queries are successful
                mysqli_commit($connection);
                echo "Group created successfully, and the creator has been added as an admin and editor.";
                // Redirect or perform other success actions
            } else {
                // Rollback the transaction if inserting the participant failed
                mysqli_rollback($connection);
                echo "Error adding creator as a participant: " . mysqli_error($connection);
            }
        } else {
            // Rollback the transaction if inserting the group failed
            mysqli_rollback($connection);
            echo "Error creating group: " . mysqli_error($connection);
        }
    } else {
        echo "Error: Invalid file upload.";
    }
}

mysqli_close($connection);
?>
