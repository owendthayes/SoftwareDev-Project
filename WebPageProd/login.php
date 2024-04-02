<?php
    session_start();
?>
<html>
    <head>
        <link rel="stylesheet" href="style.css">
        <title> CreativSync - Login</title>
        <link rel="icon" href="Images/logo.png">
        <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <section class="navigation">
            <nav>
                <div class="mainnav">
                <div class="imgnav">
                        <img src="Images/logo.png">
                        <div class="compname"><h1 style="color:white;">CreativSync</h1></div>
                    </div>
                </div>
            </nav>
        </section>
        <div text-align="center" margin="auto">
            <div class="loginDiv">
                <?php if(isset($_SESSION['error_message'])): ?>
                    <p style="color:red;" class="error-message">
                        <?php 
                            echo $_SESSION['error_message']; 
                            unset($_SESSION['error_message']); // Clear the message after displaying it
                        ?>
                    </p>
                <?php endif; ?>

                <section class="login form">
                    <form action="php/actionLogin.php" method="post">
                        <label for="uname" style="color:white;">Username:</label><br>
                        <input class="loginTextBox" type="text" id="uname" name="uname" placeholder="Username" size="40"><br><br>
                        <label for="pword" style="color:white;">Password:</label><br>
                        <input class="loginTextBox" type="password" id="pword" name="pword" placeholder="Password" size = "40"><br><br>
                        <input class="inversebutton" type="submit" value="Log In">
                      </form> 
                </section><br>
                <p style="color:white;">Not got an account?<p>
                <button class="otherbutton" onclick="location.href='signup.php'">Sign Up</button>
            </div>
        </div>    
    </body>
</html>
