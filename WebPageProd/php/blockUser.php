<?php
session_start();
include 'server_connection.php';
$connection = connect_to_database();

if(isset($_SESSION['username']) && isset($_POST['blocked'])) {
    $blocker = $_SESSION['username'];
    $blocked = $_POST['blocked'];

    // Insert block record
    $query = "INSERT INTO block (blocker, blocked) VALUES (?, ?)";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "ss", $blocker, $blocked);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($connection);

    if ($success) {
        // Redirect to the blocker's profile after successful block
        header("Location: ../profile.php");
        exit;
    } else {
        echo "Error blocking user.";
    }
} else {
    echo "Invalid request.";
}
?>
