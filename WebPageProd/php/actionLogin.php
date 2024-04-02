<?php
    include 'server_connection.php';
    $connection = connect_to_database();
    session_start();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = mysqli_real_escape_string($connection, trim($_POST['uname']));
        $password = trim($_POST['pword']);
        //$email = mysqli_real_escape_string($connection, $_POST['email']);

        $query = "SELECT username, password FROM profile WHERE username = ? OR email = ?";
        $stmt = mysqli_prepare($connection, $query);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ss", $username, $username);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $username, $hashed_password);
                if (mysqli_stmt_fetch($stmt)) {
                    if (password_verify($password, $hashed_password)) {
                        $_SESSION['loggedin'] = true;
                        #$_SESSION['Personid'] = $id;
                        $_SESSION['username'] = $username;
                        echo "Success";
                        header("location: ../profile.php");
                        exit;
                    } else {
                        #echo "The password you entered was not valid.";
                        $_SESSION['error_message'] = "The password you entered was not valid.";
                        header("Location: ../login.php"); 
                        exit;
                    }
                }
            } else {
                #echo "No account found with that username.";
                $_SESSION['error_message'] = "No account found with that username.";
                header("Location: ../login.php");
                exit;
            }
            mysqli_stmt_close($stmt);
        } else {
            echo "Error preparing statement: " . mysqli_error($connection);
        }
        mysqli_close($connection);
    }
?>
