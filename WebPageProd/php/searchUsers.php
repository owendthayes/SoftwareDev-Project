<?php
include 'server_connection.php';
$connection = connect_to_database();

if (isset($_POST['searchQuery'])) {
    $searchQuery = mysqli_real_escape_string($connection, $_POST['searchQuery']);
    $query = "SELECT username, realName FROM profile WHERE username LIKE ? OR realName LIKE ?";
    $stmt = mysqli_prepare($connection, $query);

    $searchTerm = '%' . $searchQuery . '%';
    mysqli_stmt_bind_param($stmt, "ss", $searchTerm, $searchTerm);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        //echo "<div>" . htmlspecialchars($row['username']) . " - " . htmlspecialchars($row['realName']) . "</div>";
        //echo "<div><a href='userViewProfile.php?user_id=" . htmlspecialchars($row['username']) . "'>" . htmlspecialchars($row['username']) . " - " . htmlspecialchars($row['realName']) . "</a></div>";
        //echo "<div><a href='userViewProfile.php?user_id=" . htmlspecialchars($row['username']) . "'>" . htmlspecialchars($row['username']) . " - " . htmlspecialchars($row['realName']) . "</a></div>";
        echo "<div><a href='userViewProfile.php?user_id=" . htmlspecialchars($row['username']) . "' data-username='" . htmlspecialchars($row['username']) . "'>" . htmlspecialchars($row['username']) . " - " . htmlspecialchars($row['realName']) . "</a></div>";
    }
    mysqli_stmt_close($stmt);
    mysqli_close($connection);
}
?>
