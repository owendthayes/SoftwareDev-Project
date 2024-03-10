<?php
session_start();
include 'server_connection.php'; // Adjust path as needed

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_SESSION['username']) && isset($_POST['postId']) && isset($_POST['comment'])) {
    $connection = connect_to_database();
    $username = $_SESSION['username'];
    $postId = $_POST['postId'];
    $comment = $_POST['comment'];

    // Insert the comment into the database
    $query = "INSERT INTO comments (username, postid, content) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "sis", $username, $postId, $comment);
    mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);
    mysqli_close($connection);

    echo "Comment inserted successfully!";
} else {
    echo "Error: Invalid request!";
}
?>
