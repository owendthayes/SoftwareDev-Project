<?php
    session_start();
    include 'php/server_connection.php';

    if (isset($_SESSION['username'])) {
        $loggedInUser = $_SESSION['username'];
        $connection = connect_to_database();
    
        // Modify the SQL query to join the profile table and select the profile image as well
        $query = "SELECT n.*, p.profile_image 
                  FROM notifications n
                  LEFT JOIN profile p ON n.sender_username = p.username
                  WHERE n.recipient_username = ? 
                  ORDER BY n.time_sent DESC";
    
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
                        <a href="../home.html"><img src="Images/logo.png"></a>
                        <div class="compname"><h1 style="color:white;">CreativSync</h1></div>
                    </div>
                    <div class="navi">
                            <a href="feed.php">Feed</a>
                            <a href="notifications.php">Notifications</a>
                            <a href="#">Search</a> 
                            <a href="groupFiles.php">Groups</a>
                            <a href="messages2.php">Messages</a>
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
                                <image class="notificationAvatar" src="<?php echo (!empty($notification['profile_image']) ? htmlspecialchars($notification['profile_image']) : 'Images/defaultAvatar.png'); ?>"></image>
                                <p class="notificationUsername"><?php echo htmlspecialchars($notification['sender_username']); ?></p>
                                <p class="notificationText"><?php echo htmlspecialchars($notification['activity_type']); ?></p>
                                <p class="notificationContent"><?php echo htmlspecialchars($notification['content']); ?></p>
                                <form method="POST" action="php/deleteNotifications.php">
                                    <input type="hidden" name="notificationId" value="<?php echo $notification['id']; ?>">
                                    <button class="clearNotification" type="submit" name="deleteNotification"><image src="Images/clearBin.png" class="notificationImage"></image></button>
                                </form>                    
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

    </body>
</html>
