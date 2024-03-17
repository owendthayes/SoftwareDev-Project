<?php
    session_start();
    include 'php/server_connection.php';

    if (isset($_SESSION['username'])) {
        $loggedInUser = $_SESSION['username'];
        $connection = connect_to_database();

        // Prepare the SQL query to select notifications for the logged-in user
        $query = "SELECT * FROM notifications WHERE recipient_username = ? ORDER BY time_sent DESC";
        $stmt = mysqli_prepare($connection, $query);
        mysqli_stmt_bind_param($stmt, "s", $loggedInUser);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $notifications = mysqli_fetch_all($result, MYSQLI_ASSOC);

        mysqli_free_result($result);
        mysqli_close($connection);
    } else {
        echo "Please log in to view your notifications.";
        exit;
    }
?>
<html>
    <head>
        <link rel="stylesheet" href="style.css">
        <title> CreativSync - Notifications</title>
        <link rel="icon" href="Images/logo.png">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    </head>
    <body>
        <section class="navigation">
            <nav>
                <div class="mainnav">
                    <div class="imgnav">
                        <img src="Images/logo.png">
                        <div class="compname"><h1 style="color:white;">CreativSync</h1></div>
                    </div>
                    <div class="navi">
                            <a href="feed.html">Feed</a>
                            <a href="notifications.html">Notifications</a>
                            <a href="search.html">Search</a> 
                            <a href="groupFiles.html">Groups</a>
                            <a href="messages2.php">Messages</a>
                            <a href="help.html">Help</a>
                            <button><a href="profile.php">Profile</a></button>
                    </div>
                </div>
            </nav>
        </section>
        <section>        
            <form method="POST" action="php/clearNotifications.php">
                <button type="submit" class="clearAllButton" name="clearAll">Clear All</button>
            </form>
            <div class="notificationContainer">
                <div class="notifications">
                    <p class="notificationTitle">Notifications</p>
                </div>
                <?php foreach ($notifications as $notification): ?>

                    <div class="notificationItem">
                        <div class="notificationContentContainer">
                            <div class="notification">
                                <image class="notificationAvatar" src="Images/defaultAvatar.png"></image>
                                <p class="notificationUsername"><?php echo htmlspecialchars($notification['sender_username']); ?></p>
                                <p class="notificationText"><?php echo htmlspecialchars($notification['activity_type']); ?></p>
                                <p class="notificationContent"><?php echo htmlspecialchars($notification['content']); ?></p>
                                <button class="clearNotification"><image src="Images/clearBin.png" class="notificationImage"></image></button>                    
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

    </body>
</html>
