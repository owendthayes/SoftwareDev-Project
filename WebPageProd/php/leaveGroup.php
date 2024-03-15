// leaveGroup.php
<?php
session_start();
include 'server_connection.php'; // Adjust path as needed

if (!isset($_SESSION['username'])) {
    die("You must be logged in to leave a group.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['groupid']) && isset($_POST['username'])) {
    $groupid = $_POST['groupid'];
    $username = $_POST['username'];

    // Check if the user is trying to leave a group they are a member of
    if ($username !== $_SESSION['username']) {
        die("You can only leave groups that you are a member of.");
    }

    // Connect to the database
    $connection = connect_to_database();

    // Prepare the statement to delete the user from the group_participants table
    $query = "DELETE FROM group_participants WHERE groupid = ? AND username = ?";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "is", $groupid, $username);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($connection);

    if ($result) {
        // Successfully left the group
        header("Location: ../groupFiles.php");
        exit();
    } else {
        echo "An error occurred while trying to leave the group.";
    }
} else {
    echo "Invalid request.";
}
?>
