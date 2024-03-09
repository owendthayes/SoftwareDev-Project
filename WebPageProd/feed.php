<?php
session_start();
include 'php/server_connection.php';
$connection = connect_to_database();

// Fetch user's feed posts
$username = $_SESSION['username'];
$query = "SELECT * FROM posts WHERE username = '$username'";
$result = mysqli_query($connection, $query);

$feedPosts = array();

while ($row = mysqli_fetch_assoc($result)) {
    $feedPosts[] = $row;
}
?>

<html>
    <head>
        <link rel="stylesheet" href="style.css">
        <title> CreativSync - Feed</title>
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
                            <a href="search.html">Search</a> 
                            <a href="groupFiles.html">Groups</a>
                            <a href="messages2.php">Messages</a>
                            <a href="help.html">Help</a>
                            <button><a href="profile.php">Profile</a></button>
                    </div>
                </div>
            </nav>
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
                            <!-- Display comments section, input, etc. -->
                            <!-- Your existing HTML code for comments section -->

                            <div class="postContent">
                                <?php if (!empty($post['content_image'])): ?>
                                    <!-- If it's an image post -->
                                    <img class="postImage" src="<?php echo $post['content_image']; ?>">
                                <?php else: ?>
                                    <!-- If it's a text post -->
                                    <div class="postTextContainer">
                                        <p class="postText"><?php echo $post['content_text']; ?></p>
                                    </div>
                                <?php endif; ?>
                                <div class="posterInfo">
                                    <div class="postInfoUsernameAvatar">
                                        <img class="posterAvatar" src="Images/defaultAvatar.png">
                                        <p class="posterUsername"><?php echo $post['username']; ?></p>
                                    </div>
                                    <div class="postCaption">
                                        <p class="postCaptionText"><?php echo $post['post_bio']; ?></p>
                                    </div>
                                    <div class="DateLikeContainer">
                                        <p class="postDate"><?php echo date("m/d/Y", strtotime($post['created_at'])); ?></p>
                                        <button class="likeButton">Like</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>


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