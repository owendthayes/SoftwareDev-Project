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
        $hasNotifications = !empty($notifications);


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
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
                            <a href="notifications.php">
                                Notifications
                                <?php if ($hasNotifications): ?>
                                    <span class="notification-badge"></span> <!-- Add notification badge -->
                                <?php endif; ?>
                            </a>
                            <a href="#" id="showSearch">Search</a> 
                            <a href="groupFiles.php">Groups</a>
                            <a href="messages2.php">Messages</a>
                            <button><a href="profile.php">Profile</a></button>
                    </div>
                </div>

                <div class="snav">
                    <div class="searchnav">
                        <input type="text" autocomplete="off" id="search" placeholder="Type to search..." onkeyup="searchUsers(this.value)">
                        <button id="searchBtn"><i class="fa fa-search"></i></button>
                        <!--<button id="clearBtn"><i class="fa fa-times"></i></button>-->
                    </div>
                    <div id="searchResults"></div>
                </div>
            </nav>
        </section>
        <section>
            <?php if ($hasNotifications): ?>
            <form method="POST" action="php/clearNotifications.php">
                <button type="submit" class="clearAllButton" name="clearAll">Clear All</button>
            </form>
            <div class="notificationContainer">
                <div class="notifications">
                    <p class="notificationTitle">Notifications</p>
                </div>

                <?php else: ?>
                <div class="noNotificationsMessage">
                    <h1 style="color:white; text-align: center;">No notifications</h1>
                </div>
                <?php endif; ?>
                <?php if ($hasNotifications): ?>
                    <?php foreach ($notifications as $notification): ?>
                        <div class="notificationItem">
                            <div class="notificationContentContainer">
                                <div class="notification">
                                    <image class="notificationAvatar" src="<?php echo (!empty($notification['profile_image']) ? htmlspecialchars($notification['profile_image']) : 'Images/defaultAvatar.png'); ?>"></image>
                                    <p class="notificationUsername"><?php echo htmlspecialchars($notification['sender_username']); ?></p>
                                    <p class="notificationText"><?php echo htmlspecialchars($notification['activity_type']); ?></p>
                                    <?php
                                    // Determine the link based on activity type
                                    $link = '';
                                    switch ($notification['activity_type']) {
                                        case 'message':
                                            $link = 'messages2.php';
                                            break;
                                        case 'like':
                                            $link = 'feed.php';
                                            break;
                                        case 'comment':
                                            $link = 'feed.php';
                                            break;
                                        case 'follow':
                                            $link = 'profile.php?user_id=' . $notification['sender_username'];
                                            break;
                                        default:
                                            $link = '#'; // Default link if activity type is unknown
                                            break;
                                    }
                                    ?>
                                    <a href="<?php echo $link; ?>" class="notificationContent"><?php echo htmlspecialchars($notification['content']); ?></a>
                                    <form method="POST" action="php/deleteNotifications.php">
                                        <input type="hidden" name="notificationId" value="<?php echo $notification['id']; ?>">
                                        <button class="clearNotification" type="submit" name="deleteNotification"><image src="Images/clearBin.png" class="notificationImage"></image></button>
                                    </form>                    
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>


        <script>
            function searchUsers(query) {
                if (query.length > 0) {
                    $.ajax({
                        url: 'php/searchUsers.php',
                        type: 'POST',
                        data: {
                            searchQuery: query
                        },
                        success: function(data) {
                            $('#searchResults').html(data);
                            // Add click event listener for each search result link
                            $('#searchResults a').on('click', function(e) {
                                e.preventDefault(); // Prevent default anchor click behavior
                                var clickedUsername = $(this).data('username'); 
                                var currentUser = '<?php echo $_SESSION["username"]; ?>';
                                if (clickedUsername === currentUser) {
                                    window.location.href = 'profile.php'; // Redirect to the user's own profile
                                } else {
                                    window.location.href = $(this).attr('href'); // Redirect to the clicked user's profile
                                }
                            });
                        }
                    });
                } else {
                    $('#searchResults').html('');
                }
            }
        </script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const showSearch = document.getElementById('showSearch');
                const snav = document.querySelector('.snav');

                // Function to show the search nav
                function showSnav() {
                    snav.style.display = 'block';
                    snav.style.opacity = 1;
                    snav.style.transition = 'opacity 0.5s ease-in-out';
                }

                // Function to hide the search nav
                function hideSnav() {
                    snav.style.opacity = 0; // Start fade out animation
                    setTimeout(function() {
                        snav.style.display = 'none'; // Hide snav after the animation
                    }, 1000); // Delay to match the transition
                }

                // Event to show snav when hovering over the search button
                showSearch.addEventListener('mouseenter', function() {
                    showSnav();
                });

                // Event to hide snav when the mouse leaves the snav area
                snav.addEventListener('mouseleave', function() {
                    hideSnav();
                });
            });
        </script>


    </body>
</html>
