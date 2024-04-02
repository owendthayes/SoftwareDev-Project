<?php
include 'server_connection.php'; // Adjust the path to your database connection script
$connection = connect_to_database();
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['username'])) {
    $username = $_SESSION['username'];

    // Initialize variables to null
    $fullName = null;
    $aboutMe = null;
    $profileImagePath = null;

    // Check if full name is provided
    if (!empty($_POST['fullName'])) {
        $fullName = $_POST['fullName'];
    }

    // Check if about me is provided
    if (!empty($_POST['aboutMe'])) {
        $aboutMe = $_POST['aboutMe'];
    }

    // Check if a file is uploaded
    if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] == 0) {
        $fileName = basename($_FILES['profileImage']['name']);
        $fileTmpName = $_FILES['profileImage']['tmp_name'];
        $fileDestination = '../Images/' . $fileName; // Specify your path to the image folder

        // Move the file to the image folder
        if (move_uploaded_file($fileTmpName, $fileDestination)) {
            $profileImagePath = 'Images/' . $fileName; // Store 'Images/' followed by the image filename
        } else {
            echo "Error uploading file.";
            exit;
        }
    }

    // Build the SQL query
    $query = "UPDATE profile SET ";
    $queryParams = [];

    // Update full name if provided
    if ($fullName !== null) {
        $query .= "realName = ?, ";
        array_push($queryParams, $fullName);
    }

    // Update about me if provided
    if ($aboutMe !== null) {
        $query .= "about_me = ?, ";
        array_push($queryParams, $aboutMe);
    }

    // Update profile image if provided
    if ($profileImagePath !== null) {
        $query .= "profile_image = ?, ";
        array_push($queryParams, $profileImagePath);
    }

    // Remove trailing comma and space
    $query = rtrim($query, ", ");

    // Check if any fields are being updated
    if (count($queryParams) > 0) {
        // Finalize query with WHERE clause
        $query .= " WHERE username = ?";
        array_push($queryParams, $username);
    } else {
        echo "No changes were made.";
        exit;
    }

    // Prepare and execute statement
    $stmt = mysqli_prepare($connection, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, str_repeat("s", count($queryParams)), ...$queryParams);
        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_affected_rows($stmt) > 0) {
            echo "Profile updated successfully.";
            header('Location: ../profile.php');
        } else {
            echo "No changes were made.";
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "Error preparing statement: " . mysqli_error($connection);
    }
    mysqli_close($connection);
} else {
    echo "Invalid request or not logged in.";
}
