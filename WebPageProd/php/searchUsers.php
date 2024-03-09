<?php
include 'server_connection.php';
session_start(); // Make sure session is started to access $_SESSION
$connection = connect_to_database();

if (isset($_POST['searchQuery']) && isset($_SESSION['username'])) {
    $searchQuery = mysqli_real_escape_string($connection, $_POST['searchQuery']);
    $currentUser = $_SESSION['username'];
    
    // Adjust the query to exclude users who have been blocked by the current user or have blocked the current user
    $query = "SELECT p.username, p.realName
              FROM profile p
              WHERE (p.username LIKE ? OR p.realName LIKE ?)
              AND p.username NOT IN (
                  SELECT blocked FROM block WHERE blocker = ?
              )
              AND p.username NOT IN (
                  SELECT blocker FROM block WHERE blocked = ?
              )";

    $stmt = mysqli_prepare($connection, $query);
    $searchTerm = '%' . $searchQuery . '%';
    mysqli_stmt_bind_param($stmt, "ssss", $searchTerm, $searchTerm, $currentUser, $currentUser);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        echo "<div><a href='profile.php?user_id=" . htmlspecialchars($row['username']) . "' data-username='" . htmlspecialchars($row['username']) . "'>" . htmlspecialchars($row['username']) . " - " . htmlspecialchars($row['realName']) . "</a></div>";
    }
    mysqli_stmt_close($stmt);
    mysqli_close($connection);
}
?>
