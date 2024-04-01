<?php
session_start();
include 'server_connection.php'; // Adjust the path as necessary

// Check if the user is logged in and a file has been uploaded
if (!isset($_SESSION['username']) || !isset($_FILES['fileToUpload'])) {
    die("You must be logged in and a file must be selected.");
}

$connection = connect_to_database();

// Get group ID from the form input
$groupid = $_POST['groupid'] ?? null;

// Check if file was uploaded without errors
if ($_FILES['fileToUpload']['error'] === UPLOAD_ERR_OK) {
    // Process your file here
    $tmpName = $_FILES['fileToUpload']['tmp_name'];
    $fileName = basename($_FILES['fileToUpload']['name']);
    $fileSize = $_FILES['fileToUpload']['size'];
    $fileType = $_FILES['fileToUpload']['type'];

    // Specify where you want to store files
    $uploadDir = "../uploads/"; // Ensure this directory exists and is writable
    $filePath = $uploadDir . $fileName;
    
    // Check if file already exists
    if (file_exists($filePath)) {
        echo "Sorry, file already exists.";
    } elseif (move_uploaded_file($tmpName, $filePath)) {
        // File is uploaded successfully
        // Prepare an insert statement
        $query = "INSERT INTO group_files (groupid, file_name, file_path, uploaded_by, upload_time) VALUES (?, ?, ? ,?, NOW())";
        $stmt = mysqli_prepare($connection, $query);
        mysqli_stmt_bind_param($stmt, "isss", $groupid, $fileName, $filePath, $_SESSION['username']);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "The file ". htmlspecialchars($fileName). " has been uploaded.";
            header("Location: " . $_SERVER['HTTP_REFERER']);
        } else {
            echo "Error uploading file: " . mysqli_error($connection);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
} else {
    echo "Error: " . $_FILES['fileToUpload']['error'];
}

mysqli_close($connection);
?>
