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

if (isset($_GET["saveButtonClicked"], $_GET["fullName"], $_GET["userName"])) {

    // we need to save
    $sqlUpdate = $connection->prepare("UPDATE Users set Fullname = ? where Username = ?");
    $sqlUpdate->bind_param("ss", $_GET["fullName"], $_GET["userName"]);
    $sqlUpdate->execute();
    print("Update successfull");
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
                    } else {
                        print("username");
                    } ?></span>
        </div>
    </div>
<?php
}
?>