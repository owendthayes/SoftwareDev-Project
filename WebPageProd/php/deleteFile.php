<?php
session_start();
include 'server_connection.php'; // Make sure to use the correct path to your connection script

$connection = connect_to_database();

// Ideally, you should check if the user is authorized to delete the file here

$response = ['success' => false, 'message' => ''];

if (isset($_POST['fileId'])) {
    $fileId = $_POST['fileId'];

    // Start transaction
    mysqli_begin_transaction($connection);

    try {
        // Get the file name from the database
        $stmt = mysqli_prepare($connection, "SELECT file_path FROM group_files WHERE file_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $fileId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $file = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($file) {
            // File path is relative to the script's location
            $filePath = '../uploads/' . $file['file_path']; // Adjust the path as needed

            // Delete file from the server file system if it exists
            if (file_exists($filePath)) {
                if (!unlink($filePath)) {
                    throw new Exception('Failed to delete the file from the server.');
                }
            }

            // Delete file from database
            $stmt = mysqli_prepare($connection, "DELETE FROM group_files WHERE file_id = ?");
            mysqli_stmt_bind_param($stmt, "i", $fileId);
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('Failed to delete the file from the database.');
            }
            mysqli_stmt_close($stmt);

            mysqli_commit($connection);
            $response = ['success' => true, 'message' => 'File deleted successfully.'];
        } else {
            $response['message'] = 'File not found in the database.';
        }
    } catch (Exception $e) {
        mysqli_rollback($connection);
        $response['message'] = $e->getMessage();
    }
} else {
    $response['message'] = 'No file ID provided.';
}

echo json_encode($response);
mysqli_close($connection);
?>
