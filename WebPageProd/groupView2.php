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
    

    // Query to get all posts from a specific group including likes and comments count
    $query = "SELECT p.*, u.profile_image, COUNT(l.postid) as like_count, 
              (SELECT COUNT(*) FROM comments c WHERE c.postid = p.postid) as comment_count
              FROM posts p
              LEFT JOIN profile u ON p.username = u.username
              LEFT JOIN likes l ON p.postid = l.postid
              WHERE p.groupid = ?
              GROUP BY p.postid
              ORDER BY p.created_at DESC";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "i", $groupid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $feedPosts = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $row['liked_by_user'] = false; // Default value, as we don't know yet if the user liked the post
        $feedPosts[] = $row;
    }

    // For each post, check if the user liked it
    foreach ($feedPosts as $key => $post) {
        $like_check_query = "SELECT COUNT(*) as like_count FROM likes WHERE username = ? AND postid = ?";
        $stmt_like_check = mysqli_prepare($connection, $like_check_query);
        mysqli_stmt_bind_param($stmt_like_check, "si", $_SESSION['username'], $post['postid']);
        mysqli_stmt_execute($stmt_like_check);
        $result_like_check = mysqli_stmt_get_result($stmt_like_check);
        $like_data = mysqli_fetch_assoc($result_like_check);
        $feedPosts[$key]['liked_by_user'] = $like_data['like_count'] > 0;
        mysqli_stmt_close($stmt_like_check);
    }
    
    mysqli_stmt_close($stmt);


    // Query to get all files for the specific group
    $fileQuery = "SELECT file_id, file_name, last_changed_by, modified FROM group_files WHERE groupid = ? ORDER BY modified DESC";
    $fileStmt = mysqli_prepare($connection, $fileQuery);
    mysqli_stmt_bind_param($fileStmt, "i", $groupid);
    mysqli_stmt_execute($fileStmt);
    $fileResult = mysqli_stmt_get_result($fileStmt);

    $files = array();
    while ($fileRow = mysqli_fetch_assoc($fileResult)) {
        $files[] = $fileRow;
    }

    mysqli_stmt_close($fileStmt);

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
                <form action="php/leaveGroup.php" method="post">
                    <input type="hidden" name="groupid" value="<?php echo $groupid; ?>">
                    <input type="hidden" name="username" value="<?php echo $_SESSION['username']; ?>">
                    <button type="submit" class="leaveGroupBut">Leave Group</button>
                </form>
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
                        <button><a href="groupMessages.php?groupid=<?php echo $groupid; ?>">Group Chat</a></button>
                    </div>
                </nav>
                
                <div class="groupViewMain" style="display: block;">
                    <div style="display:block;" class="filesSection">
                        <div class="addFile">
                            <div class="inputfilesec">
                                <form action="php/submittingFile.php" method="post" enctype="multipart/form-data">
                                    <input type="hidden" name="groupid" value="<?php echo $groupid; ?>">
                                    <label for="fileToUpload">Select file to upload:</label>
                                    <input type="file" name="fileToUpload" id="fileToUpload">
                                    <input type="submit" value="Submit File">
                                    <!-- <button type="submit" value="Submit File"><a href="#">Submit File</a></button> -->
                                </form>
                            </div>
                        </div>
                        
                        <nav>
                            <div class="fileIcon"><i class="fa fa-files-o"></i></div>
                            <div class="fileName"><h3>Name</h3></div>
                            <div class="lastChangedby">Last Changed By</div>
                            <div class="dateChanged">Modified</div>
                            <div class="removeFile">Remove File</div>
                            <div class="removeFile">Download File</div>
                        </nav>

                        <div class="fileSec">

                        <?php foreach ($files as $file): ?>
                        <div class="fileSecEach">
                            <div class="fileIconEach"><i class="fa fa-file"></i></div>
                            <div class="fileNameEach"><?php echo htmlspecialchars($file['file_name']); ?></div>
                            <div class="lastChangedbyEach"><?php echo htmlspecialchars($file['last_changed_by']); ?></div>
                            <div class="dateChangedEach"><?php echo htmlspecialchars(date("F jS, Y", strtotime($file['modified']))); ?></div>
                            <div class="removeFileEach" onclick="removeFile(<?php echo $file['file_id']; ?>)"><i class="fa fa-trash-o"></i></div>
                            <!-- Download Button -->
                            <div class="downloadFileEach">
                                <a href="uploads/<?php echo $file['file_name']; ?>" download="<?php echo $file['file_name']; ?>">
                                    <button type="button"><i class="fa fa-arrow-down"></i></button>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>


                            <!-- <div class="fileSecEach">
                                <div class="fileIconEach"><i class="fa fa-file"></i></div>
                                <div class="fileNameEach">To Do list</div>
                                <div class="lastChangedbyEach">Bishal Roy</div>
                                <div class="dateChangedEach">February 14th</div>
                                <div class="removeFileEach"><i class="fa fa-trash-o"></i></div>
                            </div> -->
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
                        <form id="profileForm" enctype="multipart/form-data" action="php/insertPostImageGroup.php" method="post">
                            <input type="hidden" name="groupid" value="<?php echo $groupid; ?>">
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
                        <form id="profileForm" enctype="multipart/form-data" action="php/insertPostTextGroup.php" method="post">
                            <!-- <label for="profileImageInput">Image post:</label>
                            <input type="file" id="profileImageInput" name="profileImage" accept="image/*"> -->
                            <input type="hidden" name="groupid" value="<?php echo $groupid; ?>">
                            <label for="aboutMeInput">Write Something!:</label>
                            <textarea id="aboutMeInput" name="aboutMe"></textarea>
                            <label for="fullNameInput">Add a Caption:</label>
                            <input type="text" id="fullNameInput" name="fullName">
                            <input type="submit" value="Post!">
                        </form>
                    </div>

                    <div class="feedContainer">
                    <?php foreach ($feedPosts as $post): ?>
                        <div class="feedItem">

                        <div class="CommentsContainer" id="commentsContainer<?php echo $post['postid']; ?>">    <!-- COMMENTS SECTION -->
                            <div class="CommentsHeader">Comments</div>
                            <div class="CommentsScrollBox" id="commentsScrollBox<?php echo $post['postid']; ?>">

                                <!-- <div class="CommentInfo"> 
                                    <image class="CommentAvatar" src="Images/defaultavatar.png"></image>
                                    <p class="commentUsername">LarryDavid1996</p>
                                </div>
                                <div class="Comment">Interesting!</div> -->

                                <div class="CommentInfo"> <!-- CONTAINER FOR A COMMENT AND USER AVATAR + NAME -->
                                    <image class="CommentAvatar" src="Images/defaultavatar.png"></image>
                                    <p class="commentUsername">PSwift</p>
                                </div>
                                <div class="Comment">I should make a post about this!</div>
                            </div>
                            
                            <!-- Input for text and the send button for a user to add a comment. -->
                            <!-- Input for text and the send button for a user to add a comment. -->
                            <div class="messageInputContainer">
                                <input class="messageInput" id="commentInput<?php echo $post['postid']; ?>" data-postid="<?php echo $post['postid']; ?>">
                                <button class="sendCommentButton" onclick="sendComment(<?php echo $post['postid']; ?>)">Send</button>
                            </div>
                        </div>
                            <!-- Display comments section, input, etc. -->
                            <!-- Your existing HTML code for comments section -->

                            <div class="postContent">
                                <?php if (!empty($post['content_image'])): ?>
                                    <!-- If it's an image post -->
                                    <!-- <img class="postImage" src="<?php echo $post['content_image']; ?>"> -->
                                    <img class="postImage" src="<?php echo str_replace('../Images/', 'Images/', $post['content_image']); ?>">
                                <?php else: ?>
                                    <!-- If it's a text post -->
                                    <div class="postTextContainer">
                                        <p class="postText"><?php echo $post['content_text']; ?></p>
                                    </div>
                                <?php endif; ?>
                                <div class="posterInfo">
                                    <div class="postInfoUsernameAvatar">
                                    <img class="posterAvatar" src="<?php echo !empty($post['profile_image']) ? $post['profile_image'] : 'Images/defaultAvatar.png'; ?>">
                                        <p class="posterUsername"><?php echo $post['username']; ?></p>
                                    </div>
                                    <div class="postCaption">
                                        <p class="postCaptionText"><?php echo $post['caption']; ?></p>
                                    </div>
                                    <div class="DateLikeContainer">
                                        <p class="postDate"><?php echo date("m/d/Y", strtotime($post['created_at'])); ?></p>
                                        <button class="likeButton" 
                                            onclick="likePost(<?php echo $post['postid']; ?>)"
                                            data-postid="<?php echo $post['postid']; ?>">
                                        <?php echo $post['liked_by_user'] ? 'Unlike' : 'Like'; ?>
                                    </button>
                                    <span class="likeCount" id="likeCount<?php echo $post['postid']; ?>">
                                        <?php echo $post['like_count']; ?>
                                    </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
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
                                <button type="submit" class="leaveGroupBut">Leave Group</button>
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

                    <div class="block-users">
                        <h3>Delete Group</h3>
                        <div class="user">
                            <form action="php/deleteGroup.php" method="post">
                                <input type="hidden" name="groupid" value="<?php echo $groupid; ?>">
                                <button type="submit" class="deleteGroupBut">Delete Group</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <script>
            function removeFile(fileId) {
                if (confirm('Are you sure you want to delete this file?')) {
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', 'php/deleteFile.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onload = function () {
                        if (xhr.status === 200) {
                            var response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                // Reload the page after a short delay to see the changes
                                setTimeout(function(){
                                    window.location.reload();
                                }, 500); // Delay of 500 milliseconds
                            } else {
                                alert('Could not delete the file.');
                            }
                        } else {
                            alert('An error occurred while deleting the file.');
                        }
                    };
                    xhr.send('fileId=' + fileId);
                }
            }
        </script>
        
        <script>
            function likePost(postId) {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "php/likePost.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === XMLHttpRequest.DONE) {
                        if (xhr.status === 200) {
                            var response = JSON.parse(xhr.responseText);
                            // Update the like count display
                            document.getElementById('likeCount' + postId).textContent = response.likeCount;
                            // Toggle the button text between Like and Unlike
                            var likeButton = document.querySelector('.likeButton[data-postid="' + postId + '"]');
                            if (response.action === 'liked') {
                                likeButton.textContent = 'Unlike';
                            } else if (response.action === 'unliked') {
                                likeButton.textContent = 'Like';
                            }
                        } else {
                            console.error('Error:', xhr.statusText);
                        }
                    }
                };
                xhr.send("postId=" + postId);
            }
        </script>

        <script>
            window.onload = function () {
                // Load comments for each post
                <?php foreach ($feedPosts as $post): ?>
                    loadComments(<?php echo $post['postid']; ?>);
                <?php endforeach; ?>
            };

            function loadComments(postId) {
                var xhr = new XMLHttpRequest();
                xhr.open("GET", "php/getComments.php?postId=" + postId, true);
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === XMLHttpRequest.DONE) {
                        if (xhr.status === 200) {
                            // Load comments into the comments scroll box
                            document.getElementById('commentsScrollBox' + postId).innerHTML = xhr.responseText;
                        } else {
                            console.error('Error:', xhr.statusText);
                        }
                    }
                };
                xhr.send();
            }



            function sendComment(postId) {
                var commentInput = document.getElementById('commentInput' + postId)
                var commentValue = commentInput.value.trim();
                if (commentValue === '') {
                    // Handle empty comment
                    return;
                }

                // AJAX request to insert comment
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "php/insertComment.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === XMLHttpRequest.DONE) {
                        if (xhr.status === 200) {
                            // Handle successful insertion
                            console.log(xhr.responseText);
                            commentInput.value = '';
                        } else {
                            // Handle error
                            console.error('Error:', xhr.statusText);
                        }
                    }
                };
                xhr.send("postId=" + postId + "&comment=" + encodeURIComponent(commentValue));
            }

            function loadComments(postId) {
                var xhr = new XMLHttpRequest();
                xhr.open("GET", "php/getComments.php?postId=" + postId, true);
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === XMLHttpRequest.DONE) {
                        if (xhr.status === 200) {
                            // Load comments into the comments scroll box
                            document.getElementById('commentsScrollBox' + postId).innerHTML = xhr.responseText;
                        } else {
                            console.error('Error:', xhr.statusText);
                        }
                    }
                };
                xhr.send();
            }
        </script>
        
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
