<?php
session_start();
include 'php/server_connection.php'; // Adjust the path as necessary

$connection = connect_to_database();

$query = "SELECT * FROM groups"; // Adjust the table name if necessary
$result = mysqli_query($connection, $query);

$groups = mysqli_fetch_all($result, MYSQLI_ASSOC);

mysqli_close($connection);
?>

<html>
    <head>
        <link rel="stylesheet" href="style.css">
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
                            <a href="groupFiles.html">Groups</a>
                            <a href="messages2.php">Messages</a>
                            <a href="help.html">Help</a>
                            <button><a href="profile.html">Profile</a></button>
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
                        <input type="checkbox" id="isPrivPub" name="is_privpub" value="private">
                        <span>Check this box if the group is private. Leave unchecked for public groups.</span>
                    </div>
                
                    <input type="submit" value="Create Group">
                </form>             
            </div>
        </section>

        <section class="groups" style="position: relative; bottom: 30px;">
            <div class="groupbox" style="left:50%; margin-top:50px; padding-bottom:20px; border:5px solid #8e0ce3; background:white; margin-left:60px; border-radius:10px; width:1320px;">
                <h1 style="margin-left:20px">My Groups</h1>
                <div class="row" style="display: flex; flex-wrap: wrap; justify-content: space-around;">
                    <!-- PHP code to loop through the groups and create the HTML for each one -->
                    <?php foreach ($groups as $group): ?>
                        <div class="group" style="margin-top: 20px; border: 5px solid #8e0ce3; border-radius: 10px; width: 600px;">
                            <a href="groupView2.html"><img style="width: 600px; border-bottom: 10px solid #8e0ce3;" src="<?php echo str_replace('../Images/', 'Images/', $group['groupdp']); ?>"></a>
                            <h1><?php echo $group['groupname']; ?></h1>
                            <p><?php echo $group['groupdesc']; ?></p>
                            <!-- Display 'Private' or 'Public' based on group status -->
                            <h3 style="color:gray"><?php echo $group['is_privpub'] === 'private' ? 'Private Group' : 'Public Group'; ?></h3>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <script>
            function createGroup() {
                // Toggle the visibility of the edit profile form
                var form = document.getElementById('createImagePost');
                form.style.display = form.style.display === 'none' ? 'block' : 'none';
            }

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
                    }, 3000); // Delay to match the transition
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