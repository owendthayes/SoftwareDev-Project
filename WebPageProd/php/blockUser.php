<?php
session_start();
include 'server_connection.php';
$connection = connect_to_database();

if(isset($_SESSION['username']) && isset($_POST['blocked'])) {
    $blocker = $_SESSION['username'];
    $blocked = $_POST['blocked'];

    // Start transaction
    mysqli_begin_transaction($connection);
    
    try {
        // Identify the conversation between the blocker and the blocked
        $chatQuery = "SELECT chatid FROM chat WHERE (user_1 = ? AND user_2 = ?) OR (user_1 = ? AND user_2 = ?)";
        $chatStmt = mysqli_prepare($connection, $chatQuery);
        mysqli_stmt_bind_param($chatStmt, "ssss", $blocker, $blocked, $blocked, $blocker);
        mysqli_stmt_execute($chatStmt);
        $chatResult = mysqli_stmt_get_result($chatStmt);
        $chatRow = mysqli_fetch_assoc($chatResult);
        $chatId = $chatRow['chatid'] ?? null;
        mysqli_stmt_close($chatStmt);

        if ($chatId) {
            // Delete all messages in the conversation
            $messagesQuery = "DELETE FROM messages WHERE chatid = ?";
            $messagesStmt = mysqli_prepare($connection, $messagesQuery);
            mysqli_stmt_bind_param($messagesStmt, "i", $chatId);
            mysqli_stmt_execute($messagesStmt);
            mysqli_stmt_close($messagesStmt);

            // Delete the conversation itself
            $conversationQuery = "DELETE FROM chat WHERE chatid = ?";
            $conversationStmt = mysqli_prepare($connection, $conversationQuery);
            mysqli_stmt_bind_param($conversationStmt, "i", $chatId);
            mysqli_stmt_execute($conversationStmt);
            mysqli_stmt_close($conversationStmt);
        }

        // Insert block record
        $query = "INSERT INTO block (blocker, blocked) VALUES (?, ?)";
        $stmt = mysqli_prepare($connection, $query);
        mysqli_stmt_bind_param($stmt, "ss", $blocker, $blocked);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Delete from followings (where the blocker follows the blocked user)
        $deleteFollowingQuery = "DELETE FROM follow WHERE (follower = ? AND following = ?) OR (follower = ? AND following = ?) ";
        $stmtDeleteFollowing = mysqli_prepare($connection, $deleteFollowingQuery);
        mysqli_stmt_bind_param($stmtDeleteFollowing, "ssss", $blocker, $blocked, $blocked, $blocker);
        mysqli_stmt_execute($stmtDeleteFollowing);
        mysqli_stmt_close($stmtDeleteFollowing);

        // Commit transaction
        mysqli_commit($connection);

        // Redirect to the blocker's profile after successful block
        header("Location: ../profile.php");
        exit;
    } catch (Exception $e) {
        // An exception has been thrown, rollback the transaction
        mysqli_rollback($connection);
        echo "Error blocking user: " . $e->getMessage();
    }
    mysqli_close($connection);
} else {
    echo "Invalid request.";
}
?>
