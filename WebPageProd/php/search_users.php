<?php
    include 'server_connection.php';
    $connection = connect_to_database();

    // Check if the search term is set
    if (isset($_GET['searchTerm'])) {
        $searchTerm = mysqli_real_escape_string($connection, $_GET['searchTerm']);
        // Prepare the SELECT statement to find users matching the search term
        $query = "SELECT username FROM profile WHERE username LIKE CONCAT('%', ?, '%')";
        $stmt = mysqli_prepare($connection, $query);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $searchTerm);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            $users = array();
            while ($row = mysqli_fetch_assoc($result)) {
                $users[] = $row;
            }

            mysqli_stmt_close($stmt);
            // Return the search results as JSON
            echo json_encode($users);
        } else {
            echo "Error preparing statement: " . mysqli_error($connection);
        }
    } else {
        echo "No search term provided.";
    }

    mysqli_close($connection);
?>
