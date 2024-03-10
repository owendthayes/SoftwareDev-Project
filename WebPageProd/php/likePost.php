<?php
include 'server_connection.php'; // Adjust path as needed

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['postId'])) {
    session_start();
    $username = $_SESSION['username'];
    $postId = $_POST['postId'];

    $connection = connect_to_database();

    // Check if the like already exists
    $like_check_query = "SELECT * FROM likes WHERE username = ? AND postid = ?";
    $stmt_like_check = mysqli_prepare($connection, $like_check_query);
    mysqli_stmt_bind_param($stmt_like_check, "si", $username, $postId);
    mysqli_stmt_execute($stmt_like_check);
    $result_like_check = mysqli_stmt_get_result($stmt_like_check);
    
    if (mysqli_num_rows($result_like_check) > 0) {
        // If the like exists, remove it
        $query = "DELETE FROM likes WHERE username = ? AND postid = ?";
        $like_action = 'unliked';
    } else {
        // If the like does not exist, add it
        $query = "INSERT INTO likes (username, postid) VALUES (?, ?)";
        $like_action = 'liked';
    }

    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "si", $username, $postId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt_like_check);
    
    // Get the updated like count
    $like_count_query = "SELECT COUNT(*) AS likeCount FROM likes WHERE postid = ?";
    $stmt_like_count = mysqli_prepare($connection, $like_count_query);
    mysqli_stmt_bind_param($stmt_like_count, "i", $postId);
    mysqli_stmt_execute($stmt_like_count);
    $result_like_count = mysqli_stmt_get_result($stmt_like_count);
    $row_like_count = mysqli_fetch_assoc($result_like_count);
    $likeCount = $row_like_count['likeCount'];

    mysqli_stmt_close($stmt_like_count);
    mysqli_close($connection);

    // Respond with the like count and the action taken
    echo json_encode(array("likeCount" => $likeCount, "action" => $like_action));
} else {
    echo "Error: Invalid request!";
}
?>
