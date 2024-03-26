<?php
session_start();
$recipientUsername = isset($_GET['recipient']) ? $_GET['recipient'] : '';
?>
<!DOCTYPE html>
<html>

<head>
    <title>Real-Time Chat</title>
    <link rel="stylesheet" type="text/css" href="messages2.css?version=51">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.cdnfonts.com/css/beon-2" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/emojionearea@3.4.2/dist/emojionearea.min.css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/emojionearea@3.4.2/dist/emojionearea.min.js"></script>

    <script type="text/javascript">
        var recipientUsername = <?php echo json_encode($recipientUsername); ?>;
    </script>
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
            <ul class="user-list" id="user-list">
            </ul>
        </aside>
        <div class="chat-area">
            <header class="chat-header">
                <div class="user-infop">
                    <img src="Images/defaultAvatar.png" alt="">
                    <h3>Select A User To Messaage</h3>
                </div>
            </header>
            <div class="message-area" id="message-area">
                <div class="message their-message">
                    <!--<img src="../pp2.jpg" alt="User Profile" class="profile-pic">-->
                    <div class="theirMessage">
                        <img src="Images/defaultAvatar.png" alt="User Profile" class="profile-pic">
                        <!-- <p class="message-content">Hi there! How are you?</p> -->
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
            $(".chat-footer").hide();

            if (recipientUsername !== '') {
                openConversation(recipientUsername);
            }


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

            // Function to load the default list of users the logged-in user has messaged
            function loadDefaultUserList() {
                var userList = $('#user-list');
                userList.empty();
                $.each(defaultUsers, function(index, user) {
                    var userItem = $('<li class="user"><div class="up"><h4>' + user.username + '</h4></div></li>');
                    userItem.click(function() {
                        openConversation(user.username);
                    });
                    userList.append(userItem);
                });
            }

            function loadUserList() {
                $.ajax({
                    url: 'php/get_user_list.php', // Update with the actual PHP file path
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        loadedUsers = data; // Store loaded users globally
                        var userList = $('#user-list');
                        userList.empty();
                        $.each(loadedUsers, function(index, user) {
                            // Create HTML structure for each user with default avatar and last message content
                            var userItem = $('<li class="user"><img src="Images/defaultAvatar.png" alt=""><div class="up"><h4>' + user + '</h4><p class="lastMessageContent">        </p></div></li>');
                            userItem.click(function() {
                                addUserToDefaultList(user);
                                openConversation(user);
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

            $('#filter-read').on('click', function() {
                filterUsersByReadStatus(1); // Pass 1 to filter "read" messages
            });

            // Event listener for the "Unread" button
            $('#filter-unread').on('click', function() {
                filterUsersByReadStatus(0); // Pass 0 to filter "unread" messages
            });

            $('#filter-all').on('click', function() {
                filterUsersByReadStatus(0); // Pass 0 to filter "all" messages
            });

            // Function to filter users based on their is_read status
            function filterUsersByReadStatus(isRead) {
                var userList = $('#user-list');
                userList.empty();
                $.each(loadedUsers, function(index, user) {
                    if (user.is_read === isRead) {
                        var userItem = $('<li class="user"><img src="Images/defaultAvatar.png" alt=""><div class="up"><h4>' + user.username + '</h4><p class="lastMessageContent">   </p></div></li>');
                        userItem.click(function() {
                            addUserToDefaultList(user.username);
                            openConversation(user.username);
                        });
                        userList.append(userItem);
                    }
                });
            }


            function searchUsers(searchTerm) {
                isPaused = true;
                if (searchTerm.length === 0) {
                    // If the search term is empty, load the default user list
                    loadUserList();
                    isPaused = false;
                    return; // Exit the function early
                }
                $.ajax({
                    url: 'php/search_mutual_users.php',
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
                            var userItem = $('<li class="user"><img src="Images/defaultAvatar.png" alt=""><div class="up"><h4>' + user + '</h4><p class="lastMessageContent">   </p></div></li>');
                            userItem.click(function() {
                                addUserToDefaultList(user);
                                openConversation(user);
                            });
                            userList.append(userItem);
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error("An error occurred: " + error);
                    }
                });
            }

            function addUserToDefaultList(username) {
                // Check if the user is already in the default list
                var exists = defaultUsers.some(function(user) {
                    return user.username === username;
                });

                if (!exists) {
                    // Add the new user to the default list
                    defaultUsers.push({
                        username: username
                    });
                    loadUserList();
                }
            }

            function openConversation(username) {
                isPaused = false;
                $(".chat-footer").show();
                // Update the chat header with the selected username
                $('.chat-header h3').text(username);

                // Link to profile
                $('.chat-header h3').click(function() {
                    window.location.href = 'profile.php?user_id=' + username;
                });
                // Show click on hover
                $('.chat-header h3').hover(
                    function() {
                        // Mouse enters the element
                        $(this).css('cursor', 'pointer');
                    },
                    function() {
                        // Mouse leaves the element
                        $(this).css('cursor', 'default');
                    });

                // Clear the message area
                $('#message-area').empty();

                // Find the chat ID based on the selected user
                //var chatID = loadedUsers.indexOf(username);

                // Load previous messages from the database based on the selected user
                loadMessages();
                addUserToDefaultList(username);

                // Empty the search input field
                $('.search-bar input').val('');
            }
            loadUserList(); // Load the default user list initially

            // Event listener for the search button
            $('.searchButton').on('click', function() {
                var searchTerm = $('.search-bar input').val();
                searchUsers(searchTerm);
            });

            // Event listener for the search input field to search users or revert to default list
            $('.search-bar input').on('keyup', function(e) {
                var searchTerm = $(this).val();
                if (e.keyCode === 13 || searchTerm.length === 0) {
                    searchUsers(searchTerm);
                }
            });
            var t = window.setInterval(function() {
                if (!isPaused) {
                    loadUserList()
                }
            }, 500);
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

        function sendMessage(content, isMedia) {
            var messageArea = document.getElementById('message-area');
            var emojiArea = $("#message-input").data("emojioneArea");
            //var chatID = $('#user-list .user:contains("' + recipient + '")').index(); // The chat ID based on user list position
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
                    url: 'php/actionInsertMessage.php', // Update the URL to your PHP script
                    type: 'POST',
                    data: {
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
            var recipient = $('.chat-header h3').text();

            var messageList;
            $.ajax({
                url: 'php/get_message.php', // Update the URL to your PHP script
                type: 'POST',
                data: {
                    'sentTo': recipient
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
