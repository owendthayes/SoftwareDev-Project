<?php
session_start();
include 'server_connection.php'; // Make sure to use the correct path to your connection script

$connection = connect_to_database();

$response = ['success' => false, 'message' => ''];

// Check if the logId is set in the POST request
if (isset($_POST['logId'])) {
    $logId = $_POST['logId'];

    // Start transaction
    mysqli_begin_transaction($connection);

    try {
        // Delete log from database
        $stmt = mysqli_prepare($connection, "DELETE FROM activity_log WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $logId);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Failed to delete the activity log from the database.');
        }
        mysqli_stmt_close($stmt);

        mysqli_commit($connection);
        $response = ['success' => true, 'message' => 'Activity log deleted successfully.'];
    } catch (Exception $e) {
        mysqli_rollback($connection);
        $response['message'] = $e->getMessage();
    }
} else {
    $response['message'] = 'No log ID provided.';
}

echo json_encode($response);
mysqli_close($connection);
?>
