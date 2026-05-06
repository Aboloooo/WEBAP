<?php
ob_start();

use LDAP\Result;

include_once("../MyLibrary.php");

/* Handle sign-in before any HTML output so header() works */
if (isset($_POST['signin_username'], $_POST['signin_password'])) {
    $loginCheck = $connection->prepare('SELECT * FROM Users WHERE Username = ?');
    $loginCheck->bind_param('s', $_POST["signin_username"]);
    $loginCheck->execute();
    $result = $loginCheck->get_result();
    if ($row = $result->fetch_assoc()) {
        //if (password_verify($_POST['signin_password'], $row['Password']) || $_POST['signin_password'] === $row['Password']) {
        if (password_verify($_POST['signin_password'], $row['Password'])) {
            $_SESSION["username"]  = $row['Username'];
            $_SESSION["userLogin"] = true;
            // clear any buffered output from included files before sending headers
            if (ob_get_level()) ob_clean();
            header("location: index.php");
            exit;
        } else {
            $loginError = "Incorrect password.";
        }
    } else {
        $loginError = "Username not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- CDN jQuery pull -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="../js/jquery.js"></script>
    <!-- my vanila js script -->
    <script src="../js/MyScript.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EnvMonitor - Sign In/Up Form</title>
    <link rel="stylesheet" href="../MyStyle.css">
</head>

<body>
    <?php
    NavigationBarE();
    ?>
    <div class="main_container">
        <?php
        if (!$_SESSION["userLogin"]) {
        ?>
            <div class="signInOut_form_container1">

                <!-- Overlay should be outside the form container2 -->
                <div class="overlayout" id="formOverlay">
                    <div class="overlay_content">
                        <img src="../img/login_img2.avif" class="myImg" alt="Overlay Image">
                    </div>
                </div>
                <?php
                if (isset($_POST['submit'])) {
                    $FullName = $_POST['fullname'];
                    $EmailAddress = $_POST['email'];
                    $Username = $_POST['username'];
                    $Password = $_POST['password'];
                    $hashedPass = password_hash($Password, PASSWORD_DEFAULT);
                    $DefaultAccessLevel = 3;
                    $confirmPassword = $_POST['confirmPassword'];
                    /* password doesnt match possible error */
                    if ($Password == $confirmPassword) {
                        /* check for any doblicaiton based on Username */
                        $checkDublication = $connection->prepare("SELECT * from Users where Username = ?");
                        $checkDublication->bind_param('s', $Username);
                        $checkDublication->execute();
                        $resultDublication = $checkDublication->get_result();
                        if ($resultDublication->num_rows > 0) {
                            echo "<script>alert('This username already taken. Please choose a different username.');</script>";
                        } else {
                            $insertValues = $connection->prepare("INSERT INTO Users(Fullname,Email,Username,Password,AccessLevelID) values (?,?,?,?,?)");
                            $insertValues->bind_param("ssssi", $FullName, $EmailAddress, $Username, $Password, $DefaultAccessLevel);
                            if ($insertValues->execute()) {
                                echo "<script>alert('Form submitted successfully');</script>";
                            };
                        }
                    } else {
                        echo "<script>alert('Passwords do not match');</script>";
                    }
                }
                ?>
                <div class="signInOut_form_container2">
                    <div class="left_side_container">
                        <form class="create_user_form" method="post" action="#">
                            <h2>Create New User</h2>

                            <label for="fullname">Full Name</label>
                            <input type="text" id="fullname" name="fullname" placeholder="John Doe" required>

                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" placeholder="example@email.com" required>

                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" placeholder="johndoe123" required>

                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" placeholder="********" required>

                            <label for="password">Confirm your Password</label>
                            <input type="password" id="confirmPassword" name="confirmPassword" placeholder="********" required>

                            <!--   <label for="role">User Role</label>
                        <select id="role" name="role" required>
                            <option value="">Select Role</option>
                            <option value="admin">Admin</option>
                            <option value="editor">Editor</option>
                            <option value="user">User</option>
                        </select> -->

                            <button type="submit" name="submit">Create User</button>

                            <div class="account_link">
                                <span>Already have an account?</span>
                                <a href="#" class="layoutTrigger">Sign in</a>
                            </div>
                        </form>
                    </div>

                    <?php if (isset($loginError)) echo "<script>alert('" . addslashes($loginError) . "')</script>"; ?>
                    <div class="right_side_container">
                        <form method="post" action="#">
                            <h2>Sign In</h2>
                            <label for="signin_username">Username</label>
                            <input type="text" id="signin_username" name="signin_username" placeholder="Username" required>

                            <label for="signin_password">Password</label>
                            <input type="password" id="signin_password" name="signin_password" placeholder="Enter your password" required>

                            <div class="signUpOptions">
                                <span><a href="#" class="layoutTrigger">Create New Account</a></span>
                                <span><a href="#">Forgot Password?</a></span>
                            </div>
                            <button type="submit">Sign In</button>
                            <div class="seperator"></div>
                        </form>
                    </div>
                </div>
            </div>
        <?php
        } else {
            $loginCheck = $connection->prepare('select * from Users where Username =?');
            $loginCheck->bind_param('s', $_SESSION["username"]);
            $loginCheck->execute();
            $result = $loginCheck->get_result();
            if ($row = $result->fetch_assoc()) {
                $fullName = $row['Fullname'];
                $username = $row['Username'];
                $email = $row['Email'];
                $password = $row['Password'];
                $level = $row['AccessLevelID'];
            }

        ?>
            <div class="main_container">
                <div class="user_card">
                    <div>
                        <h2 id="UserInfoH2">User Information</h2>
                    </div>
                    <div class="slidesContainer">
                        <div id="firstSide">
                            <div class="info_grid" id="userInfoGrid">
                                <!-- Full Name -->
                                <div class="info_row" data-field="full-name">
                                    <strong>Full Name</strong>
                                    <span class="value" id="fullNameValue"><?= $fullName ?></span>
                                    <div class="editable-field">
                                        <input type="text" id="fullNameInput" value="<?= $fullName ?>">
                                    </div>
                                    <!-- <span class="edit-icon" onclick="editField('full-name')">✏️</span> -->
                                </div>

                                <!-- Username -->
                                <div class="info_row" data-field="username">
                                    <strong>Username</strong>
                                    <span class="value" id="usernameValue"><?= $username ?></span>
                                    <div class="editable-field">
                                        <input type="text" id="usernameInput" disabled value="<?= $username ?>">
                                    </div>
                                    <!--  <span class="edit-icon" onclick="editField('username')">✏️</span> -->
                                </div>

                                <!-- Email -->
                                <div class="info_row" data-field="email">
                                    <strong>Email Address</strong>
                                    <span class="value" id="emailValue"><?= $email ?></span>
                                    <div class="editable-field">
                                        <input type="email" id="emailInput" value="<?= $email ?>">
                                    </div>
                                    <!-- <span class="edit-icon" onclick="editField('email')">✏️</span> -->
                                </div>

                                <!-- Password -->
                                <div class="info_row" data-field="password">
                                    <strong>Password</strong>
                                    <span class="value" id="passwordValue">••••••••••</span>
                                    <div class="editable-field">
                                        <input type="password" id="passwordInput" placeholder="Enter new password">
                                    </div>
                                    <!-- <span class="edit-icon" onclick="editField('password')">✏️</span> -->
                                </div>
                                <div class="info_row" style="display: none;" id="passConfir" data-field="passwordConfirmation">
                                    <strong>Re-type Password</strong>
                                    <span class="value" id="passConfirValue">••••••••••</span>
                                    <div class="editable-field">
                                        <input type="password" id="passwordConfirmationInput" placeholder="Confirm your new password">
                                    </div>
                                    <!-- <span class="edit-icon" onclick="editField('password')">✏️</span> -->
                                </div>


                                <!-- Assigned Stations -->
                                <div class="info_row" data-field="stations">
                                    <strong>Assigned Stations</strong>
                                    <span class="value" id="stationsValue">
                                        <!-- <span class="station_badge">Station A</span> -->
                                        <?php
                                        $user = getUserInfo($_SESSION['username']);
                                        $currentUserID = $user['UserID'];
                                        $myStations = $connection->prepare("SELECT Name FROM Station WHERE Owner_id=?");
                                        $myStations->bind_param("i", $currentUserID);
                                        $myStations->execute();
                                        $result = $myStations->get_result();
                                        while ($stationRow = $result->fetch_assoc()) {
                                            $stationName = $stationRow['Name'];
                                        ?>
                                            <span class="station_badge"><?= $stationName ?></span>
                                        <?php
                                        }
                                        ?>

                                    </span>

                                    <!-- <span class="edit-icon" onclick="editField('stations')">✏️</span> -->
                                </div>
                            </div>
                        </div>
                        <div id="secondSide">
                            <img src="../img/User.png" alt="not found">
                            <span><?= $username ?></span>
                            <?php if ($_SESSION["Admin"]): ?>
                                <span style="color: #4CAF50; font-weight: bold;">(Admin User)</span>
                            <?php endif; ?>
                            <span>Share your username to establish a friendship</span>
                        </div>

                    </div>
                    <!-- Action Buttons -->
                    <div class="card-actions" id="actionButtons">
                        <button class="btn btn-edit" id="editBtn" onclick="enableEditing()" style="display: flex;">
                            ✏️ Edit Information
                        </button>
                        <button class="btn btn-cancel" id="profileLogoutBtn" onclick="Logout()" style="display: flex;">
                            🚪 Logout
                        </button>
                        <!-- save btn will be handeled with php -->
                        <button class="btn btn-save" id="saveBtn" style="display: none;">
                            💾 Save Changes
                        </button>
                        <button class="btn btn-cancel" id="cancelBtn" onclick="cancelEdit()" style="display: none;">
                            ❌ Cancel
                        </button>
                    </div>
                </div>
            </div>
        <?php
        }
        ?>
    </div>


</body>

</html>