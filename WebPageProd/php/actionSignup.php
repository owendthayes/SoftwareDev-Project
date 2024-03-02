<?php
    include 'server_connection.php';
    $connection = connect_to_database();
    session_start();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = mysqli_real_escape_string($connection, $_POST['uname']);
        $email = mysqli_real_escape_string($connection, $_POST['email']);
        $password = $_POST['pword'];
        $confirm_password = $_POST['conpword'];

        // Password validation
        if (strlen($password) < 7) {
            //die("Password must be at least 7 characters long.");
            $_SESSION['error_message'] = "Password must be at least 7 characters long.";
            header("Location: ../signup.php"); // Redirect back to the signup page
            exit;
        }
        if ($password != $confirm_password) {
            //die("Passwords do not match.");
            $_SESSION['error_message'] = "Passwords do not match.";
            header("Location: ../signup.php"); // Redirect back to the signup page
            exit;
        }

        // Check if email already exists
        if (!checkdata($email, $connection)) {
            //die("Email already exists.");
            $_SESSION['error_message'] = "Email already exists.";
            header("Location: ../signup.php"); // Redirect back to the signup page
            exit;
        }

        // Check if username already exists
        if(!checkUsername($username, $connection)) {
            #die("Username already exsit.");
            $_SESSION['error_message'] = "Username already exists.";
            header("Location: ../signup.php"); // Redirect back to the signup page
            exit;
        }

        // Hash the password before storing it
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);



        $query = "INSERT INTO profile (username, password, email, realName) VALUES (?, ?, ?, ?)";

        $stmt = mysqli_prepare($connection, $query);

        mysqli_stmt_bind_param($stmt, "ssss", $username, $hashed_password, $email, $username);

        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_affected_rows($stmt) > 0) {
            echo "Account created successfully.";
            // Redirect to login page or set the session
            header('Location: ../login.php');
        } else {
            echo "Error: " . mysqli_error($connection);
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_close($connection);
?>
