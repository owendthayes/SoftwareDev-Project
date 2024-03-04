<?php
include 'php/actionLogin.php';
// Ensure session_start() is called to access $_SESSION
if(!isset($_SESSION)) { 
    session_start(); 
}

$connection = connect_to_database();

if(isset($_SESSION['username'])) {
    // Fetch the username from the query parameter instead of the session
    $username = isset($_GET['user_id']) ? $_GET['user_id'] : '';

    // Query the database to retrieve profile information for the given username
    $query = "SELECT realName, about_me, profile_image FROM profile WHERE username = ?";
    $stmt = mysqli_prepare($connection, $query);

    if($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $realName, $aboutMe, $profileImage);

        if(mysqli_stmt_fetch($stmt)) {
            // Process fetched data
        } else {
            echo "No profile found for the user.";
        }
        mysqli_stmt_close($stmt);
        // Check if the logged-in user is following the viewed user
        if (isset($_SESSION['username'])) {
            $loggedInUser = $_SESSION['username'];
            
            // Query to check if the logged-in user is following the viewed user
            $checkFollowQuery = "SELECT * FROM follow WHERE follower = ? AND following = ?";
            $checkFollowStmt = mysqli_prepare($connection, $checkFollowQuery);
            mysqli_stmt_bind_param($checkFollowStmt, "ss", $loggedInUser, $username);
            mysqli_stmt_execute($checkFollowStmt);
            mysqli_stmt_store_result($checkFollowStmt);
            $isFollowing = mysqli_stmt_num_rows($checkFollowStmt) > 0;
            mysqli_stmt_close($checkFollowStmt);
        } else {
            // User is not logged in, handle this case accordingly
            $isFollowing = false;
        }
        // Query to fetch follower count
        $followerCountQuery = "SELECT COUNT(*) FROM follow WHERE following = ?";
        $followerStmt = mysqli_prepare($connection, $followerCountQuery);
        mysqli_stmt_bind_param($followerStmt, "s", $username);
        mysqli_stmt_execute($followerStmt);
        mysqli_stmt_bind_result($followerStmt, $followerCount);
        mysqli_stmt_fetch($followerStmt);
        mysqli_stmt_close($followerStmt);
        
        // Query to fetch following count
        $followingCountQuery = "SELECT COUNT(*) FROM follow WHERE follower = ?";
        $followingStmt = mysqli_prepare($connection, $followingCountQuery);
        mysqli_stmt_bind_param($followingStmt, "s", $username);
        mysqli_stmt_execute($followingStmt);
        mysqli_stmt_bind_result($followingStmt, $followingCount);
        mysqli_stmt_fetch($followingStmt);
        mysqli_stmt_close($followingStmt);
    } else {
        echo "Error preparing statement.";
    }
    mysqli_close($connection);
} else {
    echo "User is not logged in.";
}
?>

<html>
    <head>
        <link rel="stylesheet" href="style.css">
        <title> CreativSync - Profile</title>
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
                            <a href="search.html" id="showSearch">Search</a> 
                            <a href="groups.html">Groups</a>
                            <a href="messages2.php">Messages</a>
                            <button><a href="profile.php">Profile</a></button>
                    </div>
                </div>

                <div class="snav">
                    <div class="searchnav">
                        <input type="text" id="search" placeholder="Type to search...">
                        <button id="searchBtn"><i class="fa fa-search"></i></button>
                        <!--<button id="clearBtn"><i class="fa fa-times"></i></button>-->
                    </div>
                </div>
            </nav>
        </section>   
        <section>
            <div class = "profileContainer">
                <p class = "profileRealName"><?php echo $realName; ?></p>
                <!--<p class = "profileUsername">JSmith2024</p>-->
                <p class = "profileUsername"><?php echo $username ?></p>
                <!--<button id="logoutButton" class="logoutButton" onclick ="window.location.href='php/logout.php';">Logout</button>
                <button id="editProfileButton" class="editProfileButton" onclick="editProfileButton()">Edit Profile</button>-->
                <div class="followerCount">Followers: <span><?php echo $followerCount; ?></span></div>
                <div class="followingCount">Following: <span><?php echo $followingCount; ?></span></div>

                <p class = "profileAboutMe">About me</p>
                <p class = "profileAboutMeContent"><?php echo $aboutMe; ?></p>
                <p class = "profileGroupsLabel">Groups</p>
                <div class = "profileGroupsContainer">
                    <div class = "profileGroup" style="background-color:red">Alfie's Artists</div>
                    <div class = "profileGroup" style="background-color:green"background-color="blue">Birthday Planning</div>
                    <div class = "profileGroup" style="background-color:blue"background-color="green">UNI-GRP11</div>
                    <div class = "profileGroup" style="background-color:orange"background-color="orange">Class 15</div>
                    <div class = "profileGroup" style="background-color:darkgreen"background-color="yellow">Class 9</div>
                    <div class = "profileGroup" style="background-color:purple"background-color="purple">Teachers 2024</div>
                    <div class = "profileGroup" style="background-color:darkcyan"background-color="lightblue">CreativSync Team</div>
                    <div class = "profileGroup" style="background-color:darkorange"background-color="pink">Sports Day 2024</div>
                    <div class = "profileGroup" style="background-color:darkred"background-color="lightgreen">School Play 2024</div>
                </div>
                <image class = "profileAvatar" src = "<?php echo $profileImage; ?>"></image>
                <div class="profileButtonsContainer">
                    <button class = "profileMessageButton" >Send Message</button>
                    <button id="followButton" onclick="toggleFollow('<?php echo $username; ?>')" class="profileFollowButton"><?php echo $isFollowing ? 'Unfollow' : 'Follow'; ?></button>
                    <button class = "profileCopyEmailButton">Copy Email</button>
                    <button class = "profileInviteButton">Invite to Group</button>
                    <form action="php/blockUser.php" method="post">
                        <input type="hidden" name="blocked" value="<?php echo $username; ?>">
                        <button type="submit" class="profileBlockButton">Block</button>
                    </form>
                </div>
            </div>

            <!-- Hidden form for profile editing
            <div id="editProfileForm" style="display: none;">
                <form id="profileForm" enctype="multipart/form-data" action="php/save_profile_changes.php" method="post">
                    <label for="profileImageInput">Profile Image:</label>
                    <input type="file" id="profileImageInput" name="profileImage" accept="image/*">

                    <label for="fullNameInput">Full Name:</label>
                    <input type="text" id="fullNameInput" name="fullName">

                    <label for="aboutMeInput">About Me:</label>
                    <textarea id="aboutMeInput" name="aboutMe"></textarea>

                    <input type="submit" value="Save Changes">
                </form>
            </div>-->
        </section>

        <script>
            function toggleFollow(username) {
                var button = document.getElementById('followButton');
                if (button.innerText === 'Follow') {
                    followUser(username, function() {
                        button.innerText = 'Unfollow';
                    });
                } else {
                    unfollowUser(username, function() {
                        button.innerText = 'Follow';
                    });
                }
            }

            function followUser(username, callback) {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'php/followUser.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == XMLHttpRequest.DONE) {
                        if (xhr.status == 200) {
                            callback();
                        } else {
                            console.error('Error: ' + xhr.responseText);
                        }
                    }
                };
                xhr.send('username=' + username);
            }

            function unfollowUser(username, callback) {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'php/unfollowUser.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == XMLHttpRequest.DONE) {
                        if (xhr.status == 200) {
                            callback();
                        } else {
                            console.error('Error: ' + xhr.responseText);
                        }
                    }
                };
                xhr.send('username=' + username);
            }
        </script>




        <script>
            function editProfileButton() {
                // Toggle the visibility of the edit profile form
                var form = document.getElementById('editProfileForm');
                form.style.display = form.style.display === 'none' ? 'block' : 'none';
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
