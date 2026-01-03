<?php
session_start();
/* connection to database */
$host = 'localhost';
$username = 'root';
$password = '';
$port = '3306';
$certificate_file_path = '';
$database = 'PIF_2026';
$connection = mysqli_connect($host, $username, $password, $database);

if (!isset($_SESSION["userLogin"])) {
    $_SESSION["userLogin"] = false;
}
if (!isset($_SESSION["username"])) {
    $_SESSION["username"] = "Username";
}
if (!isset($_SESSION["level"])) {
    $_SESSION["level"] = 3;
}
if (!isset($_SESSION["Admin"])) {
    $_SESSION["Admin"] = false;
}
if (!isset($_SESSION["SecurityAccess"])) {
    $_SESSION["SecurityAccess"] = false;
}

/* user info from DB */
function getUserInfo($username)
{
    global $connection;
    $userInfo = $connection->prepare("SELECT * FROM Users WHERE Username =?");
    $userInfo->bind_param('s', $username);
    $userInfo->execute();
    $result = $userInfo->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row;
    }
    return null;

    /* $user = getUserInfo('john_doe');
    if ($user) {
        echo "Welcome, " . $user['Fullname'];
        echo "Your ID: " . $user['UserID'];
        echo "Public ID: " . $user['PublicUserID'];
    } */
}

/* logout */
if (isset($_POST["logoutBtn"])) {
    session_unset();
    session_destroy();
    /* header("Refresh:0"); */
}

if (isset($_POST["saveButtonClicked"], $_POST["fullName"], $_POST["userName"], $_POST["email"])) {
    // we need to save
    $sqlUpdate = $connection->prepare("UPDATE Users set Fullname = ?, Email = ? where Username = ?");
    $sqlUpdate->bind_param("sss", $_POST["fullName"], $_POST["email"], $_POST["userName"]);
    $sqlUpdate->execute();

    //we have to handel password update seperately as it is 
    if (!empty($_POST["pass"])) {
        $hashedPassword = password_hash($_POST["pass"], PASSWORD_DEFAULT);
        $sqlPass = $connection->prepare("UPDATE Users SET Password = ? WHERE Username = ?");
        $sqlPass->bind_param("ss", $hashedPassword, $_POST["userName"]);
        $sqlPass->execute();
    }

    print("Update successful");
}

/* save changes of user credentials */
//if (isset(""));

function NavigationBarE()
{
    global $t;
?>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">MyBrand</div>
            <ul class="nav-links">
                <li><a href="index.php#Home">Home</a></li>
                <li><a href="index.php#About">About</a></li>
                <li><a href="index.php#Service">Service</a></li>
                <li><a href="index.php#Dashboard">Dashboard</a></li>
                <li><a href="index.php#Contact">Contact</a></li>
            </ul>
        </div>

    </nav>
    <div class="login_container_indexPage">
        <div id="goToLogin">
            <img src="../img/User.png" alt="not found">
            <span><?php if ($_SESSION["userLogin"]) {
                        print($_SESSION["username"]);
                    ?>
                    <br>
                <?php
                    } else {
                        print("username");
                    } ?></span>

        </div>
    </div>
<?php
}
?>