<?php
session_start();
include 'server_connection.php';
$connection = connect_to_database();


// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    die("You must be logged in to post.");
}


// Function to handle inserting the post
function insertTextPost($connection, $username, $textPost, $caption) {
    // Prepare a query for insertion
    $query = "INSERT INTO posts (content_text, username, created_at, caption) VALUES (?, ?, NOW(), ?)";
    
    // Create a prepared statement
    $stmt = mysqli_prepare($connection, $query);
    
    // Bind the variables to the prepared statement as parameters
    mysqli_stmt_bind_param($stmt, "sss", $textPost, $username, $caption);
    
    // Execute the statement and check if successful
    $result = mysqli_stmt_execute($stmt);
    
    // Close statement
    mysqli_stmt_close($stmt);
    
    return $result;
}

// Handle the POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $textPost = $_POST['aboutMe'] ?? ''; // Get the text content
    $caption = $_POST['fullName'] ?? ''; // Get the caption

    $result = insertTextPost($connection, $_SESSION['username'], $textPost, $caption);
    if ($result) {
        // echo 'Post successfully saved.';
        header("Location: ../feed.php");
    } else {
        echo 'Error: ' . mysqli_error($connection);
    }
}

mysqli_close($connection); // Close the database connection
?>
