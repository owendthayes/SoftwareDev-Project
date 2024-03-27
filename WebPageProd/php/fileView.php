<?php
    session_start();
    include '../php/server_connection.php'; // Adjust the path as necessary
    $connection = connect_to_database();

    if (!isset($_SESSION['username'])) {
        // Redirect to login page or show an error message
        echo "You must be logged in to perform this action.";
        exit;
    }

    $username = $_SESSION['username'];
    $fileid = $_GET['id'];
    $groupid = $_GET['gid'];
    $sessionID = session_id();

    $padURL = "http://localhost:9001/p/" . $groupid . "-" . $fileid; 

    $query = "SELECT * FROM group_participants WHERE username = ? AND groupid = ?";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "is", $username, $groupid);

    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $count = mysqli_stmt_num_rows($stmt);

    if ($count == 0) {
        echo "You must be a part of the group to edit this file.";
        exit;
    }

    mysqli_stmt_close($stmt);

    if ($sessionID && $padURL) {
        print '<html>'."\n";
        print '<head>'."\n";
        print '<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">'."\n";
        print '</head>'."\n";
        print '<body>'."\n";
        print '<script type="text/javascript">'."\n";
        print 'document.cookie = "sessionID='.$sessionID.';path=/;SameSite=None";'."\n";
        print '</script>'."\n";
        print '<iframe src="'.$padURL.'" frameborder="0" name="servFrame" width="100%" height="100%"></iframe>'."\n";
        print '</body>'."\n";
        print '</html>'."\n";
    }
?>
