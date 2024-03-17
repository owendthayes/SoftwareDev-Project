<?php
include 'php/actionLogin.php';
// Add your database connection and query here to retrieve profile information
$connection = connect_to_database();

if (isset($_SESSION['username'])) {
    $username = (isset($_GET['user_id']) && !empty($_GET['user_id'])) ? $_GET['user_id'] : $_SESSION['username'];

    // Query the database to retrieve profile information
    $query = "SELECT realName, about_me, profile_image FROM profile WHERE username = ?";
    $stmt = mysqli_prepare($connection, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $realName, $aboutMe, $profileImage);

        if (mysqli_stmt_fetch($stmt)) {
            // Handle empty values and set default values
            if (empty($realName)) {
                $realName = "Name Unknown";
            }
            if (empty($aboutMe)) {
                $aboutMe = "Add something about yourself!";
            }
            if (empty($profileImage)) {
                $profileImage = "Images/defaultAvatar.png";
            }
        } else {
            // No profile found for the user
            // You can handle this case accordingly
            echo "No profile found for the user";
        }

        mysqli_stmt_close($stmt);

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

        // Query to see if the user is already followed
        $followingCountQuery = "SELECT COUNT(*) FROM follow WHERE follower = ? and following = ?";
        $followingBoolStmt = mysqli_prepare($connection, $followingCountQuery);
        mysqli_stmt_bind_param($followingBoolStmt, "ss", $_SESSION['username'], $username);
        mysqli_stmt_execute($followingBoolStmt);
        mysqli_stmt_bind_result($followingBoolStmt, $following);
        mysqli_stmt_fetch($followingBoolStmt);
        mysqli_stmt_close($followingBoolStmt);
    }

    mysqli_close($connection);
} else {
    // User is not logged in, handle this case accordingly
    echo "user not logged in";
}
?>



<html>
    <head>
        <link rel="stylesheet" href="style.css">
        <title> CreativSync - Profile</title>
        <link rel="icon" href="Images/logo.png">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    </head>

    <body>
        <section class="navigation">
            <nav>
                <div class="mainnav">
                    <div class="imgnav">
                        <img src="Images/logo.png">
                        <div class="compname">
                            <h1 style="color:white;">CreativSync</h1>
                        </div>
                    </div>
                    <div class="navi">
                        <a href="feed.php">Feed</a>
                        <a href="notifications.php">Notifications</a>
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
            <div class="profileContainer">
                <p class="profileRealName"><?php echo $realName; ?></p>
                <!--<p class = "profileUsername">JSmith2024</p>-->
                <p class="profileUsername"><?php echo $username; ?></p>
                <div class="followerCount">Followers: <span><?php echo $followerCount; ?></span></div>
                <div class="followingCount">Following: <span><?php echo $followingCount; ?></span></div>

                <button id="logoutButton" class="logoutButton" onclick="window.location.href='php/logout.php';">Logout</button>
                <button id="editProfileButton" class="editProfileButton" onclick="editProfileButton()">Edit Profile</button>
                <p class="profileAboutMe">About me</p>
                <p class="profileAboutMeContent"><?php echo $aboutMe; ?></p>
                <p class="profileGroupsLabel">Groups</p>
                <div class="profileGroupsContainer">
                    <div class="profileGroup" style="background-color:red">Alfie's Artists</div>
                    <div class="profileGroup" style="background-color:green" background-color="blue">Birthday Planning</div>
                    <div class="profileGroup" style="background-color:blue" background-color="green">UNI-GRP11</div>
                    <div class="profileGroup" style="background-color:orange" background-color="orange">Class 15</div>
                    <div class="profileGroup" style="background-color:darkgreen" background-color="yellow">Class 9</div>
                    <div class="profileGroup" style="background-color:purple" background-color="purple">Teachers 2024</div>
                    <div class="profileGroup" style="background-color:darkcyan" background-color="lightblue">CreativSync Team</div>
                    <div class="profileGroup" style="background-color:darkorange" background-color="pink">Sports Day 2024</div>
                    <div class="profileGroup" style="background-color:darkred" background-color="lightgreen">School Play 2024</div>
                </div>
                <image class="profileAvatar" src="<?php echo $profileImage; ?>"></image>
                <div class="profileButtonsContainer">
                    <button class="profileMessageButton">Send Message</button>
                    <button class="profileFollowButton">Follow</button>
                    <button class="profileCopyEmailButton">Copy Email</button>
                    <button class="profileInviteButton">Invite to Group</button>
                    <button class="profileBlockButton">Block</button>
                </div>
            </div>

            <!-- Hidden form for profile editing -->
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
            </div>
        </section>



        <script>
            $(document).ready(function() {
                var username = "<?php echo $username; ?>";
                var user = "<?php echo $_SESSION["username"]; ?>";
                var following = <?php echo $following; ?>;
                console.log(following)

                if (user == username) {
                    $(".profileButtonsContainer").remove()
                } else {
                    var profileButton = document.getElementById('editProfileButton');
                    var logoutButton = document.getElementById('logoutButton');
                    profileButton.parentNode.removeChild(profileButton)
                    logoutButton.parentNode.removeChild(logoutButton)
                }

                if (following == 0) {
                    $(".profileFollowButton").text("Follow");
                    $(".profileFollowButton").on("click", function() {
                        $.ajax({
                            url: './php/followUser.php', // Update the URL to your PHP script
                            type: 'POST',
                            data: {
                                'username': username
                            },
                            success: function(response) {
                                window.location.reload();
                                console.log(response); // Handle the response
                            },
                            error: function(xhr, status, error) {
                                console.error("An error occurred: " + error);
                            }
                        });
                    });
                } else if (following == 1) {
                    $(".profileFollowButton").text("Unfollow");
                    $(".profileFollowButton").on("click", function() {
                        $.ajax({
                            url: './php/unfollowUser.php', // Update the URL to your PHP script
                            type: 'POST',
                            data: {
                                'username': username
                            },
                            success: function(response) {
                                window.location.reload();
                                console.log(response); // Handle the response
                            },
                            error: function(xhr, status, error) {
                                console.error("An error occurred: " + error);
                            }
                        });
                    });
                } else {
                    console.error("Error in SQL STATEMENT")
                }

            })

            function editProfileButton() {
                // Toggle the visibility of the edit profile form
                var form = document.getElementById('editProfileForm');
                form.style.display = form.style.display === 'none' ? 'block' : 'none';
            }

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
                                var clickedUsername = $(this).data('username'); // Assuming you add data-username attribute to your <a> tag in searchUsers.php
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
