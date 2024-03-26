<?php
session_start();
include 'server_connection.php';
$connection = connect_to_database();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    die("You must be logged in to post.");
}

// Function to save the uploaded image and get the path
function saveUploadedImage($fileKey, $uploadDir = '../Images/') {
    if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES[$fileKey]['tmp_name'];
        $fileName = basename($_FILES[$fileKey]['name']);
        $path = $uploadDir . $fileName;
        if (move_uploaded_file($tmpName, $path)) {
            return $path;
        }
    }
    return null;
}

// Function to handle inserting the post
function insertPostWithImage($connection, $username, $caption, $imagePath, $groupid) {
    $query = "INSERT INTO posts (groupid, content_image, username, created_at, caption) VALUES (?,?, ?, NOW(), ?)";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "isss", $groupid, $imagePath, $username, $caption);

    // Execute the statement
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $result;
}

// Handle the POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $caption = $_POST['aboutMe'] ?? ''; // Get the caption
    $groupid = $_POST['groupid'];
    $imagePath = saveUploadedImage('profileImage'); // Save the uploaded image and get the path

    if ($imagePath !== null) {
        $result = insertPostWithImage($connection, $_SESSION['username'], $caption, $imagePath, $groupid);
        if ($result) {
            // header("Location: ../feed.php"); // Redirect to feed.html if successful
            header("Location: " . $_SERVER['HTTP_REFERER']);
        } else {
            echo 'Error: ' . mysqli_error($connection);
        }
    } else {
        echo 'Error: Invalid file upload.';
    }
}

mysqli_close($connection); // Close the database connection
?>
