<?php
    include 'php/actionLogin.php';
    // Add your database connection and query here to retrieve profile information
    $connection = connect_to_database();


    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];
        
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
    </head>
    <body>
        <section class="navigation">
            <nav>
                <div class="mainnav">
                    <div class="imgnav"><img src="Images/logo.png"></div>
                    <div class="compname"><h1 style="color:white;">CreativSync</h1></div>
                    <div class="navi">
                            <a href="feed.html">Feed</a>
                            <a href="notifications.html">Notifications</a>
                            <a href="search.html">Search</a> 
                            <a href="groups.html">Groups</a>
                            <a href="help.html">Help</a>
                            <a href="messages2.php">Messages</a>
                            <!--<button><a href="profile.html">Profile</a></button>-->
                    </div>
                </div>
            </nav>
        </section>   
        <section>
            <div class = "profileContainer">
                <p class = "profileRealName"><?php echo $realName; ?></p>
                <!--<p class = "profileUsername">JSmith2024</p>-->
                <p class = "profileUsername"><?php echo $_SESSION['username']; ?></p>
                <button id="logoutButton" class="logoutButton" onclick ="window.location.href='php/logout.php';">Logout</button>
                <button id="editProfileButton" class="editProfileButton" onclick="editProfileButton()">Edit Profile</button>
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
                    <button class = "profileFollowButton">Follow</button>
                    <button class = "profileCopyEmailButton">Copy Email</button>
                    <button class = "profileInviteButton">Invite to Group</button>
                    <button class = "profileBlockButton">Block</button>
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
            function editProfileButton() {
                // Toggle the visibility of the edit profile form
                var form = document.getElementById('editProfileForm');
                form.style.display = form.style.display === 'none' ? 'block' : 'none';
            }         
        </script>
    </body>
</html>
