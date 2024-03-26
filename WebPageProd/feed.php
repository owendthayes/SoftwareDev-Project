<?php
session_start();
include 'php/server_connection.php';

$connection = connect_to_database();
$username = $_SESSION['username']; // Assume this is the logged-in user's username

// Check if the user follows anyone
$query_follow_check = "SELECT COUNT(*) AS count FROM follow WHERE follower = ?";
$stmt_follow_check = mysqli_prepare($connection, $query_follow_check);
mysqli_stmt_bind_param($stmt_follow_check, "s", $username);
mysqli_stmt_execute($stmt_follow_check);
$result_follow_check = mysqli_stmt_get_result($stmt_follow_check);
$row_follow_check = mysqli_fetch_assoc($result_follow_check);
$count_follow = $row_follow_check['count'];
mysqli_stmt_close($stmt_follow_check);

$subqueryBlockedUsers = "
    SELECT blocked FROM block WHERE blocker = ?
    UNION
    SELECT blocker FROM block WHERE blocked = ?
";

if ($count_follow > 0) {
    // Query to get the user's posts and the posts of users they follow, where groupid is NULL
    $query = "
    SELECT p.*, u.profile_image, COUNT(l.postid) as like_count 
    FROM posts p
    LEFT JOIN profile u ON p.username = u.username
    LEFT JOIN likes l ON p.postid = l.postid
    LEFT JOIN follow f ON p.username = f.following AND f.follower = ?
    WHERE 
        p.username NOT IN ($subqueryBlockedUsers)
        AND (p.username = ? OR f.follower = ?) 
        AND p.groupid IS NULL
    GROUP BY p.postid
    ORDER BY p.created_at DESC
    ";

    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "sssss", $username, $username, $username, $username, $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    // If the user does not follow anyone, retrieve only their own posts where groupid is NULL
    $query = "SELECT p.*, u.profile_image FROM posts p
    LEFT JOIN profile u ON p.username = u.username
    WHERE p.username = ? AND p.groupid IS NULL
    ORDER BY p.created_at DESC;";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "s", $username);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$feedPosts = array();
while ($row = mysqli_fetch_assoc($result)) {
    $feedPosts[] = $row;
}

foreach ($feedPosts as $key => $post) {
    $like_check_query = "SELECT COUNT(*) as like_count FROM likes WHERE username = ? AND postid = ?";
    $stmt_like_check = mysqli_prepare($connection, $like_check_query);
    mysqli_stmt_bind_param($stmt_like_check, "si", $username, $post['postid']);
    mysqli_stmt_execute($stmt_like_check);
    $result_like_check = mysqli_stmt_get_result($stmt_like_check);
    $like_data = mysqli_fetch_assoc($result_like_check);
    $feedPosts[$key]['liked_by_user'] = $like_data['like_count'] > 0;
    if (!isset($post['like_count'])) {
        $feedPosts[$key]['like_count'] = 0;
    }
    mysqli_stmt_close($stmt_like_check);
}


mysqli_stmt_close($stmt);
mysqli_close($connection);
?>




<html>
    <head>
        <link rel="stylesheet" href="style.css">
        <title> CreativSync - Feed</title>
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
        </section>

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

        <section>        
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
        </section>


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

<script>
    window.onload = function () {
        // Load comments for each post
        <?php foreach ($feedPosts as $post): ?>
            loadComments(<?php echo $post['postid']; ?>);
        <?php endforeach; ?>
    };

    setInterval(periodicallyLoadComments, 10000);

    window.onload = function () {
        periodicallyLoadComments(); // Start loading comments immediately
    };


    function loadComments(postId) {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "php/getComments.php?postId=" + postId, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                var commentsBox = document.getElementById('commentsScrollBox' + postId);
                var currentCommentsHTML = commentsBox.innerHTML;
                var newCommentsHTML = xhr.responseText;

                // Update only if there's a change to reduce DOM manipulation
                if (currentCommentsHTML !== newCommentsHTML) {
                    commentsBox.innerHTML = newCommentsHTML;
                }
            }
        };
        xhr.send();
    }




    function sendComment(postId) {
        var commentInput = document.getElementById('commentInput' + postId);
        var commentValue = commentInput.value.trim();
        if (commentValue === '') {
            return; // Handle empty comment
        }

        var xhr = new XMLHttpRequest();
        xhr.open("POST", "php/insertComment.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                commentInput.value = '';
                loadComments(postId); // Reload comments for this post
            }
        };
        xhr.send("postId=" + postId + "&comment=" + encodeURIComponent(commentValue));
    }

</script>

    </body>
</html>
