<?php
include 'server_connection.php'; // Adjust path as needed

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['postId'])) {
    $postId = $_GET['postId'];

    // Query to retrieve comments for the specific post
    $connection = connect_to_database();
    $query = "SELECT * FROM comments WHERE postid = ? ORDER BY timestamp DESC";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "i", $postId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $comments = "";
    while ($row = mysqli_fetch_assoc($result)) {
        $comments .= '<div class="CommentInfo">';
        $comments .= '<img class="CommentAvatar" src="Images/defaultavatar.png">';
        $comments .= '<p class="commentUsername">' . $row['username'] . '</p>';
        $comments .= '</div>';
        $comments .= '<div class="Comment">' . $row['content'] . '</div>';
    }

    mysqli_stmt_close($stmt);
    mysqli_close($connection);

    echo $comments;
} else {
    echo "Error: Invalid request!";
}
?>
