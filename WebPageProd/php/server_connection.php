<?php
    function connect_to_database()
    {
        $connection = mysqli_connect('sabaik6fx8he7pua.chr7pe7iynqr.eu-west-1.rds.amazonaws.com', 'uauqa4lh7ekaidim', 'h097tylestzbdl4g', 'gbnmqplwbiep0tuu');

    if (mysqli_connect_errno()) {
        echo "<h1>Connection Error</h1>";
        echo "Failed to connect to MYSQL Database: ".mysqli_connect_errno();
        //and kill the script
        die();
    }
    return $connection;
    }

    function checkdata($email, $connection)
    {
        // This line prevents SQL injection by using a prepared statement
        $query = "SELECT email FROM profile WHERE email = ?";
        $stmt = mysqli_prepare($connection, $query);
        
        // Check if the statement was prepared correctly
        if ($stmt) {
            // Bind the input email to the prepared statement as a parameter
            mysqli_stmt_bind_param($stmt, "s", $email);
            
            // Execute the query
            mysqli_stmt_execute($stmt);
            
            // Store the result so we can check how many rows were returned
            mysqli_stmt_store_result($stmt);
            
            // Check the number of rows. If it is more than 0, the username exists
            if (mysqli_stmt_num_rows($stmt) > 0) {
                mysqli_stmt_close($stmt);
                return false; // email exists
            } else {
                mysqli_stmt_close($stmt);
                return true; // email is unique
            }
        } else {
            // Handle errors for statement preparation
            echo "Error preparing statement: " . mysqli_error($connection);
            return false; // Return false because the statement couldn't be prepared
        }
    }

    function checkUsername($username, $connection)
    {
        // This line prevents SQL injection by using a prepared statement
        $query = "SELECT username FROM profile WHERE username = ?";
        $stmt = mysqli_prepare($connection, $query);
        
        // Check if the statement was prepared correctly
        if ($stmt) {
            // Bind the input username to the prepared statement as a parameter
            mysqli_stmt_bind_param($stmt, "s", $username);
            
            // Execute the query
            mysqli_stmt_execute($stmt);
            
            // Store the result so we can check how many rows were returned
            mysqli_stmt_store_result($stmt);
            
            // Check the number of rows. If it is more than 0, the username exists
            if (mysqli_stmt_num_rows($stmt) > 0) {
                mysqli_stmt_close($stmt);
                return false; // Username exists
            } else {
                mysqli_stmt_close($stmt);
                return true; // Username is unique
            }
        } else {
            // Handle errors for statement preparation
            echo "Error preparing statement: " . mysqli_error($connection);
            return false; // Return false because the statement couldn't be prepared
        }
    }
?>
