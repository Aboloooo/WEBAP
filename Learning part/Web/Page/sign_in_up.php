<?php
include_once("../MyLibrary.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- CDN jQuery pull -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- my vanila js script -->
    <script src="../MyScript.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>index</title>
    <link rel="stylesheet" href="../MyStyle.css">
</head>

<body>
    <?php
    NavigationBarE();
    ?>
    <div class="main_container">
        <div class="signInOut_form_container1">
            <div class="signInOut_form_container2">
                <div class="left_side_container">
                    <form class="create_user_form" method="post">
                        <h2>Create New User</h2>

                        <label for="fullname">Full Name</label>
                        <input type="text" id="fullname" name="fullname" placeholder="John Doe" required>

                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="example@email.com" required>

                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" placeholder="johndoe123" required>

                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="********" required>

                        <label for="role">User Role</label>
                        <select id="role" name="role" required>
                            <option value="">Select Role</option>
                            <option value="admin">Admin</option>
                            <option value="editor">Editor</option>
                            <option value="user">User</option>
                        </select>

                        <button type="submit">Create User</button>

                        <div class="account_link">
                            <span>Already have an account?</span>
                            <a href="#">Sign in</a>
                        </div>
                    </form>
                </div>

                <div class="right_side_container">
                    <form method="post">
                        <h2>Sign up</h2>
                        <label for="">Username</label>
                        <input type="text" placeholder="Username">

                        <label for="">Password</label>
                        <input type="password" placeholder="...">

                        <div class="signUpOptions">
                            <span><a href="#">Create New Account</a></span>
                            <span><a href="#">Forget Password</a></span>
                        </div>

                        <button>submit</button>

                        <div class="seperator"></div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</body>

</html>