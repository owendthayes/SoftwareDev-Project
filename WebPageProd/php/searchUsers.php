<?php
include 'server_connection.php';
session_start();
$connection = connect_to_database();

if (isset($_POST['searchQuery']) && isset($_SESSION['username'])) {
    $searchQuery = mysqli_real_escape_string($connection, $_POST['searchQuery']);
    $currentUser = $_SESSION['username'];
    
    // Query for users including blocked ones but excluding those who have blocked the logged-in user
    $userQuery = "SELECT p.username AS name, p.realName AS detail, 'User' AS type
                  FROM profile p
                  WHERE (p.username LIKE ? OR p.realName LIKE ?)
                  AND p.username NOT IN (
                      SELECT blocker FROM block WHERE blocked = ?
                  )";

    // Query for public groups
    $groupQuery = "SELECT g.groupid AS name, g.groupname AS detail, 'Group' AS type
   FROM `groups` g
                   WHERE g.groupname LIKE ? AND g.type= 'public'";

    // Combine queries with UNION
    $combinedQuery = "($userQuery) UNION ($groupQuery)";

    // Prepare the combined statement
    $stmt = mysqli_prepare($connection, $combinedQuery);
    $searchTerm = '%' . $searchQuery . '%';
    // Bind parameters for both queries
    mysqli_stmt_bind_param($stmt, "ssss", $searchTerm, $searchTerm, $currentUser, $searchTerm);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // Fetch and output results
    while ($row = mysqli_fetch_assoc($result)) {
        $displayName = htmlspecialchars($row['detail']); // Use 'detail' for display name, which is 'groupname' for groups
        $type = htmlspecialchars($row['type']);
    
        if ($type === 'User') {
            // User type output format
            echo "<div><a href='profile.php?user_id=" . htmlspecialchars($row['name']) . "'>" . htmlspecialchars($row['name']) . " - " . $displayName . " (" . $type . ")</a></div>";
        } elseif ($type === 'Group') {
            // Group type output format, passing groupid in the URL
            echo "<div><a href='groupView2.php?groupid=" . htmlspecialchars($row['name']) . "'>" . $displayName . " (" . $type . ")</a></div>";
        }
    } 

    mysqli_stmt_close($stmt);
    mysqli_close($connection);
}
?>
