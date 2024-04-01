<?php
session_start();
include 'server_connection.php'; // Adjust the path to your actual server connection script

// Function to save the uploaded image and return the filename
function saveUploadedImage($fileKey, $uploadDir = '../Images/') {
    if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES[$fileKey]['tmp_name'];
        $fileName = time() . '_' . basename($_FILES[$fileKey]['name']);
        $path = $uploadDir . $fileName;
        if (move_uploaded_file($tmpName, $path)) {
            return $fileName;
        }
    }
    return null;
}

// Ensure a user is logged in
if (!isset($_SESSION['username'])) {
    die("You must be logged in to create a group.");
}

$username = $_SESSION['username'];
$connection = connect_to_database();

// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect post data
    $groupName = $_POST['groupname'] ?? null;
    $groupDesc = $_POST['groupdesc'] ?? null;
    $isPrivPub = isset($_POST['type']) && $_POST['type'] === 'private' ? 'private' : 'public';
    $groupImageName = saveUploadedImage('groupdp');

    // Check required fields
    if (!$groupName || !$groupDesc) {
        echo "Group name and description are required.";
        exit;
    }

    if ($groupImageName !== null) {
        $groupImagePath = "Images/" . $groupImageName; // Assuming the Images/ directory is in the root

        // Insert the group into the database
        $insertGroupQuery = "INSERT INTO `groups` (groupname, groupdp, groupdesc, type) VALUES (?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($connection, $insertGroupQuery)) {
            mysqli_stmt_bind_param($stmt, "ssss", $groupName, $groupImagePath, $groupDesc, $isPrivPub);
            mysqli_stmt_execute($stmt);
            $groupId = mysqli_insert_id($connection);
            mysqli_stmt_close($stmt);

            // Add the logged-in user as an admin and editor in the group_participants table
            $insertParticipantQuery = "INSERT INTO group_participants (groupid, username, gpermissions, fpermissions) VALUES (?, ?, 'owner', 'editor')";
            if ($stmt = mysqli_prepare($connection, $insertParticipantQuery)) {
                mysqli_stmt_bind_param($stmt, "is", $groupId, $username);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);

                echo "Group created successfully!";
                header("Location: ../groupFiles.php");
            } else {
                echo "Error: " . mysqli_error($connection);
            }
        } else {
            echo "Error: " . mysqli_error($connection);
        }
    } else {
        echo "Error: File upload failed.";
    }
}

mysqli_close($connection);
?>
