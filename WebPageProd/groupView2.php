<?php
session_start();
include 'php/server_connection.php'; // Adjust the path as necessary

// Get groupid from URL
$groupid = isset($_GET['groupid']) ? $_GET['groupid'] : null;

$groupDetails = null;
$groupMembers = [];
$userIsAdmin = false; // Initialize user admin status

if ($groupid) {
    $connection = connect_to_database();

    // Fetch group details
    $query = "SELECT * FROM groups WHERE groupid = ?";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "i", $groupid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $groupDetails = mysqli_fetch_assoc($result);

    mysqli_free_result($result);

    // Fetch member usernames and permissions
    $memberQuery = "SELECT username, gpermissions, fpermissions FROM group_participants WHERE groupid = ?";
    $memberStmt = mysqli_prepare($connection, $memberQuery);
    mysqli_stmt_bind_param($memberStmt, "i", $groupid);
    mysqli_stmt_execute($memberStmt);
    $memberResult = mysqli_stmt_get_result($memberStmt);

    while ($row = mysqli_fetch_assoc($memberResult)) {
        $groupMembers[] = $row;

        // Check if logged-in user is admin
        if ($row['username'] == $_SESSION['username'] && $row['gpermissions'] == 'admin') {
            $userIsAdmin = true;
        }
    }

    mysqli_free_result($memberResult);
    mysqli_close($connection);
}

if (!$groupDetails) {
    echo "Group not found.";
    exit;
}
?>

<html>
    <head>
        <link rel="stylesheet" href="groupView.css">
        <title> CreativSync - Profile</title>
        <link rel="icon" href="Images/logo.png">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
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
                        <input type="text" id="search" placeholder="Type to search..." onkeyup="searchUsers(this.value)">
                        <button id="searchBtn"><i class="fa fa-search"></i></button>
                        <!--<button id="clearBtn"><i class="fa fa-times"></i></button>-->
                    </div>
                    <div id="searchResults"></div>
                </div>
            </nav>
        </section>   

        <div class="groupviewDashboard">
            <div class="memberContainer">
                <!-- <h1 style="color: white;">Group Name</h1> -->
                <div class="groupTitle"><h1><?php echo $groupDetails['groupname']; ?></h1></div>
                <button class="leaveGroupBut"><a href="#">Leave Group</a></button>
                <div class="memSearch">
                    <div class="searchDiv">
                        <input type="text" id="search" placeholder="Type to search...">
                        <button id="searchBtn"><i class="fa fa-search"></i></button>
                        <!--<button id="clearBtn"><i class="fa fa-times"></i></button>-->
                    </div>
                    <div id="searchResults"></div>
                </div>
                <aside class="members">
                    <h2 class="h2">Members</h2>
                    <?php foreach ($groupMembers as $member): ?>
                        <div class="member">
                            <?php echo $member['username']; ?> - <?php echo $member['gpermissions']; ?> - <?php echo $member['fpermissions']; ?>
                        </div>
                    <?php endforeach; ?>
                </aside>
            </div>
    
            <div class="groupView">
                <nav>
                    <div class="groupSections">
                        <a href="#" onclick="showFiles()">Files</a>
                        <a href="#" onclick="showPosts()">Posts</a>
                        <a href="#" onclick="showLog()">Activity Log</a> 
                        <?php if ($userIsAdmin): ?>
                            <a href="#" onclick="showMorderation()">Moderation</a>
                        <?php endif; ?>
                        <a href="#">Canvas</a>
                        <a href="#">New File</a>                        
                        <button><a href="groupMessages.html">Group Chat</a></button>
                    </div>
                </nav>
                
                <div class="groupViewMain" style="display: block;">
                    <div style="display:block;" class="filesSection">
                        <div class="addFile">
                            <div class="inputfilesec">
                                <label for="profileImageInput">Add File:</label>
                                <input type="file" id="profileImageInput" name="profileImage" accept="image/*">
                            </div>
                            <button><a href="groupMessages.html">Submit File</a></button>
                        </div>
                        
                        <nav>
                            <div class="fileIcon"><i class="fa fa-files-o"></i></div>
                            <div class="fileName"><h3>Name</h3></div>
                            <div class="lastChangedby">Last Changed By</div>
                            <div class="dateChanged">Modified</div>
                            <div class="removeFile">Remove File</div>
                        </nav>

                        <div class="fileSec">
                            <div class="fileSecEach">
                                <div class="fileIconEach"><i class="fa fa-file"></i></div>
                                <div class="fileNameEach">To Do list</div>
                                <div class="lastChangedbyEach">Bishal Roy</div>
                                <div class="dateChangedEach">February 14th</div>
                                <div class="removeFileEach"><i class="fa fa-trash-o"></i></div>
                            </div>

                            <div class="fileSecEach">
                                <div class="fileIconEach"><i class="fa fa-file"></i></div>
                                <div class="fileNameEach">To Do list</div>
                                <div class="lastChangedbyEach">Bishal Roy</div>
                                <div class="dateChangedEach">February 14th</div>
                                <div class="removeFileEach"><i class="fa fa-trash-o"></i></div>
                            </div>
    
                            <div class="fileSecEach">
                                <div class="fileIconEach"><i class="fa fa-file"></i></div>
                                <div class="fileNameEach">To Do list</div>
                                <div class="lastChangedbyEach">Bishal Roy</div>
                                <div class="dateChangedEach">February 14th</div>
                                <div class="removeFileEach"><i class="fa fa-trash-o"></i></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="groupViewMain2" style="display: none;">
                <!-- maybe feed posts -->
                <div class="feedHead">
                        <div class="groupSettings" style="color:white; display:flex; position: relative; left: 130px;">
                            <h1>Post Something!</h4>
                            <button class = "otherButton" onclick="createImagePostButton()" style="color:white; font-size:x-large; height: 40px; width: 150px; margin-top: 20px; margin-left: 10px;">Post Image</button>
                            <button class = "otherButton" onclick="createTextPostButton()" style="color:white; font-size:x-large; height: 40px; width: 150px; margin-top: 20px; margin-left: 10px;">Post Text</button>
                        </div>
                    </div>
                    
                    <!-- Hidden form for profile editing -->
                    <div id="createImagePost" style="display: none;">
                        <form id="profileForm" enctype="multipart/form-data" action="php/insertPostImage.php" method="post">
                            <label for="profileImageInput">Image post:</label>
                            <input type="file" id="profileImageInput" name="profileImage" accept="image/*">
                            <!-- <label for="fullNameInput">Full Name:</label>
                            <input type="text" id="fullNameInput" name="fullName"> -->
                            <label for="aboutMeInput">Add a Caption:</label>
                            <textarea id="aboutMeInput" name="aboutMe"></textarea>
                            <input type="submit" value="Post!">
                        </form>
                    </div>
                    
                     <!-- Hidden form for profile editing -->
                     <div id="createTextPost" style="display: none;">
                        <form id="profileForm" enctype="multipart/form-data" action="php/insertPostText.php" method="post">
                            <!-- <label for="profileImageInput">Image post:</label>
                            <input type="file" id="profileImageInput" name="profileImage" accept="image/*"> -->
                            <label for="aboutMeInput">Write Something!:</label>
                            <textarea id="aboutMeInput" name="aboutMe"></textarea>
                            <label for="fullNameInput">Add a Caption:</label>
                            <input type="text" id="fullNameInput" name="fullName">
                            <input type="submit" value="Post!">
                        </form>
                    </div>

                    <div class="feedContainer">
                        <div class="feedItem">
                            <div class="CommentsContainer" >    <!-- COMMENTS SECTION -->
                                <div class="CommentsHeader">Comments</div>
                                <div class="CommentsScrollBox">
        
                                    <div class="CommentInfo"> <!-- CONTAINER FOR A COMMENT AND USER AVATAR + NAME -->
                                        <image class="CommentAvatar" src="Images/defaultavatar.png"></image>
                                        <p class="commentUsername">LarryDavid1996</p>
                                    </div>
                                    <div class="Comment">Hello!</div>
        
                                    <div class="CommentInfo">
                                        <image class="CommentAvatar" src="Images/defaultavatar.png"></image>
                                        <p class="commentUsername">T.Georgiou420</p>
                                    </div>
                                    <div class="Comment">Awesome post!</div>
        
                                    <div class="CommentInfo">
                                        <image class="CommentAvatar" src="Images/defaultavatar.png"></image>
                                        <p class="commentUsername">SusanCartwright</p>
                                    </div>
                                    <div class="Comment">Hey, did you get my message last night about the meeting? Its important!!</div>
        
                                    <div class="CommentInfo">
                                        <image class="CommentAvatar" src="Images/defaultavatar.png"></image>
                                        <p class="commentUsername">JCenaUCS</p>
                                    </div>
                                    <div class="Comment">Howdy!! Awesome photo!</div>
        
                                    <div class="CommentInfo">
                                        <image class="CommentAvatar" src="Images/defaultavatar.png"></image>
                                        <p class="commentUsername">sampsonJ321</p>
                                    </div>
                                    <div class="Comment">This reminds me of the last time I went on holiday, probably around 10 years ago now?</div>
        
                                    <div class="CommentInfo">
                                        <image class="CommentAvatar" src="Images/defaultavatar.png"></image>
                                        <p class="commentUsername">Konstance1</p>
                                    </div>
                                    <div class="Comment">Working hard or hardly working??</div>
        
                                    <div class="CommentInfo">
                                        <image class="CommentAvatar" src="Images/defaultavatar.png"></image>
                                        <p class="commentUsername">LarryDavid1996</p>
                                    </div>
                                    <div class="Comment">Nice :D</div>
                                </div>
                                
                                <!-- Input for text and the send button for a user to add a comment. -->
                                <div class = messageInputContainer>
                                    <input class="messageInput">
                                    <button class ="sendCommentButton">Send</button>
                                </div>
                            </div>
        
                            <!--Text or image content of a post-->
                            <div class="postContent">
                                <Image class="postImage" src="Images/grouplogo4.webp">   
                                <div class="posterInfo">
                                    <div class="postInfoUsernameAvatar">
                                        <image class="posterAvatar" src="Images/defaultAvatar.png"></image>
                                        <p class="posterUsername">oh2002</p>
                                    </div>
                                    <div class="postCaption">
                                        <p class="postCaptionText">Here's a little painting I made earlier, just thought I'd share!</p>
                                    </div>
                                    <div class="DateLikeContainer">
                                        <p class="postDate">02/03/2024</p>
                                        <button class="likeButton">Like</button>
                                    </div>
                                </div>
                            </div>
                        </div>
        
                        <div class="feedItem">
                            <div class="CommentsContainer" >    <!-- COMMENTS SECTION -->
                                <div class="CommentsHeader">Comments</div>
                                <div class="CommentsScrollBox">
        
                                    <div class="CommentInfo"> <!-- CONTAINER FOR A COMMENT AND USER AVATAR + NAME -->
                                        <image class="CommentAvatar" src="Images/defaultavatar.png"></image>
                                        <p class="commentUsername">LarryDavid1996</p>
                                    </div>
                                    <div class="Comment">Interesting!</div>
        
                                    <div class="CommentInfo"> <!-- CONTAINER FOR A COMMENT AND USER AVATAR + NAME -->
                                        <image class="CommentAvatar" src="Images/defaultavatar.png"></image>
                                        <p class="commentUsername">PSwift</p>
                                    </div>
                                    <div class="Comment">I should make a post about this!</div>
                                </div>
                                
                                <!-- Input for text and the send button for a user to add a comment. -->
                                <div class = messageInputContainer>
                                    <input class="messageInput">
                                    <button class ="sendCommentButton">Send</button>
                                </div>
                            </div>
        
                            <!--Text or image content of a post-->
                            <div class="postContent">
                                <div class="postTextContainer">
                                    <p class = "postText">Heres is some example text for a text-based post, hopefully everything works okay and the text fits fine. I will scream if it doesnt work properly. blah blah fill out the available space with some perhaps more elongated dialects to see if lengthy words spill over the side.</p>  
                                </div>
                                <div class="posterInfo">
                                    <div class="postInfoUsernameAvatar">
                                        <image class="posterAvatar" src="Images/defaultAvatar.png"></image>
                                        <p class="posterUsername">SusanCartwright</p>
                                    </div>
                                    <div class="postCaption">
                                        <p class="postCaptionText">What do you guys think? Am I doing well?</p>
                                    </div>
                                    <div class="DateLikeContainer">
                                        <p class="postDate">02/03/2024</p>
                                        <button class="likeButton">Like</button>
                                    </div>
                                </div> 
                            </div>
                        </div>
                    </div>
                </div>

                <div class="groupViewMain3" style="display: none;">
                    <!-- <h2>Activity Log</h2> -->
                    <table class="activity-log">
                    <tr>
                        <th>File</th>
                        <th>Edited By</th>
                        <th>Timestamp</th>
                        <th>Edit Description</th>
                    </tr>
                    <tr>
                        <td>Project_Plan.docx</td>
                        <td>John Doe</td>
                        <td class="timestamp">2024-03-02 14:35</td>
                        <td class="edit-description">Updated project objectives section.</td>
                    </tr>
                    <tr>
                        <td>Meeting_Notes.txt</td>
                        <td>Jane Smith</td>
                        <td class="timestamp">2024-03-02 09:20</td>
                        <td class="edit-description">Added minutes from March 1st meeting.</td>
                    </tr>
                    <!-- Add more log entries here -->
                    </table>
                </div>

                <div class="groupViewMain4" style="display: none;">
                    <div class="moderation-panel">
                        <h2>Moderation Panel</h2>
                
                        <!-- Role Management -->
                        <div class="role-management">
                            <h3>Role Management</h3>
                            <form action="php/update_grole.php" method="post">
                                <input type="hidden" name="groupid" value="<?php echo $groupid; ?>">
                                <label for="user-select">Select User:</label>
                                <select id="user-select" name="username">
                                    <?php foreach ($groupMembers as $member): ?>
                                        <option name="role" value="<?php echo $member['username']; ?>"><?php echo $member['username']; ?> - <?php echo $member['gpermissions']; ?></option>
                                    <?php endforeach; ?>
                                </select>

                                <label for="role-select">Assign Role:</label>
                                <select id="role-select" name="grole">
                                    <option value="member">Member</option>
                                    <option value="admin">Admin</option>
                                </select>

                                <button type="submit" id="assign-role" class="modButton">Assign Role</button>
                            </form>

                            <form action="php/update_frole.php" method="post">
                                <input type="hidden" name="groupid" value="<?php echo $groupid; ?>">
                                <label for="user-select">Select User:</label>
                                <select id="user-select" name="username">
                                    <?php foreach ($groupMembers as $member): ?>
                                        <option name="frole" value="<?php echo $member['username']; ?>"><?php echo $member['username']; ?> - <?php echo $member['fpermissions']; ?></option>
                                    <?php endforeach; ?>
                                </select>

                                <label for="role-select">Assign Role:</label>
                                <select id="role-select" name="frole">
                                    <option value="editor">Editor</option>
                                    <option value="viewer">Viewer</option>
                                </select>

                                <button type="submit" id="assign-fp" class="modButton">Assign Role</button>
                            </form>
                        </div>
                
                        <!-- Message Deletion 
                        <div class="message-deletion">
                            <h3>Message Deletion</h3>
                            Messages will be dynamically loaded here
                            Example message with a delete button
                            <div class="message">
                                <p>Example message text</p>
                                <button class="delete-message" class="modButton">Delete</button>
                            </div>
                        </div>-->
                
                        <!-- User Addition Panel -->
                        <div class="user-addition-panel">
                            <h3>Add User to Group</h3>
                            <!-- Add User to Group Form -->
                            <form action="php/add_user_to_group.php" enctype="multipart/form-data" method="post">
                                <input type="hidden" name="groupid" value="<?php echo $groupid; ?>">
                                <input type="text" name="userSearch" id="userSearch" placeholder="Search for a user...">
                                <input type="submit" id="searchUserButton" value="Add To Group">
                                <div id="userSearchResults" class="user-search-results"></div>
                            </form>
                        </div>

                        
                        <!-- User Removal from Groups -->
                        <div class="user-removal">
                            <h3>User Removal from Groups</h3>
                            <!-- Group members will be dynamically loaded here -->
                            <!-- Example member with a remove button -->
                            <form action="php/removeFromGroup.php" enctype="multipart/form-data" method="post">
                                <div class="group-member">
                                    <input type="hidden" name="groupid" value="<?php echo $groupid; ?>">
                                    <p>Username</p>
                                    <select id="user-select" name="username">
                                        <?php foreach ($groupMembers as $member): ?>
                                            <option name="frole" value="<?php echo $member['username']; ?>"><?php echo $member['username']; ?> - <?php echo $member['gpermissions'];?> - <?php echo $member['fpermissions']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="remove-user" class="modButton">Remove from Group</button>
                                </div>
                            </form>
                        </div>
                
                        <!-- Block Users
                        <div class="block-users">
                            <h3>Block Users</h3>
                            Users will be dynamically loaded here
                             Example user with a block button
                            <div class="user">
                                <p>Username</p>
                                <button class="block-user" class="modButton">Block User</button>
                            </div>
                        </div> -->
                
                        <!-- Content Filter Toggle -->
                        <div class="content-filter">
                            <h3>Content Filter</h3><br>
                            <div class="filtercheckbox">
                                <label for="content-filter-toggle">Filter Inappropriate Content:</label>
                                <input type="checkbox" id="content-filter-toggle">
                            </div>
                        </div>
                        <button class="otherButton" onclick="createTextPostButton()" style="color:white; font-size:25px; height: 80px; width: 150px; margin-top: 20px;">Submit Changes</button>
                    </div>
                </div>
            </div>
        </div>

        
        
        <script>
           function showFiles() {
                var groupViewMain = document.getElementsByClassName('groupViewMain')[0];
                var groupViewMain2 = document.getElementsByClassName('groupViewMain2')[0];
                var groupViewMain3 = document.getElementsByClassName('groupViewMain3')[0];
                var groupViewMain4 = document.getElementsByClassName('groupViewMain4')[0];
                groupViewMain.style.display = "block";
                groupViewMain2.style.display = "none";
                groupViewMain3.style.display = "none";
                groupViewMain4.style.display = "none";
            }

            function showPosts() {
                var groupViewMain = document.getElementsByClassName('groupViewMain')[0];
                var groupViewMain2 = document.getElementsByClassName('groupViewMain2')[0];
                var groupViewMain3 = document.getElementsByClassName('groupViewMain3')[0];
                var groupViewMain4 = document.getElementsByClassName('groupViewMain4')[0];
                groupViewMain.style.display = "none";
                groupViewMain2.style.display = "block";
                groupViewMain3.style.display = "none";
                groupViewMain4.style.display = "none";
            }

            function showLog() {
                var groupViewMain = document.getElementsByClassName('groupViewMain')[0];
                var groupViewMain2 = document.getElementsByClassName('groupViewMain2')[0];
                var groupViewMain3 = document.getElementsByClassName('groupViewMain3')[0];
                var groupViewMain4 = document.getElementsByClassName('groupViewMain4')[0];
                groupViewMain.style.display = "none";
                groupViewMain2.style.display = "none";
                groupViewMain3.style.display = "block";
                groupViewMain4.style.display = "none";
            }

            function showMorderation() {
                var groupViewMain = document.getElementsByClassName('groupViewMain')[0];
                var groupViewMain2 = document.getElementsByClassName('groupViewMain2')[0];
                var groupViewMain3 = document.getElementsByClassName('groupViewMain3')[0];
                var groupViewMain4 = document.getElementsByClassName('groupViewMain4')[0];
                groupViewMain.style.display = "none";
                groupViewMain2.style.display = "none";
                groupViewMain3.style.display = "none";
                groupViewMain4.style.display = "block";
            }
        </script>


        

        <script>
            function createImagePostButton() {
                // Toggle the visibility of the edit profile form
                var form = document.getElementById('createImagePost');
                form.style.display = form.style.display === 'none' ? 'block' : 'none';
            }

            function createTextPostButton() {
                // Toggle the visibility of the edit profile form
                var form = document.getElementById('createTextPost');
                form.style.display = form.style.display === 'none' ? 'block' : 'none';
            }
        </script>

        
    </body>
</html>