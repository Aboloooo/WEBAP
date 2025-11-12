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
    <div class="signInOut_form_container1">
        <div class="signInOut_form_container2">
            <!-- <div class="left_side_container"></div> -->
            <div class="right_side_container">
                <form method="post">
                    <h2>Sign up</h2>
                    <label for="">Username</label>
                    <input type="text" placeholder="Username">

                    <label for="">Password</label>
                    <input type="password" placeholder="...">

                    <div class="signUpOptions">
                        <span><a href="#">create new</a></span>
                        <span><a href="#">forget password</a></span>
                    </div>

                    <button>submit</button>

                    <div class="seperator"></div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>