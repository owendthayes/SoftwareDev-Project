<?php
session_start();
include 'php/server_connection.php'; // Adjust the path as necessary

// Get groupid from URL
$groupid = isset($_GET['groupid']) ? $_GET['groupid'] : null;

$userIsMember = false; // Initialize user member status
$groupDetails = null;
$userIsOwner = false;
$groupMembers = [];
$userIsAdmin = false; // Initialize user admin status
$userIsEditor = false; // Initialize user editor status

if ($groupid) {
    $connection = connect_to_database();

    // Fetch group details
    $query = "SELECT * FROM `groups` WHERE groupid = ?";
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

        // Check if logged-in user is admin
        if ($row['username'] == $_SESSION['username'] && $row['gpermissions'] == 'owner') {
            $userIsOwner = true;
        }

        // Check if logged-in user is editor
        if ($row['username'] == $_SESSION['username'] && $row['fpermissions'] == 'editor') {
            $userIsEditor = true;
        }

        // Check if the logged-in user is a member
        if ($row['username'] == $_SESSION['username']) {
            $userIsMember = true;
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
    $fileQuery = "SELECT file_id, file_name, uploaded_by, upload_time FROM group_files WHERE groupid = ? ORDER BY upload_time DESC";
    $fileStmt = mysqli_prepare($connection, $fileQuery);
    mysqli_stmt_bind_param($fileStmt, "i", $groupid);
    mysqli_stmt_execute($fileStmt);
    $fileResult = mysqli_stmt_get_result($fileStmt);

    $files = array();
    while ($fileRow = mysqli_fetch_assoc($fileResult)) {
        $files[] = $fileRow;
    }

    mysqli_stmt_close($fileStmt);

    // Query to get activity logs for the specific group
    $activityLogQuery = "SELECT * FROM activity_log WHERE groupid = ? ORDER BY timestamp DESC";
    $activityLogStmt = mysqli_prepare($connection, $activityLogQuery);
    mysqli_stmt_bind_param($activityLogStmt, "i", $groupid);
    mysqli_stmt_execute($activityLogStmt);
    $activityLogResult = mysqli_stmt_get_result($activityLogStmt);

    $activityLogs = array();
    while ($logRow = mysqli_fetch_assoc($activityLogResult)) {
        $activityLogs[] = $logRow;
    }

    mysqli_stmt_close($activityLogStmt);

    // Query to get pads
    
    $padsQuery = "SELECT DISTINCT SUBSTRING_INDEX(SUBSTRING_INDEX(`key`, '-', -1),':',1) AS `key` FROM `store` WHERE SUBSTRING_INDEX(`key`, '-', 1) = ?";
    $padsStmt = mysqli_prepare($connection, $padsQuery);
    $padid = "pad:".$groupid;
    mysqli_stmt_bind_param($padsStmt, "s", $padid);
    mysqli_stmt_execute($padsStmt);
    $padsResult = mysqli_stmt_get_result($padsStmt);

    $pads = array();
    while ($logRow = mysqli_fetch_assoc($padsResult)) {
        $pads[] =   $logRow["key"];
    }

    mysqli_stmt_close($padsStmt);

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
        <link rel="stylesheet" href="groupView.css?version=1">
        <title> CreativSync - Profile</title>
        <link rel="icon" href="Images/logo.png">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet"/>
    </head>
    <body>
        <section class="navigation">
            <nav>
                <div class="mainnav">
                    <div class="imgnav">
                        <a href="../home.html"><img src="Images/logo.png"></a>
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

        <div class="groupviewDashboard">
            <div class="memberContainer">
                <!-- <h1 style="color: white;">Group Name</h1> -->
                <div class="groupTitle"><h1><?php echo $groupDetails['groupname']; ?></h1></div>
                <form action="php/joinGroup.php" method="post">
                        <input type="hidden" name="groupid" value="<?php echo $groupid; ?>">
                        <input type="hidden" name="username" value="<?php echo $_SESSION['username']; ?>">
                        <?php if (!$userIsMember): ?>
                            <button type="submit" name="action" value="join" class="joinGroupBut">Join Group</button>
                        <?php else: ?>
                    </form>
                <form action="php/leaveGroup.php" method="post">
                    <input type="hidden" name="groupid" value="<?php echo $groupid; ?>">
                    <input type="hidden" name="username" value="<?php echo $_SESSION['username']; ?>">
                        <button type="submit" name="action" value="leave" class="leaveGroupBut">Leave Group</button>
                    <?php endif; ?>
                </form>
                <div class="memSearch">
                    <div class="searchDiv">
                        <input type="text" id="memberSearch" placeholder="Type to search members..." onkeyup="searchGroupMembers(this.value, <?php echo $groupid; ?>)">
                        <button id="searchBtn"><i class="fa fa-search"></i></button>
                    </div>
                </div>
                <aside class="members" id="members">
                    <h2 class="h2">Members</h2>
                    <div id="memberList">
                        <?php foreach ($groupMembers as $member): ?>
                            <div class="member">
                                <a style="color: black; text-decoration: none;" href="profile.php?user_id=<?php echo urlencode($member['username']); ?>">
                                    <?php echo htmlspecialchars($member['username']); ?>
                                </a> - <?php echo htmlspecialchars($member['gpermissions']); ?> - <?php echo htmlspecialchars($member['fpermissions']); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </aside>
            </div>
    
            <div class="groupView">
                <nav>
                    <div class="groupSections">
                        <a href="#" onclick="showFiles()">Files</a>
                        <a href="#" onclick="showPosts()">Posts</a>
                        <a href="#" onclick="showLog()">Activity Log</a> 
                        <?php if ($userIsAdmin || $userIsOwner): ?>
                            <a href="#" onclick="showMorderation()">Moderation</a>
                        <?php endif; ?>
                        <?php if ($userIsEditor): ?>
                            <a href="#" onclick="showCanvas()">Canvas</a>
                        <?php endif; ?>
                        <?php if ($userIsEditor): ?>
                            <a href="#" onclick="showfileEdit()">New File</a>
                        <?php endif; ?>               
                        <?php if ($userIsMember): ?>       
                            <button><a href="groupMessages.php?groupid=<?php echo $groupid; ?>">Group Chat</a></button>
                        <?php endif ?>
                    </div>
                </nav>
                
                <div class="groupViewMain" style="display: block;">
                    <div style="display:block;" class="filesSection">
                        <?php if ($userIsEditor): ?>
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
                        <?php endif; ?>
                        
                        <nav>
                            <div class="fileIcon"><i class="fa fa-files-o"></i></div>
                            <div class="fileName"><h3>Name</h3></div>
                            <div class="lastChangedby">Last Changed By</div>
                            <div class="dateChanged">Modified</div>
                            <?php if ($userIsMember): ?>
                                <div class="removeFile">Remove File</div>
                            <?php endif; ?>
                            <div class="removeFile">Download File</div>
                        </nav>

                        <div class="fileSec">

                        <?php foreach ($files as $file): ?>
                        <div class="fileSecEach">
                            <div class="fileIconEach"><i class="fa fa-file"></i></div>
                            <div class="fileNameEach"><?php echo htmlspecialchars($file['file_name']); ?></div>
                            <div class="lastChangedbyEach"><?php echo htmlspecialchars($file['uploaded_by']); ?></div>
                            <div class="dateChangedEach"><?php echo htmlspecialchars(date("F jS, Y", strtotime($file['upload_time']))); ?></div>
                            <?php if ($userIsMember): ?>
                                <div class="removeFileEach" onclick="removeFile(<?php echo $file['file_id']; ?>)"><i class="fa fa-trash-o"></i></div>
                            <?php endif; ?>
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

                        <div>
                        <?php if ($userIsMember): ?>
                            <div class="padsHead">
                                <h2>Collaborative Pads</h2>
                                <div class="padsHeadSub">
                                    <button class="otherButtonPad" onclick="showPad()">+</button>
                                    <div id="createPad" style="display: none;">
                                        <form id="padForm" enctype="multipart/form-data" onsubmit="return createPad();">
                                            <label for="padInput">Pad Name:</label>
                                            <input type="text" id="padNameInput" name="id" required> 
                                            <input type="hidden" name="gid" value="<?php echo $groupid; ?>"> 
                                            <input type="submit" value="Create Pad">
                                        </form>
                                        <a id="padError" style="display: none; color: red">Error Pad Already Exists</a>             
                                    </div>
                                </div>
                                <br>
                            </div>
                            <div class="padSec">
                                <?php foreach ($pads as $pad): ?>
                                    <div class="padSecEach">
                                        <div class="fileIconEach"><i class="fa fa-file"></i></div>
                                        <a href="./php/fileView.php/?gid=<?php echo $groupid . "&id=" . $pad; ?>" target="_blank"><?php echo $pad; ?></a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="groupViewMain2" style="display: none;">
                <!-- maybe feed posts -->
                    <div class="feedHead">
                        <?php if ($userIsMember): ?>
                            <div class="groupSettings" style="color:white; display:flex; position: relative; left: 130px;">
                                <h1>Post Something!</h4>
                                <button class = "otherButton" onclick="createImagePostButton()" style="color:white; font-size:x-large; height: 40px; width: 150px; margin-top: 20px; margin-left: 10px;">Post Image</button>
                                <button class = "otherButton" onclick="createTextPostButton()" style="color:white; font-size:x-large; height: 40px; width: 150px; margin-top: 20px; margin-left: 10px;">Post Text</button>
                            </div>
                        <?php endif ?>
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
                    <?php if ($userIsEditor): ?>
                        <div class="feedHead">
                            <div class="groupSettings" style="color:white; display:flex; position: relative; left: 130px;">
                                <h1>Create A New Activity Log:</h4>
                                <button class = "otherButton" onclick="createALOGButton()" style="color:white; font-size:x-large; height: 40px; width: 200px; margin-top: 20px; margin-left: 10px;">Create New Log</button>
                            </div>
                        </div>
                        
                        <!-- Hidden form -->
                        <div id="createAClog" style="display: none;">
                            <form id="profileForm" enctype="multipart/form-data" action="php/insertActivityLog.php" method="post">
                                <input type="hidden" name="groupid" value="<?php echo $groupid; ?>">
                                <label for="file_name">File Name:</label>
                                <input type="text" id="file_name" name="file_name"><br><br>

                                <label for="timestamp">Timestamp:</label>
                                <input type="datetime-local" id="timestamp" name="timestamp" value="<?php echo date('Y-m-d\TH:i'); ?>"><br><br>

                                <label for="edit_description">Edit Description:</label>
                                <textarea id="edit_description" name="edit_description"></textarea><br><br>

                                <input type="submit" value="Submit">
                            </form>
                        </div>
                    <?php endif; ?>
                    
                    <table class="activity-log">
                        <tr>
                            <th>File</th>
                            <th>Edited By</th>
                            <th>Timestamp</th>
                            <th>Edit Description</th>
                            <?php if ($userIsAdmin): ?>
                            <th>Delete Log</th>
                            <?php endif; ?>
                        </tr>
                        <?php foreach ($activityLogs as $log): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($log['file_name']); ?></td>
                            <td><?php echo htmlspecialchars($log['edited_by']); ?></td>
                            <td class="timestamp"><?php echo htmlspecialchars($log['timestamp']); ?></td>
                            <td class="edit-description"><?php echo htmlspecialchars($log['edit_description']); ?></td>
                            <?php if ($userIsAdmin): ?>
                            <td class="removeLog" onclick="deleteLog(<?php echo $log['id']; ?>)"><i class="fa fa-trash-o"></i></td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
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
                                        <?php if ($member['gpermissions'] !== 'owner'): ?>
                                            <option name="role" value="<?php echo $member['username']; ?>"><?php echo $member['username']; ?> - <?php echo $member['gpermissions']; ?></option>
                                        <?php endif; ?>
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
                            <div id="addError" class="error-message"></div>
                        </div>

                        
                        <!-- User Removal from Groups -->
                        <div class="user-removal">
                            <h3>User Removal from Groups</h3>
                            <!-- Group members will be dynamically loaded here -->
                            <form action="php/removeFromGroup.php" enctype="multipart/form-data" method="post">
                                <div class="group-member">
                                    <input type="hidden" name="groupid" value="<?php echo $groupid; ?>">
                                    <p>Username</p>
                                    <select id="user-select" name="username">
                                        <?php foreach ($groupMembers as $member): ?>
                                            <?php if ($member['gpermissions'] !== 'owner'): ?>
                                                <option name="frole" value="<?php echo $member['username']; ?>"><?php echo $member['username']; ?> - <?php echo $member['gpermissions'];?> - <?php echo $member['fpermissions']; ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="remove-user" class="modButton">Remove from Group</button>
                                </div>
                            </form>
                        </div>
                
                
                        <!-- Content Filter Toggle -->
                        <!-- <div class="content-filter">
                            <h3>Content Filter</h3><br>
                            <div class="filtercheckbox">
                                <label for="content-filter-toggle">Filter Inappropriate Content:</label>
                                <input type="checkbox" id="content-filter-toggle">
                            </div>
                        </div>
                        <button class="otherButton" onclick="createTextPostButton()" style="color:white; font-size:25px; height: 80px; width: 150px; margin-top: 20px;">Submit Changes</button> -->
                    </div>

                    <div class="block-users">
                    <?php if ($userIsOwner): ?>
                        <h3>Delete Group</h3>
                        <div class="user">
                            <form action="php/deleteGroup.php" method="post">
                                <input type="hidden" name="groupid" value="<?php echo $groupid; ?>">
                                <button type="submit" class="deleteGroupBut">Delete Group</button>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="groupViewMain5" style="display: none;">
                    <div class="textContainer">
                        <div class="textOptions">
                            <!-- Save/Download -->
                            <button id="save" onclick="saveTextFile()" class="optionButton format"><i class="fa fa-arrow-down"></i></button>
                            
                            <!-- Text Fromat -->
                            <button id="bold" class="optionButton format"><i class="fa fa-bold"></i></button>
                            <button id="italic" class="optionButton format"><i class="fa fa-italic"></i></button>
                            <button id="underline" class="optionButton format"><i class="fa fa-underline"></i></button>
                            <button id="strikeThrough" class="optionButton format"><i class="fa fa-strikethrough"></i></button>
                            <button id="indent" class="optionButton"><i class="fa fa-indent"></i></button>
                            <button id="subscript" class="optionButton script"><i class="fa fa-subscript"></i></button>
                            <button id="superscript" class="optionButton script"><i class="fa fa-superscript"></i></button>


                            <!-- List -->
                            <button id="insertOrderedList" class="optionButton"><i class="fa fa-list-ol"></i></button>
                            <button id="insertUnorderedList" class="optionButton"><i class="fa fa-list"></i></button>

                            <!-- Undo/Redo -->
                            <button id="undo" class="optionButton"><i class="fa fa-rotate-left"></i></button>
                            <button id="redo" class="optionButton"><i class="fa fa-rotate-right"></i></button>

                            <!-- Link -->
                            <button id="createLink" class="optionButton-cl"><i class="fa fa-link"></i></button>
                            <button id="unlink" class="optionButton"><i class="fa fa-unlink"></i></button>
                            

                            <!-- Alignment -->
                            <button id="justifyLeft" class="optionButton align"><i class="fa fa-align-left"></i></button>
                            <button id="justifyCenter" class="optionButton align"><i class="fa fa-align-center"></i></button>
                            <button id="justifyRight" class="optionButton align"><i class="fa fa-align-right"></i></button>
                            <button id="justifyFull" class="optionButton align"><i class="fa fa-align-justify"></i></button>
                            
                            <!-- Headings -->
                            <select id="formatBlock" class="optionButton-cl">
                                <option value="H1">H1</option>
                                <option value="H2">H2</option>
                                <option value="H3">H3</option>
                                <option value="H4">H4</option>
                                <option value="H5">H5</option>
                                <option value="H6">H6</option>
                            </select>

                            <!-- Font -->
                            <select id="fontName" class="optionButton-cl"></select>
                            <select id="fontSize" class="optionButton-cl"></select>

                            <!-- Colour -->
                            <div class="clInput">
                                <input type="color" id="foreColour" class="optionButton-cl">
                                <label for="foreColor">Front Colour</label>
                            </div>
                            <div class="clInput">
                                <input type="color" id="backColour" class="optionButton-cl">
                                <label for="backColor">Highlight Colour</label>
                            </div>
                        </div>
                        <div id="inputText" contenteditable="true"></div>
                    </div>
                </div>

                <div class="groupViewMain6" style="display: none;">
                    <div class="controls">
                        <label for="brushSize">Brush Size: </label>
                        <input type="range" id="brushSize" min="1" max="50" value="5">
                        <label for="brushColor">Brush Color: </label>
                        <input type="color" id="brushColor" value="#000000">
                        <button id="useBrush">Brush</button>
                        <button id="useEraser">Eraser</button>
                        <button id="drawCircle">Circle</button>
                        <button id="drawSquare">Square</button>
                        <button id="drawRectangle">Rectangle</button>
                        <button id="drawTriangle">Triangle</button>
                        <button id="clearCanvas">Clear Canvas</button>
                        <button id="changeBackground" style="display: none;">Change Background</button>
                        <input style="display: none;" type="color" id="backgroundColor" value="#ffffff">
                        <button id="saveCanvas">Save as Image</button>
                    </div>
                    <canvas id="drawingCanvas" width="1000" height="600"></canvas>
                </div>
            </div>
        </div>

        
        <script>
    $(document).ready(function() {
        $('#searchUserButton').click(function(event) {
            event.preventDefault(); // Prevent the default form submission

            var groupid = <?php echo $groupid; ?>;
            var userSearch = $('#userSearch').val().trim();

            if(userSearch === '') {
                $('#addError').text('Please enter a username.'); // Display error if the search is empty
                return;
            }

            $.ajax({
                url: 'php/add_user_to_group.php',
                type: 'POST',
                data: {
                    groupid: groupid,
                    userSearch: userSearch
                },
                dataType: 'json', // Expect JSON response
                success: function(response) {
                    // Clear any previous error message
                    $('#addError').text('');
                    
                    if(response.success) {
                        $('#addError').text(response.message);
                        window.location.reload();
                    } else {
                        $('#addError').text(response.error);
                    }
                },
                error: function(xhr, status, error) {
                    $('#addError').text('An error occurred: ' + error);
                }
            });
        });
    });
</script>

        <script>
            // Store the original member list
            let originalMemberList = document.getElementById('members').innerHTML;

            function searchGroupMembers(query, groupId) {
                if (query.length < 3) { // Start searching after at least 3 characters
                    // Revert to the original member list if the search query is cleared
                    document.getElementById('members').innerHTML = originalMemberList;
                    return;
                }

                var xhr = new XMLHttpRequest();
                xhr.open('GET', 'php/searchGroupMembers.php?query=' + encodeURIComponent(query) + '&groupId=' + groupId, true);
                xhr.onload = function () {
                    if (xhr.status === 200) {
                        // Replace the member list with the search results
                        document.getElementById('members').innerHTML = xhr.responseText;
                    } else {
                        console.error('Error fetching search results:', xhr.statusText);
                    }
                };
                xhr.send();
            }

            // Set the original member list on page load
            window.onload = function () {
                originalMemberList = document.getElementById('members').innerHTML;
            };
        </script>

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
                                alert('File deleted successfully');
                                window.location.reload();
                                // Reload the page after a short delay to see the changes
                                // setTimeout(function(){
                                //     window.location.reload();
                                // }, 500); 
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

            function showPad(){
                // Toggle the visibility of the edit profile form
                var form = document.getElementById('createPad');
                form.style.display = form.style.display === 'none' ? 'block' : 'none';
            }

            function createPad() {
                var form = document.getElementById('padForm');
                var id = form.elements['id'].value;
                var pads = <?php echo json_encode($pads); ?>;
                var groupid = <?php echo $groupid; ?>;

                for (var i = 0; i < pads.length; i++) {
                    if (pads[i] === id) {
                        var error = document.getElementById('padError');
                        error.style.display = 'block';
                        return false;
                    }
                }

                var params = new URLSearchParams();
                params.append('gid', groupid);
                params.append('id', id);

                window.open('php/fileView.php?' + params.toString(), '_blank');

                location.reload();

                return false;
            }

            function deleteLog(logId) {
                if (confirm('Are you sure you want to delete this activity log?')) {
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', 'php/deleteActivityLog.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onload = function () {
                        if (xhr.status === 200) {
                            var response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                alert('Log deleted successfully');
                                window.location.reload();
                            } else {
                                alert('Error deleting log: ' + response.message);
                            }
                        } else {
                            alert('An error occurred while attempting to delete the log.');
                        }
                    };
                    xhr.send('logId=' + logId); // This must match the POST variable in PHP
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

        document.addEventListener('DOMContentLoaded', function () {
            // Load comments for each post
            <?php foreach ($feedPosts as $post): ?>
                loadComments(<?php echo $post['postid']; ?>);
            <?php endforeach; ?>
        });


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
        </script>
        
        <script>

                var groupViewMain = document.getElementsByClassName('groupViewMain')[0];
                var groupViewMain2 = document.getElementsByClassName('groupViewMain2')[0];
                var groupViewMain3 = document.getElementsByClassName('groupViewMain3')[0];
                var groupViewMain4 = document.getElementsByClassName('groupViewMain4')[0];
                var groupViewMain5 = document.getElementsByClassName('groupViewMain5')[0];
                var groupViewMain6 = document.getElementsByClassName('groupViewMain6')[0];
                
           function showFiles() {
                groupViewMain.style.display = "block";
                groupViewMain2.style.display = "none";
                groupViewMain3.style.display = "none";
                groupViewMain4.style.display = "none";
                groupViewMain5.style.display = "none";
                groupViewMain6.style.display = "none";
            }

            function showPosts() {
                groupViewMain.style.display = "none";
                groupViewMain2.style.display = "block";
                groupViewMain3.style.display = "none";
                groupViewMain4.style.display = "none";
                groupViewMain5.style.display = "none";
                groupViewMain6.style.display = "none";
            }

            function showLog() {
                groupViewMain.style.display = "none";
                groupViewMain2.style.display = "none";
                groupViewMain3.style.display = "block";
                groupViewMain4.style.display = "none";
                groupViewMain5.style.display = "none";
                groupViewMain6.style.display = "none";
            }

            function showMorderation() {
                groupViewMain.style.display = "none";
                groupViewMain2.style.display = "none";
                groupViewMain3.style.display = "none";
                groupViewMain4.style.display = "block";
                groupViewMain5.style.display = "none";
                groupViewMain6.style.display = "none";
            }

            function showfileEdit(){
                groupViewMain.style.display = "none";
                groupViewMain2.style.display = "none";
                groupViewMain3.style.display = "none";
                groupViewMain4.style.display = "none";
                groupViewMain5.style.display = "block";
                groupViewMain6.style.display = "none";
            }

            function showCanvas(){
                groupViewMain.style.display = "none";
                groupViewMain2.style.display = "none";
                groupViewMain3.style.display = "none";
                groupViewMain4.style.display = "none";
                groupViewMain5.style.display = "none";
                groupViewMain6.style.display = "block";
            }
        </script>
        
        <script>
            let optionsButtons = document.querySelectorAll(".optionButton");
            let advancedOptionButton = document.querySelectorAll(".optionButton-cl");
            let fontName = document.getElementById("fontName");
            let fontSizeRef = document.getElementById("fontSize");
            let writingArea = document.getElementById("inputText");
            let linkButton = document.getElementById("createLink");
            let alignButtons = document.querySelectorAll(".align");
            let spacingButtons = document.querySelectorAll(".spacing");
            let formatButtons = document.querySelectorAll(".format");
            let scriptButtons = document.querySelectorAll(".script");

            function saveTextFile() {
                var textToWrite = document.getElementById('inputText').innerHTML;
                var textFileAsBlob = new Blob([textToWrite], { type: 'text/html' });

                // Create an invisible a element
                var downloadLink = document.createElement("a");
                downloadLink.style.display = "none";
                document.body.appendChild(downloadLink);

                // Set the file name and start the download
                downloadLink.download = "MyDocument.txt";
                downloadLink.href = window.URL.createObjectURL(textFileAsBlob);
                downloadLink.click();
            }

            document.getElementById('indent').addEventListener('click', function() {
                // `indent` increases the indent level of a block element
                document.execCommand('indent', false, null);
            });

            document.getElementById('foreColour').addEventListener('change', function() {
                document.execCommand('foreColor', false, this.value);
            });

            document.getElementById('backColour').addEventListener('change', function() {
                // Use 'hiliteColor' for the background color (highlight color)
                document.execCommand('hiliteColor', false, this.value);
            });


            //List of fontlist
            let fontList = [
                "Arial",
                "Verdana",
                "Times New Roman",
                "Garamond",
                "Georgia",
                "Courier New",
                "cursive",
            ];

            //Initial Settings
            const initializer = () => {
            //function calls for highlighting buttons
            //No highlights for link, unlink,lists, undo,redo since they are one time operations
            highlighter(alignButtons, true);
            highlighter(spacingButtons, true);
            highlighter(formatButtons, false);
            highlighter(scriptButtons, true);

            //create options for font names
            fontList.map((value) => {
                let option = document.createElement("option");
                option.value = value;
                option.innerHTML = value;
                fontName.appendChild(option);
            });

            //fontSize allows only till 7
            for (let i = 1; i <= 7; i++) {
                let option = document.createElement("option");
                option.value = i;
                option.innerHTML = i;
                fontSizeRef.appendChild(option);
            }

            //default size
            fontSizeRef.value = 3;
            };

            //main logic
            const modifyText = (command, defaultUi, value) => {
            //execCommand executes command on selected text
            document.execCommand(command, defaultUi, value);
            };

            //For basic operations which don't need value parameter
            optionsButtons.forEach((button) => {
            button.addEventListener("click", () => {
                modifyText(button.id, false, null);
            });
            });

            //options that require value parameter (e.g colors, fonts)
            advancedOptionButton.forEach((button) => {
                button.addEventListener("change", () => {
                    modifyText(button.id, false, button.value);
                });
            });

            //link
            linkButton.addEventListener("click", () => {
            let userLink = prompt("Enter a URL");
            //if link has http then pass directly else add https
            if (/http/i.test(userLink)) {
                modifyText(linkButton.id, false, userLink);
            } else {
                userLink = "http://" + userLink;
                modifyText(linkButton.id, false, userLink);
            }
            });

            //Highlight clicked button
            const highlighter = (className, needsRemoval) => {
            className.forEach((button) => {
                button.addEventListener("click", () => {
                //needsRemoval = true means only one button should be highlight and other would be normal
                if (needsRemoval) {
                    let alreadyActive = false;

                    //If currently clicked button is already active
                    if (button.classList.contains("active")) {
                    alreadyActive = true;
                    }

                    //Remove highlight from other buttons
                    highlighterRemover(className);
                    if (!alreadyActive) {
                    //highlight clicked button
                    button.classList.add("active");
                    }
                } else {
                    //if other buttons can be highlighted
                    button.classList.toggle("active");
                }
                });
            });
            };

            const highlighterRemover = (className) => {
            className.forEach((button) => {
                button.classList.remove("active");
            });
            };

            window.onload = initializer();
        </script>

        <script>
        const canvas = document.getElementById('drawingCanvas');
        const ctx = canvas.getContext('2d');
        const brushSize = document.getElementById('brushSize');
        const brushColor = document.getElementById('brushColor');
        const useBrushButton = document.getElementById('useBrush');
        const useEraserButton = document.getElementById('useEraser');
        const drawCircleButton = document.getElementById('drawCircle');
        const drawSquareButton = document.getElementById('drawSquare');
        const drawRectangleButton = document.getElementById('drawRectangle');
        const drawTriangleButton = document.getElementById('drawTriangle');
        const backgroundColorInput = document.getElementById('backgroundColor');
        const clearCanvasButton = document.getElementById('clearCanvas');
        let drawing = false;
        let currentMode = 'brush';

        // Set initial background color to white
        canvas.style.backgroundColor = "#ffffff";
        backgroundColorInput.value = "#ffffff";

        // Draw initial white background
        ctx.fillStyle = "#ffffff";
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        canvas.addEventListener('mousedown', (e) => {
            drawing = true;
            if (currentMode === 'brush' || currentMode === 'eraser') {
            ctx.beginPath();
            ctx.moveTo(e.offsetX, e.offsetY); // Use offsetX and offsetY
            } else {
            drawShape(e);
            }
        });

        canvas.addEventListener('mousemove', (e) => {
            if ((currentMode === 'brush' || currentMode === 'eraser') && drawing) {
            ctx.lineTo(e.offsetX, e.offsetY); // Use offsetX and offsetY
            ctx.strokeStyle = currentMode === 'eraser' ? backgroundColorInput.value : brushColor.value;
            ctx.lineWidth = brushSize.value;
            ctx.stroke();
            }
        });

        canvas.addEventListener('mouseup', () => drawing = false);
        canvas.addEventListener('mouseleave', () => drawing = false);

        function drawShape(e) {
            if (currentMode !== 'brush' && currentMode !== 'eraser') {
            const size = parseInt(brushSize.value, 10);
            const x = e.offsetX;
            const y = e.offsetY;

            ctx.fillStyle = brushColor.value;
            ctx.beginPath();
            switch(currentMode) {
                case 'circle':
                ctx.arc(x, y, size, 0, Math.PI * 2);
                ctx.fill();
                break;
                case 'square':
                ctx.rect(x - size / 2, y - size / 2, size, size);
                ctx.fill();
                break;
                case 'rectangle':
                ctx.rect(x - size, y - size / 2, size * 2, size);
                ctx.fill();
                break;
                case 'triangle':
                ctx.moveTo(x, y - size);
                ctx.lineTo(x - size, y + size);
                ctx.lineTo(x + size, y + size);
                ctx.closePath();
                ctx.fill();
                break;
            }
            }
        }

        // Change Background Color
        document.getElementById("changeBackground").addEventListener("click", function() {
            let color = backgroundColorInput.value;
            canvas.style.backgroundColor = color;

            // If you want to make the background color part of the image when saving
            ctx.globalCompositeOperation = "destination-over"; // Ensure the background is drawn behind existing drawing
            ctx.fillStyle = color;
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.globalCompositeOperation = "source-over"; // Reset composition mode
        });

        // Save Canvas as Image
        document.getElementById("saveCanvas").addEventListener("click", function() {
            let image = canvas.toDataURL("image/png").replace("image/png", "image/octet-stream");
            let link = document.createElement('a');
            link.download = "my-canvas.png";
            link.href = image;
            link.click();
        });

        // Clear Canvas
        clearCanvasButton.addEventListener('click', () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = backgroundColorInput.value;
            ctx.fillRect(0, 0, canvas.width, canvas.height);
        });

        useBrushButton.addEventListener('click', () => currentMode = 'brush');
        useEraserButton.addEventListener('click', () => currentMode = 'eraser');
        drawCircleButton.addEventListener('click', () => currentMode = 'circle');
        drawSquareButton.addEventListener('click', () => currentMode = 'square');
        drawRectangleButton.addEventListener('click', () => currentMode = 'rectangle');
        drawTriangleButton.addEventListener('click', () => currentMode = 'triangle');
        </script>

        <script>
            function createImagePostButton() {
                // Toggle the visibility of the form
                var form = document.getElementById('createImagePost');
                form.style.display = form.style.display === 'none' ? 'block' : 'none';
            }

            function createTextPostButton() {
                // Toggle the visibility of the form
                var form = document.getElementById('createTextPost');
                form.style.display = form.style.display === 'none' ? 'block' : 'none';
            }

            function createALOGButton(){
                // Toggle the visibility of the form
                var form = document.getElementById('createAClog');
                form.style.display = form.style.display === 'none' ? 'block' : 'none';
            }
        </script>


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
