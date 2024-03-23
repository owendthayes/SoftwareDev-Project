<?php
    session_start();
    include 'server_connection.php';
    
    if (!isset($_SESSION['username'])) {
        echo "User not logged in.";
        exit;
    }
    
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['notificationId'])) {
        $loggedInUser = $_SESSION['username'];
        $notificationId = $_POST['notificationId'];
        $connection = connect_to_database();
        
        $query = "DELETE FROM notifications WHERE id = ? AND recipient_username = ?";
        $stmt = mysqli_prepare($connection, $query);
        mysqli_stmt_bind_param($stmt, "is", $notificationId, $loggedInUser);
        $success = mysqli_stmt_execute($stmt);
        
        if ($success) {
            echo "Notification deleted successfully.";
            header("Location: " . $_SERVER['HTTP_REFERER']);
        } else {
            echo "Error deleting notification.";
        }

        mysqli_stmt_close($stmt);
        mysqli_close($connection);
    }
?>
