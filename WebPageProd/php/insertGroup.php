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

$connection = connect_to_database();

if (!isset($_SESSION['username'])) {
    die("You must be logged in to create a group.");
}

// Host of the group is the logged-in user
$host = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $groupName = $_POST['groupname'] ?? '';
    $groupDesc = $_POST['groupdesc'] ?? '';
    $isPrivPub = isset($_POST['is_privpub']) && $_POST['is_privpub'] === 'private' ? 'private' : 'public';
    $groupImageName = saveUploadedImage('groupdp');

    if ($groupImageName !== null) {
        $groupImagePath = "../Images/" . $groupImageName; // The path to be stored in the database
        $query = "INSERT INTO groups (groupname, groupdp, groupdesc, is_privpub, host) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($connection, $query);
        mysqli_stmt_bind_param($stmt, "sssss", $groupName, $groupImagePath, $groupDesc, $isPrivPub, $host);

        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ($result) {
            echo "Group created successfully!";
            // Redirect or perform other success actions
        } else {
            echo "Error: " . mysqli_error($connection);
        }
    } else {
        echo 'Error: Invalid file upload.';
    }
}

mysqli_close($connection);
?>
