<?php
    session_start();
?>
<html>
    <head>
        <link rel="stylesheet" href="style.css">
        <title>CreativSync - Sign Up</title>
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
            <div class="signUpDiv">
                <?php if(isset($_SESSION['error_message'])): ?>
                    <div style="color:red; font-size: 20px;" class="error-message">
                        <?php 
                            echo $_SESSION['error_message']; 
                            unset($_SESSION['error_message']); // Clear the message after displaying
                        ?>
                    </div>
                <?php endif; ?>
                <section class="signup form">
                    <form action="php/actionSignup.php" method="post">
                        <label for="email" style="color:white;">Email:</label><br>
                        <input class="loginTextBox" type="email" id="email" name="email" placeholder="Email" size="40"><br><br>
                        <label for="uname" style="color:white;">Username:</label><br>
                        <input class="loginTextBox" type="text" id="uname" name="uname" placeholder="Username" size="40"><br><br>
                        <label for="pword" style="color:white;">Password:</label><br>
                        <input class="loginTextBox" type="password" id="pword" name="pword" placeholder="Password" size = "40"><br><br>
                        <label for="conpword" style="color:white;">Confirm Password:</label><br>
                        <input class="loginTextBox" type="password" id="conpword" name="conpword" placeholder="Confirm Password" size = "40"><br><br>
                        <input class="inversebutton" type="submit" value="Sign Up">
                      </form> 
                </section><br>
                <p style="color:white;">Already got an account?<p>
                <button class="otherbutton" onclick="location.href='login.php'">Log in</button>
            </div>
        </div>
    </body>
</html>
