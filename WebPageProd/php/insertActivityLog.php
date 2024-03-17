<?php
session_start();
include 'server_connection.php';
$connection = connect_to_database();

// Check if the user is logged in and the request is a POST
if (isset($_SESSION['username']) && $_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $groupid = $_POST['groupid'];
    $file_name = $_POST['file_name'];
    $edited_by = $_SESSION['username']; // Logged in user's username from the session.
    $timestamp = $_POST['timestamp'];
    $edit_description = $_POST['edit_description'];

    // Prepare an INSERT statement to add the activity log
    $query = "INSERT INTO activity_log (file_name, groupid, edited_by, timestamp, edit_description) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($connection, $query);

    // Bind the parameters to the statement
    mysqli_stmt_bind_param($stmt, "sisss", $file_name, $groupid, $edited_by, $timestamp, $edit_description);

    // Execute the statement
    if (mysqli_stmt_execute($stmt)) {
        // echo "Activity log entry added successfully.";
        header("Location: " . $_SERVER['HTTP_REFERER']);

    } else {
        echo "Error: " . mysqli_error($connection);
        // Redirect or display error message
        // header('Location: error_page.php'); // Redirect to an error page
    }

    // Close the statement
    mysqli_stmt_close($stmt);

    // Close the database connection
    mysqli_close($connection);
} else {
    // Redirect to the login page if the user is not logged in
    // header('Location: login_page.php');
    echo "User is not logged in.";
}
?>
