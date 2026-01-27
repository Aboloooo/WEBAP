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
if (isset($_POST['displayStaion']) && $_POST['displayStaion']) {
    $stationInfo = $connection->prepare("SELECT s.Station_id, s.Name FROM Station s JOIN Users u ON s.Owner_id = u.UserID where username = ?");
    $stationInfo->bind_param('s', $_SESSION["username"]);
    $stationInfo->execute();
    $result = $stationInfo->get_result();
    $stationDetails = [];
    while ($row = $result->fetch_assoc()) {
        $sId = $row['Station_id'];
        $sName = $row['Name'];
        $stationDetails[] = [
            "stationId" => $sId,
            "stationName" => $sName,
        ];
    }
    echo json_encode($stationDetails);
}
if (isset($_POST['displayCollections']) && $_POST['displayCollections']) {
    $MyInfo = getUserInfo($_SESSION["username"]);
    $MyID = $MyInfo['UserID'];
    $CollectionInfo = $connection->prepare("select * from Collection where Creator_ID = ?");
    $CollectionInfo->bind_param('i', $MyID);
    $CollectionInfo->execute();
    $result = $CollectionInfo->get_result();
    $CollectionDetails = [];
    while ($row = $result->fetch_assoc()) {
        $Collection_id = $row['Collection_id'];
        $Collection_name = $row['Name'];
        $CollectionDetails[] = [
            "Collection_id" => $Collection_id,
            "Collection_name" => $Collection_name,
        ];
    }
    echo json_encode($CollectionDetails);
}


if (isset($_POST['measurementValues'], $_POST['CollecionN'], $_POST['CollecionD'])) {
    $user = getUserInfo($_SESSION['username']);
    $currentUserID = $user['UserID'];
    $createCollection = $connection->prepare("INSERT INTO Collection(Name, Description ,Creator_ID) VALUES (?,?,?)");
    $createCollection->bind_param('ssi', $_POST['CollecionN'], $_POST['CollecionD'], $currentUserID);
    if ($createCollection->execute()) {
        // After creation of collection you can start inserting measurement IDs and collection ID into CollectionContains
        $collectionId = $connection->insert_id; //last column id (last collection id)
        $inputs = json_decode($_POST['measurementValues'], true);
        foreach ($inputs as $stationId) {
            $Measurement_id = $stationId[0];
            $saveIntoCollectionContains = $connection->prepare("INSERT INTO CollectionContains values(?,?)");
            $saveIntoCollectionContains->bind_param('ii', $collectionId, $Measurement_id);
            if ($saveIntoCollectionContains->execute()) {
                echo "Collection: " . $_POST['CollecionN'] . " now contains the follwing measurements(ID): " . $stationId[0] . "\n";
            }
        }
    }
}

// unassign my station
if (isset($_POST['targetID'])) {
    $newStatus = "available";
    $unassignStation = null;
    $stst = $connection->prepare("UPDATE Station set Status = ? , Owner_id = ? where Station_id = ?");
    $stst->bind_param('ssi', $newStatus, $unassignStation, $_POST['targetID']);

    if ($stst->execute()) {
        echo "Station with ID " .  $_POST['targetID'] . " unassigned successfully";
    }
};

if (isset($_POST['removeFriend']) && isset($_POST['target_user'])) {
    $MyInfo = getUserInfo($_SESSION["username"]);
    $MyID = $MyInfo['UserID'];
    $removeFriend = $connection->prepare("DELETE FROM FriendList WHERE UserA_ID = ? and UserB_ID = ? or UserB_ID = ? and UserA_ID = ?;");
    $removeFriend->bind_param('iiii', $MyID, $_POST['target_user'], $_POST['target_user'], $MyID);
    if ($removeFriend->execute()) {
        echo "Friendship with user ID: " . $_POST['target_user'] . " eneded successfully.";
    }
}

// show my Friends
if (isset($_POST['showFriends']) && $_POST['showFriends'] == "true") {

    $MyInfo = getUserInfo($_SESSION["username"]);
    $MyID = $MyInfo['UserID'];

    $friends = [];

    $friendsInfo = $connection->prepare(
        "SELECT * FROM FriendList WHERE UserA_ID = ? OR UserB_ID = ?"
    );
    $friendsInfo->bind_param('ii', $MyID, $MyID);
    $friendsInfo->execute();
    $result = $friendsInfo->get_result();

    while ($row = $result->fetch_assoc()) {

        $friend_id = ($MyID == $row['UserA_ID'])
            ? $row['UserB_ID']
            : $row['UserA_ID'];

        $ststm = $connection->prepare(
            "SELECT UserID, Username, Email FROM Users WHERE UserID = ?"
        );
        $ststm->bind_param('i', $friend_id);
        $ststm->execute();
        $userResult = $ststm->get_result();

        if ($user = $userResult->fetch_assoc()) {
            $friends[] = [
                "id" => $user['UserID'],
                "username" => $user['Username'],
                "email" => $user['Email'],
            ];
        }
    }

    echo json_encode($friends);
    exit;
}



if (isset($_POST['selectedOption'], $_POST['filterDateStart'], $_POST['filterDateEnd'])) {

    // I need to check and verify that I always get measurements of my own station
    $user = getUserInfo($_SESSION['username']);
    $Owner_id = $user['UserID'];
    $stationId = (int) $_POST['selectedOption'];
    $filterDateStart = $_POST['filterDateStart'];
    $filterDateEnd = $_POST['filterDateEnd'];
    if ($stationId == 0) {
        // Filter based on date only
        $sql = "
            select m.*
           FROM Measurement m
            INNER JOIN Station s
                ON m.Station_id = s.Station_id
            WHERE s.Owner_id = ?
                AND Timestamp between ? and ?
            ORDER BY Timestamp ASC
        ";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("iss", $Owner_id, $filterDateStart, $filterDateEnd);
    } else {
        // ✅ Filter by station and date and ownership only
        $sql = "
           SELECT m.*
            FROM Measurement m
                INNER JOIN Station s
                ON m.Station_id = s.Station_id
            WHERE 
                m.Station_id = ?
                AND s.Owner_id = ?
                AND m.Timestamp BETWEEN ? AND ?
            ORDER BY m.Timestamp ASC
        ";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("iiss", $stationId, $Owner_id, $filterDateStart, $filterDateEnd);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $measurementsArray = [];

    while ($row = $result->fetch_assoc()) {
        $measurementsArray[] = [
            "Measurement_id"   => $row['Measurement_id'],
            "Timestamp"        => $row['Timestamp'],
            "Humidity"         => $row['Humidity'],
            "Air_pressure"     => $row['Air_pressure'],
            "Light_intensity"  => $row['Light_intensity'],
            "Air_quality"      => $row['Air_quality'],
            "Station_id"       => $row['Station_id'],
            "Collection_id"    => $row['Collection_id'], // ✅ fixed key
        ];
    }

    echo json_encode($measurementsArray);
    exit;
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