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
    }
    
    mysqli_free_result($memberResult);
    mysqli_close($connection);
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
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/emojionearea@3.4.2/dist/emojionearea.min.css" />
        <script src="https://cdn.jsdelivr.net/npm/emojionearea@3.4.2/dist/emojionearea.min.js"></script>
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
                            <a href="" id="showSearch">Search</a> 
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


        <section class="mesSec">
            <aside class="sidebar">
                <div class="search-bar">
                    <input type="text" placeholder="Search users..." />
                    <button type="submit" class="searchButton">
                        <i class='fa fa-search'></i>
                    </button>
                </div>
                <!-- <div class="filter-buttons">
                    <button id="filter-all">All</button>
                    <button id="filter-read">Read</button>
                    <button id="filter-unread">Unread</button>
                </div> -->
                <h2 class="memTitle">Members</h2>
                <ul class="user-list" id="user-list">
                    <!-- List of users will go here 
                        REMOVED FOR NOW SO THE DEFAULT PEOPLE DONT SHOW UP WHEN RELOADING-->
                    
                    <?php foreach ($groupMembers as $member): ?>
                        <li class="user">
                            <img src="Images/defaultAvatar.png" alt="Avatar">
                            <div class="up">
                                <h4><?php echo htmlspecialchars($member['username']); ?></h4>
                                <p><?php echo htmlspecialchars($member['gpermissions']) . ' - ' . htmlspecialchars($member['fpermissions']); ?></p>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </aside>
            <div class="chat-area">
                <header class="chat-header">
                    <div class="user-infop">
                        <!-- <img src="Images/defaultAvatar.png" alt=""> -->
                        <h3><?php echo htmlspecialchars($groupDetails['groupname']); ?> Group Chat</h3>
                    </div>
                </header>
                <div class="message-area" id="message-area">
                    <div class="message their-message">
                        <!--<img src="../pp2.jpg" alt="User Profile" class="profile-pic">-->
                        <div class="theirMessage">
                            <img src="Images/defaultAvatar.png" alt="User Profile" class="profile-pic">
                            <p class="message-content">Hi there! How are you?</p>
                            <p class="messageDate">24/01/24</p>
                        </div>
                    </div>
                    <!-- Messages will go here -->
                </div>
                <footer class="chat-footer">
                    <!--<label for="emoji-input" class="emoji-btn">😊</label>-->
                    <input type="text" id="message-input" class="message-input" placeholder="Type a message..." />
                    <label for="media-input" class="attachment-btn">📎</label>
                    <button id="send-btn" class="send-btn">Send</button>
    
                    <input type="file" id="media-input" style="display: none;" />
                    <input type="hidden" id="emoji-input" />
                </footer>
            </div>
        </section>
    
    
        <script>
            $(document).ready(function() {
                // Initialize emojioneArea
                $('#message-area').empty();
                $("#message-input").emojioneArea({
                    pickerPosition: "top",
                    tonesStyle: "bullet",
                    events: {
                        keyup: function(editor, event) {
                            // Check if enter is pressed without the shift key
                            if (event.keyCode === 13 && !event.shiftKey) {
                                event.preventDefault();
                                sendMessage();
                            }
                        }
                    }
                });
    
    
                // Removed so user alice and bob dont get added by default to messages
                // var defaultUsers = [{
                //         username: 'Alice'
                //     },
                //     {
                //         username: 'Bob'
                //     }
                // ];
    
                var defaultUsers = [];
    
                // Global variable to stop events
                var isPaused = false;
    
                // Global variable to store the loaded user list
                var loadedUsers = [];
    
                function loadUserList() {
                    $.ajax({
                        url: 'php/get_group_user_list.php', // Update with the actual PHP file path
                        type: 'GET',
                        data: {
                            'groupid': "<?php echo $groupid?>"
                        },
                        dataType: 'json',
                        success: function(data) {
                            loadedUsers = data; // Store loaded users globally
                            var userList = $('#user-list');
                            userList.empty();
                            $.each(loadedUsers, function(index, user) {
                                // Create HTML structure for each user with default avatar and last message content
                                var userItem = $('<li class="user"><img src="Images/defaultAvatar.png" alt=""><div class="up"><h4>' + user.username + '</h4></div></li>');
                                userItem.click(function() {
                                    redirect(user.username);
                                });
                                userList.append(userItem);
                            });
                        },
                        error: function(xhr, status, error) {
                            console.error("An error occurred: " + error);
                        }
                    });
                }
                loadUserList();
    
                function searchUsers(searchTerm) {
                    isPaused = true;
                    if (searchTerm.length === 0) {
                        // If the search term is empty, load the default user list
                        loadUserList();
                        isPaused = false;
                        return; // Exit the function early
                    }
                    $.ajax({
                        url: 'php/search_users.php',
                        type: 'GET',
                        data: {
                            'searchTerm': searchTerm
                        },
                        dataType: 'json',
                        success: function(users) {
                            var userList = $('#user-list'); // Update the user list
                            userList.empty(); // Clear the current list
                            $.each(users, function(index, user) {
                                // Create HTML structure for each user with default avatar and last message content
                                var userItem = $('<li class="user"><img src="Images/defaultAvatar.png" alt=""><div class="up"><h4>' + user.username + '</h4><p class="lastMessageContent">   </p></div></li>');
                                userItem.href = 
                                userItem.click(function() {
                                    addUserToDefaultList(user.username);
                                    openConversation(user.username);
                                });
                                userList.append(userItem);
                            });
                        },
                        error: function(xhr, status, error) {
                            console.error("An error occurred: " + error);
                        }
                    });
                }

                var t = window.setInterval(function() {
                    if (!isPaused) {
                        loadUserList()
                    }
                }, 1000);
            });
    
    
    
            document.getElementById('send-btn').addEventListener('click', sendMessage);
    
            document.getElementById('emoji-input').addEventListener('change', function() {
                var emoji = this.value;
                if (emoji) {
                    document.getElementById('message-input').value += emoji; // Append emoji to message input
                }
            });
    
            document.getElementById('media-input').addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        sendMessage(e.target.result, true); // Send the media content as the message
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });
            
            function redirect(username){
                window.location.href = 'profile.php?user_id=' + username;
            }

            function sendMessage(content, isMedia) {
                var messageArea = document.getElementById('message-area');
                var emojiArea = $("#message-input").data("emojioneArea");
                var chatID = $('#user-list .user:contains("' + recipient + '")').index(); // The chat ID based on user list position
                var messageContent = isMedia ? content : emojiArea.getText();
                var recipient = $('.chat-header h3').text(); // The recipient's username
    
                if (messageContent.trim()) {
                    var messageDiv = document.createElement('div');
                    messageDiv.classList.add('message', 'my-message');
    
                    var img = document.createElement('img');
                    img.src = "Images/defaultAvatar.png"; // The src of your user profile picture
                    img.alt = "My Profile";
                    img.classList.add('profile-pic');
    
                    var messageP = document.createElement('p');
                    messageP.classList.add('message-content');
    
                    if (isMedia) {
                        var mediaElement = document.createElement('img');
                        mediaElement.src = messageContent;
                        mediaElement.classList.add('media-attachment');
                        messageP.appendChild(mediaElement);
                    } else {
                        messageP.textContent = messageContent;
                    }
    
                    messageDiv.appendChild(img);
                    messageDiv.appendChild(messageP);
                    messageArea.appendChild(messageDiv);
    
                    $.ajax({
                        url: 'php/actionInsertGroupMessage.php', // Update the URL to your PHP script
                        type: 'POST',
                        data: {
                            'groupid': "<?php echo $groupid?>",
                            'sentTo': recipient,
                            'message': messageContent
                        },
                        success: function(response) {
                            console.log(response); // Handle the response
                        },
                        error: function(xhr, status, error) {
                            console.error("An error occurred: " + error);
                        }
                    });
    
                    emojiArea.setText(''); // Clear the emojioneArea
    
                    messageArea.scrollTop = messageArea.scrollHeight;
                }
            }
            document.getElementById('send-btn').addEventListener('click', function() {
                sendMessage(null, false); // When clicking send, pass null as content and false for isMedia
            });
    
            function loadMessages() {
                var messageArea = document.getElementById('message-area');
    
                var messageList;
                $.ajax({
                    url: 'php/get_group_message.php', // Update the URL to your PHP script
                    type: 'POST',
                    data: {
                        'groupid': "<?php echo $groupid?>"
                    },
                    success: function(response) {
                        //console.log(response); // Handle the response
                        messageArea.innerHTML = response;
                    },
                    error: function(xhr, status, error) {
                        console.error("An error occurred: " + error);
                    }
                });
    
    
            }
    
            setInterval(loadMessages,500)
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
