<?php
session_start();
include 'php/server_connection.php'; // Adjust the path as necessary

$connection = connect_to_database();

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    echo "You must be logged in to view your groups.";
    exit;
}

$username = mysqli_real_escape_string($connection, $_SESSION['username']);

// Query to select groups where the logged-in user is a participant
$query = "SELECT `g`.* FROM `groups` `g`
          INNER JOIN `group_participants` `gp` ON `g`.`groupid` = `gp`.`groupid`
          WHERE `gp`.`username` = '$username'";

$result = mysqli_query($connection, $query);

if (!$result) {
    echo "Error: " . mysqli_error($connection);
    exit;
}

$groups = mysqli_fetch_all($result, MYSQLI_ASSOC);

mysqli_close($connection);
?>


<html>
    <head>
        <link rel="stylesheet" href="style.css">
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
        <section class="preGroup">
            <div class="groupSettings" style="color:white; display:flex; position: relative; left: 70px; top: 0px;">
                <h1>Create A Group</h4>
                <button class = "otherButton" onclick="createGroup()" style="color:white; font-size:x-large; height: 40px; width: 40px; margin-top: 15px; margin-left: 10px;">+</button>
            </div>

            <div id="createImagePost" style="display: none;">
                <form id="profileForm" enctype="multipart/form-data" action="php/insertGroup.php" method="post">
                    <label for="groupNameInput">Group Name:</label>
                    <input type="text" id="profileImageInput" name="groupname" required>
                
                    <label for="groupImageInput">Group Profile Image:</label>
                    <input type="file" id="profileImageInput" name="groupdp" accept="image/*">
                
                    <label for="groupDescInput">Group Description:</label>
                    <textarea id="aboutMeInput" name="groupdesc"></textarea>
                
                    <div>
                        <label for="isPrivPub">Private Group:</label>
                        <input type="checkbox" id="isPrivPub" name="type" value="private">
                        <span>Check this box if the group is private. Leave unchecked for public groups.</span>
                    </div>
                
                    <input type="submit" value="Create Group">
                </form>             
            </div>
        </section>

        <section class="groups" style="position: relative;">
            <div class="groupbox" style="margin-top: 50px; padding-bottom: 20px; border: 5px solid #8e0ce3; background: white; margin: 0 auto; border-radius: 10px; max-width: 1320px; display: flex; flex-wrap: wrap; justify-content: space-evenly;">
                <h1 style="width: 100%; text-align: center; margin-top: 0; padding-top: 20px;">My Groups</h1>
                <!-- PHP code to loop through the groups and create the HTML for each one -->
                <?php foreach ($groups as $group): ?>
                    <div class="group" style="flex: 0 0 calc(33.333% - 40px); /* Three items per row with spacing */ margin: 20px; border: 5px solid #8e0ce3; border-radius: 10px; display: flex; flex-direction: column; align-items: center; overflow: hidden;">
                        <a href="groupView2.php?groupid=<?php echo $group['groupid']; ?>" style="width: 100%; display: block;">
                            <img style="width: 100%; height: 300px; object-fit: cover; object-position: 50% 50%;" src="<?php echo str_replace('../Images/', 'Images/', $group['groupdp']); ?>">
                        </a>
                        <div style="padding: 10px; text-align: center;">
                            <h2 style="margin: 0;"><?php echo $group['groupname']; ?></h2>
                            <p><?php echo $group['groupdesc']; ?></p>   
                            <h3 style="color: gray;"><?php echo $group['type'] === 'private' ? 'Private Group' : 'Public Group'; ?></h3>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>


        <script>
	function createGroup() {
                // Toggle the visibility of the edit profile form
                var form = document.getElementById('createImagePost');
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
