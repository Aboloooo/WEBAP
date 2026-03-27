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
function userHasCollections(int $userId): bool
{
    global $connection;
    // Prepare the statement
    $stmt = mysqli_prepare($connection, "SELECT 1 FROM Collection WHERE Creator_ID = ? LIMIT 1");
    if (!$stmt) {
        return false; // failed to prepare
    }
    // Bind the parameter
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    // Execute
    mysqli_stmt_execute($stmt);
    // Store result to get number of rows
    mysqli_stmt_store_result($stmt);
    $hasCollections = mysqli_stmt_num_rows($stmt) > 0;
    return $hasCollections;
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

/* Remove or delete my collection */
if (isset($_POST['targetCollection'])) {
    $removeCollectionContains = $connection->prepare("DELETE FROM CollectionContains WHERE Collection_id = ?");
    $removeCollectionContains->bind_param('i', $_POST['targetCollection']);
    if ($removeCollectionContains->execute()) {
        $removeCollection = $connection->prepare("DELETE FROM Collection WHERE Collection_id = ?");
        $removeCollection->bind_param('i', $_POST['targetCollection']);
        if ($removeCollection->execute()) {
            echo "Collection removed successfully!";
        }
    }
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

if (isset($_POST['DisplayCollection']) && $_POST['DisplayCollection']) {

    $MyInfo = getUserInfo($_SESSION["username"]);
    $MyID = $MyInfo['UserID'];

    $statement = "
        SELECT 
            c.Collection_id,
            c.Name,
            c.Description,
            m.*
        FROM Collection c
        JOIN CollectionContains cc ON c.Collection_id = cc.Collection_id
        JOIN Measurement m ON cc.Measurement_id = m.Measurement_id
        WHERE c.Creator_ID = ?
        ORDER BY c.Collection_id
    ";

    $stmt = $connection->prepare($statement);
    $stmt->bind_param('i', $MyID);
    $stmt->execute();
    $result = $stmt->get_result();

    $collections = [];

    while ($row = $result->fetch_assoc()) {
        $cid = $row['Collection_id'];

        if (!isset($collections[$cid])) {
            $collections[$cid] = [
                "Collection_id" => $cid,
                "Name" => $row['Name'],
                "Description" => $row['Description'],
                "Measurements" => []
            ];
        }

        $collections[$cid]['Measurements'][] = [
            "Measurement_id" => $row['Measurement_id'],
            "Timestamp" => $row['Timestamp'],
            "Humidity" => $row['Humidity'],
            "Air_pressure" => $row['Air_pressure'],
            "Light_intensity" => $row['Light_intensity'],
            "Air_quality" => $row['Air_quality']
        ];
    }

    if (empty($collections)) {
        echo json_encode(["message" => "No Collection Found!"]);
    } else {
        echo json_encode(array_values($collections));
    }
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
    $CollectionDetails = [];
    if (!isset($_SESSION["username"])) {
        echo json_encode($CollectionDetails);
        exit;
    }
    $MyInfo = getUserInfo($_SESSION["username"]);
    if (!$MyInfo || !isset($MyInfo['UserID'])) {
        echo json_encode($CollectionDetails);
        exit;
    }
    $MyID = $MyInfo['UserID'];
    $CollectionInfo = $connection->prepare(
        "SELECT Collection_id, Name FROM Collection WHERE Creator_ID = ?"
    );
    $CollectionInfo->bind_param('i', $MyID);
    $CollectionInfo->execute();
    $result = $CollectionInfo->get_result();
    while ($row = $result->fetch_assoc()) {
        $CollectionDetails[] = [
            "Collection_id"   => $row['Collection_id'],
            "Collection_name" => $row['Name'],
        ];
    }
    echo json_encode($CollectionDetails);
    exit;
}

/* share Collection with Friends */
if (isset($_POST['shareWith'], $_POST['targetCollectionToShare'])) {
    $user = getUserInfo($_SESSION['username']);
    $sharedBy = $user['UserID'];
    $targetToShare = (int) $_POST['targetCollectionToShare'];
    $FriendsToShareWith = $_POST['shareWith'];

    if (!is_array($FriendsToShareWith)) {
        $FriendsToShareWith = [$FriendsToShareWith];
    }

    $stmt = $connection->prepare("
        INSERT INTO CollectionShare (Collection_id, Shared_by, Shared_with)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE Collection_id = Collection_id
    ");

    $success = 0;
    foreach ($FriendsToShareWith as $friendId) {
        $stmt->bind_param('iii', $targetToShare, $sharedBy, $friendId);
        if ($stmt->execute()) {
            $success++;
        }
    }

    echo "Shared with " . $success . " friend(s)";
    exit;
}

/* Stop sharing this collection */
if (isset($_POST['CancelSharedCollection'])) {
    $collectionId = (int)$_POST['CancelSharedCollection'];
    $user = getUserInfo($_SESSION['username']);
    $userId = $user['UserID'];

    $stmt = $connection->prepare("
        DELETE FROM CollectionShare 
        WHERE Collection_id = ? AND Shared_by = ?
    ");
    $stmt->bind_param('ii', $collectionId, $userId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Share canceled']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to cancel share']);
    }
    exit;
}
/* Return all the Collection related to ME */
/* Return all the Collection related to ME */
if (isset($_POST['FetchSharedCollection']) && $_POST['FetchSharedCollection']) {
    $user = getUserInfo($_SESSION['username']);
    $currentUserID = $user['UserID'];

    $response = [
        'success' => true,
        'sharedByMeCollections' => [],
        'sharedWithMeCollections' => []
    ];

    // Get collections shared BY me
    $stmt1 = $connection->prepare("
        SELECT cs.Collection_id, c.Name, c.Description 
        FROM CollectionShare cs
        JOIN Collection c ON cs.Collection_id = c.Collection_id
        WHERE cs.Shared_by = ?
    ");
    $stmt1->bind_param('i', $currentUserID);
    $stmt1->execute();
    $result1 = $stmt1->get_result();

    while ($row = $result1->fetch_assoc()) {
        $collectionData = getCollectionsWithMeasurementsForCollection($row['Collection_id'], $connection);
        $response['sharedByMeCollections'][$row['Collection_id']] = $collectionData;
    }

    // Get collections shared WITH me
    $stmt2 = $connection->prepare("
        SELECT cs.Collection_id, c.Name, c.Description 
        FROM CollectionShare cs
        JOIN Collection c ON cs.Collection_id = c.Collection_id
        WHERE cs.Shared_with = ?
    ");
    $stmt2->bind_param('i', $currentUserID);
    $stmt2->execute();
    $result2 = $stmt2->get_result();

    while ($row = $result2->fetch_assoc()) {
        $collectionData = getCollectionsWithMeasurementsForCollection($row['Collection_id'], $connection);
        $response['sharedWithMeCollections'][$row['Collection_id']] = $collectionData;
    }

    echo json_encode($response);
    exit;
}

// === ADD THIS MISSING FUNCTION ===
function getCollectionsWithMeasurementsForCollection($collectionID, $connection)
{
    // First get collection info
    $stmt1 = $connection->prepare("
        SELECT c.Collection_id, c.Name, c.Description
        FROM Collection c
        WHERE c.Collection_id = ?
    ");
    $stmt1->bind_param('i', $collectionID);
    $stmt1->execute();
    $result1 = $stmt1->get_result();

    if ($result1->num_rows === 0) {
        return null;
    }

    $collectionRow = $result1->fetch_assoc();

    $collection = [
        "Collection_id" => $collectionRow['Collection_id'],
        "Name" => $collectionRow['Name'],
        "Description" => $collectionRow['Description'],
        "Measurements" => []
    ];

    // Get measurements for this collection
    $stmt2 = $connection->prepare("
        SELECT m.*
        FROM CollectionContains cc
        JOIN Measurement m ON cc.Measurement_id = m.Measurement_id
        WHERE cc.Collection_id = ?
    ");
    $stmt2->bind_param('i', $collectionID);
    $stmt2->execute();
    $result2 = $stmt2->get_result();

    while ($row = $result2->fetch_assoc()) {
        $collection['Measurements'][] = [
            "Measurement_id" => $row['Measurement_id'],
            "Timestamp" => $row['Timestamp'],
            "Humidity" => $row['Humidity'],
            "Air_pressure" => $row['Air_pressure'],
            "Light_intensity" => $row['Light_intensity'],
            "Air_quality" => $row['Air_quality']
        ];
    }

    return $collection;
}
// === END OF ADDED FUNCTION ===


if (isset($_POST['measurementValues'], $_POST['CollecionN'], $_POST['CollecionD'])) {

    $user = getUserInfo($_SESSION['username']);
    $currentUserID = $user['UserID'];

    $createCollection = $connection->prepare(
        "INSERT INTO Collection (Name, Description, Creator_ID) VALUES (?, ?, ?)"
    );
    $createCollection->bind_param(
        'ssi',
        $_POST['CollecionN'],
        $_POST['CollecionD'],
        $currentUserID
    );

    if ($createCollection->execute()) {

        $collectionId = $connection->insert_id;
        $inputs = json_decode($_POST['measurementValues'], true);

        $insertCC = $connection->prepare(
            "INSERT INTO CollectionContains (Collection_id, Measurement_id) VALUES (?, ?)"
        );

        foreach ($inputs as $stationId) {
            $Measurement_id = $stationId[0];

            // insert relation
            $insertCC->bind_param('ii', $collectionId, $Measurement_id);
            $insertCC->execute();

            echo "Collection {$_POST['CollecionN']} now contains measurement ID: {$Measurement_id}\n";
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
    $removeFriend = $connection->prepare("DELETE FROM FriendList WHERE (UserA_ID = ? AND UserB_ID = ?) OR (UserB_ID = ? AND UserA_ID = ?);");
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
        "SELECT * FROM FriendList WHERE (UserA_ID = ? OR UserB_ID = ?) AND status = 'accepted'"
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
            ORDER BY Timestamp DESC
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
        ];
    }

    echo json_encode($measurementsArray);
    exit;
}

/* Get only NEW measurements after a given timestamp (for real-time polling) */
if (isset($_POST['getNewMeasurements'], $_POST['stationId'], $_POST['lastTimestamp'])) {
    $user = getUserInfo($_SESSION['username']);
    $Owner_id = $user['UserID'];
    $stationId = (int) $_POST['stationId'];
    $lastTimestamp = $_POST['lastTimestamp'];

    if ($stationId == 0) {
        // Get new measurements from all user's stations
        $sql = "
            SELECT m.*
            FROM Measurement m
            INNER JOIN Station s
                ON m.Station_id = s.Station_id
            WHERE s.Owner_id = ?
                AND m.Timestamp > ?
            ORDER BY m.Timestamp ASC
        ";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("is", $Owner_id, $lastTimestamp);
    } else {
        // Get new measurements from specific station
        $sql = "
            SELECT m.*
            FROM Measurement m
            INNER JOIN Station s
                ON m.Station_id = s.Station_id
            WHERE m.Station_id = ?
                AND s.Owner_id = ?
                AND m.Timestamp > ?
            ORDER BY m.Timestamp ASC
        ";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("iis", $stationId, $Owner_id, $lastTimestamp);
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
        ];
    }

    echo json_encode([
        'success' => true,
        'newMeasurements' => $measurementsArray,
        'lastTimestamp' => !empty($measurementsArray) ? $measurementsArray[count($measurementsArray) - 1]['Timestamp'] : $_POST['lastTimestamp']
    ]);
    exit;
}

    /* save changes of user credentials */;

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
                <?php
                $MyInfo = getUserInfo($_SESSION['username']);
                if ($MyInfo && userHasCollections($MyInfo['UserID'])) {
                    echo '<li><a href="./Collection.php">My Collection</a></li>';
                }
                // Add Admin Panel link if user is admin
                if ($_SESSION["Admin"]) {
                    echo '<li><a href="./admin.php">Admin Panel</a></li>';
                }
                ?>
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
                    <?php if ($_SESSION["Admin"]) echo "<small>(Admin)</small>"; ?>
                <?php
                    } else {
                        print("username");
                    } ?></span>

        </div>
    </div>
<?php
}


/* ==================== ADMIN FUNCTIONS ==================== */

/* Check if user is admin */
function isAdmin($username)
{
    global $connection;
    $stmt = $connection->prepare("SELECT AccessLevelID FROM Users WHERE Username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        return $row['AccessLevelID'] == 1; // Assuming 1 is Admin role
    }
    return false;
}

/* Update session Admin status - Add this after session_start section */
if ($_SESSION["userLogin"]) {
    $_SESSION["Admin"] = isAdmin($_SESSION["username"]);
}

/* ==================== ADMIN POST HANDLERS ==================== */

/* Create new user (Admin only) */
if (isset($_POST['create_user']) && isset($_POST['new_username'])) {
    if (!$_SESSION["Admin"]) {
        echo "Unauthorized";
        exit;
    }

    $username = $_POST['new_username'];
    $password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    $fullname = $_POST['new_fullname'];
    $email = $_POST['new_email'];
    $role = $_POST['new_role'];

    $stmt = $connection->prepare("INSERT INTO Users (Username, Password, Fullname, Email, AccessLevelID) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $username, $password, $fullname, $email, $role);

    if ($stmt->execute()) {
        echo "User created successfully";
    } else {
        echo "Error creating user";
    }
    exit;
}

/* Get all users for admin (Admin only) */
if (isset($_POST['get_all_users']) && $_POST['get_all_users']) {
    if (!$_SESSION["Admin"]) {
        echo "Unauthorized";
        exit;
    }

    $result = $connection->query("SELECT UserID, Username, Fullname, Email, AccessLevelID FROM Users ORDER BY UserID");

    $html = '<table style="width:100%; border-collapse: collapse;">
                <tr style="background: #f4f4f4;">
                    <th style="border:1px solid #ddd; padding:8px;">ID</th>
                    <th style="border:1px solid #ddd; padding:8px;">Username</th>
                    <th style="border:1px solid #ddd; padding:8px;">Full Name</th>
                    <th style="border:1px solid #ddd; padding:8px;">Email</th>
                    <th style="border:1px solid #ddd; padding:8px;">Role</th>
                    <th style="border:1px solid #ddd; padding:8px;">Actions</th>
                </tr>';

    if ($result->num_rows == 0) {
        $html .= '<tr><td colspan="6" style="text-align:center; padding:20px;">No users found</td></tr>';
    } else {
        while ($row = $result->fetch_assoc()) {
            $role = $row['AccessLevelID'] == 1 ? 'Admin' : ($row['AccessLevelID'] == 2 ? 'Dev' : 'User');
            $html .= '<tr>
                        <td style="border:1px solid #ddd; padding:8px;">' . $row['UserID'] . '</td>
                        <td style="border:1px solid #ddd; padding:8px;">' . $row['Username'] . '</td>
                        <td style="border:1px solid #ddd; padding:8px;">' . $row['Fullname'] . '</td>
                        <td style="border:1px solid #ddd; padding:8px;">' . $row['Email'] . '</td>
                        <td style="border:1px solid #ddd; padding:8px;">' . $role . '</td>
                        <td style="border:1px solid #ddd; padding:8px;">
<button class="delete-btn" value="user_' . $row['UserID'] . '" style="background:#f44336; color:white; border:none; padding:8px 12px; cursor:pointer; border-radius:4px; width:100%;">Delete</button>                      </tr>';
        }
    }

    $html .= '</table>';
    echo $html;
    exit;
}

/* Create new station (Admin only) */
if (isset($_POST['create_station']) && isset($_POST['station_serial'])) {
    if (!$_SESSION["Admin"]) {
        echo "Unauthorized";
        exit;
    }

    $name = $_POST['station_name'];
    $serial = $_POST['station_serial'];
    $description = $_POST['station_description'];

    $stmt = $connection->prepare("INSERT INTO Station (Name, Serial_number, Description) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $serial, $description);

    if ($stmt->execute()) {
        echo "Station created successfully";
    } else {
        echo "Error creating station";
    }
    exit;
}

/* Get all stations for admin (Admin only) */
if (isset($_POST['get_all_stations']) && $_POST['get_all_stations']) {
    if (!$_SESSION["Admin"]) {
        echo "Unauthorized";
        exit;
    }

    $result = $connection->query("
        SELECT s.*, u.Username as Owner 
        FROM Station s 
        LEFT JOIN Users u ON s.Owner_id = u.UserID 
        ORDER BY s.Station_id
    ");

    $html = '<table style="width:100%; border-collapse: collapse;">
                <tr style="background: #f4f4f4;">
                    <th style="border:1px solid #ddd; padding:8px;">ID</th>
                    <th style="border:1px solid #ddd; padding:8px;">Name</th>
                    <th style="border:1px solid #ddd; padding:8px;">Serial</th>
                    <th style="border:1px solid #ddd; padding:8px;">Status</th>
                    <th style="border:1px solid #ddd; padding:8px;">Owner</th>
                </tr>';

    if ($result->num_rows == 0) {
        $html .= '<tr><td colspan="5" style="text-align:center; padding:20px;">No stations found</td></tr>';
    } else {
        while ($row = $result->fetch_assoc()) {
            $html .= '<tr>
                        <td style="border:1px solid #ddd; padding:8px;">' . $row['Station_id'] . '</td>
                        <td style="border:1px solid #ddd; padding:8px;">' . $row['Name'] . '</td>
                        <td style="border:1px solid #ddd; padding:8px;">' . $row['Serial_number'] . '</td>
                        <td style="border:1px solid #ddd; padding:8px;">' . $row['Status'] . '</td>
                        <td style="border:1px solid #ddd; padding:8px;">' . ($row['Owner'] ?: 'None') . '</td>
                      </tr>';
        }
    }

    $html .= '</table>';
    echo $html;
    exit;
}

/* Get all measurements for admin (Admin only) */
if (isset($_POST['get_all_measurements']) && $_POST['get_all_measurements']) {
    if (!$_SESSION["Admin"]) {
        echo "Unauthorized";
        exit;
    }

    $result = $connection->query("
        SELECT m.*, s.Name as StationName 
        FROM Measurement m 
        JOIN Station s ON m.Station_id = s.Station_id 
        ORDER BY m.Timestamp DESC 
        LIMIT 50
    ");

    $html = '<table style="width:100%; border-collapse: collapse;">
                <tr style="background: #f4f4f4;">
                    <th style="border:1px solid #ddd; padding:8px;">ID</th>
                    <th style="border:1px solid #ddd; padding:8px;">Timestamp</th>
                    <th style="border:1px solid #ddd; padding:8px;">Station</th>
                    <th style="border:1px solid #ddd; padding:8px;">Humidity</th>
                    <th style="border:1px solid #ddd; padding:8px;">Air Pressure</th>
                </tr>';

    if ($result->num_rows == 0) {
        $html .= '<tr><td colspan="5" style="text-align:center; padding:20px;">No measurements found</td></tr>';
    } else {
        while ($row = $result->fetch_assoc()) {
            $html .= '<tr>
                        <td style="border:1px solid #ddd; padding:8px;">' . $row['Measurement_id'] . '</td>
                        <td style="border:1px solid #ddd; padding:8px;">' . $row['Timestamp'] . '</td>
                        <td style="border:1px solid #ddd; padding:8px;">' . $row['StationName'] . '</td>
                        <td style="border:1px solid #ddd; padding:8px;">' . $row['Humidity'] . '</td>
                        <td style="border:1px solid #ddd; padding:8px;">' . $row['Air_pressure'] . '</td>
                      </tr>';
        }
    }

    $html .= '</table>';
    echo $html;
    exit;
}

/* Assign measurements to collection (Admin only) */
if (isset($_POST['assign_measurements']) && isset($_POST['collection_id'])) {
    if (!$_SESSION["Admin"]) {
        echo "Unauthorized";
        exit;
    }

    $collection_id = $_POST['collection_id'];
    $measurement_ids = $_POST['measurement_ids'];

    if (!is_array($measurement_ids)) {
        $measurement_ids = [$measurement_ids];
    }

    $success = 0;
    $errors = 0;

    foreach ($measurement_ids as $measurement_id) {
        // Check if already assigned
        $check = $connection->prepare("SELECT * FROM CollectionContains WHERE Collection_id = ? AND Measurement_id = ?");
        $check->bind_param("ii", $collection_id, $measurement_id);
        $check->execute();

        if ($check->get_result()->num_rows == 0) {
            $stmt = $connection->prepare("INSERT INTO CollectionContains (Collection_id, Measurement_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $collection_id, $measurement_id);

            if ($stmt->execute()) {
                $success++;
            } else {
                $errors++;
            }
        }
    }

    echo "Assigned $success measurements. $errors failed.";
    exit;
}

/* Get collections dropdown (Admin only) */
if (isset($_POST['get_collections_dropdown']) && $_POST['get_collections_dropdown']) {
    if (!$_SESSION["Admin"]) {
        echo "Unauthorized";
        exit;
    }

    $result = $connection->query("SELECT Collection_id, Name FROM Collection ORDER BY Name");

    $html = '<option value="">Select Collection</option>';
    while ($row = $result->fetch_assoc()) {
        $html .= '<option value="' . $row['Collection_id'] . '">' . $row['Name'] . '</option>';
    }

    echo $html;
    exit;
}

/* Get measurements dropdown (Admin only) */
if (isset($_POST['get_measurements_dropdown']) && $_POST['get_measurements_dropdown']) {
    if (!$_SESSION["Admin"]) {
        echo "Unauthorized";
        exit;
    }

    $result = $connection->query("SELECT Measurement_id, Timestamp, Station_id FROM Measurement ORDER BY Timestamp DESC LIMIT 100");

    $html = '<option value="">Select Measurements</option>';
    while ($row = $result->fetch_assoc()) {
        $html .= '<option value="' . $row['Measurement_id'] . '">' .
            $row['Measurement_id'] . ' - ' .
            substr($row['Timestamp'], 0, 16) . ' (Station ' . $row['Station_id'] . ')' .
            '</option>';
    }

    echo $html;
    exit;
}

/* Delete user (Admin only) */
if (isset($_POST['delete_user']) && isset($_POST['user_id'])) {
    if (!$_SESSION["Admin"]) {
        echo "Unauthorized";
        exit;
    }

    $user_id = $_POST['user_id'];

    // Don't allow deleting self
    $current_user = getUserInfo($_SESSION['username']);
    if ($current_user['UserID'] == $user_id) {
        echo "Cannot delete yourself";
        exit;
    }

    $stmt = $connection->prepare("DELETE FROM Users WHERE UserID = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        echo "User deleted successfully";
    } else {
        echo "Error deleting user";
    }
    exit;
}

/* ==================== GROUP CHAT HANDLERS ==================== */

/* Create a new chat group and add the creator + selected friends as members */
if (isset($_POST['createGroup'], $_POST['groupName'])) {
    if (!$_SESSION['userLogin']) {
        echo json_encode(['success' => false, 'error' => 'Not logged in']);
        exit;
    }
    $groupName = trim($_POST['groupName']);
    if ($groupName === '') {
        echo json_encode(['success' => false, 'error' => 'Group name cannot be empty']);
        exit;
    }
    $user = getUserInfo($_SESSION['username']);
    $creatorId = $user['UserID'];

    $stmt = $connection->prepare("INSERT INTO ChatGroup (Group_name, Created_by) VALUES (?, ?)");
    $stmt->bind_param('si', $groupName, $creatorId);
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'error' => 'Failed to create group']);
        exit;
    }
    $groupId = $connection->insert_id;

    // Add creator as member
    $addMember = $connection->prepare("INSERT IGNORE INTO GroupMember (Group_id, User_id) VALUES (?, ?)");
    $addMember->bind_param('ii', $groupId, $creatorId);
    $addMember->execute();

    // Add selected friends as members
    $friendIds = isset($_POST['memberIds']) ? $_POST['memberIds'] : [];
    if (!is_array($friendIds)) {
        $friendIds = [$friendIds];
    }
    foreach ($friendIds as $fid) {
        $fid = (int)$fid;
        if ($fid > 0) {
            $addMember->bind_param('ii', $groupId, $fid);
            $addMember->execute();
        }
    }

    echo json_encode(['success' => true, 'groupId' => $groupId, 'groupName' => $groupName]);
    exit;
}

/* Get all groups the current user belongs to */
if (isset($_POST['getMyGroups']) && $_POST['getMyGroups']) {
    if (!$_SESSION['userLogin']) {
        echo json_encode([]);
        exit;
    }
    $user = getUserInfo($_SESSION['username']);
    $userId = $user['UserID'];

    $stmt = $connection->prepare("
        SELECT cg.Group_id, cg.Group_name, cg.Created_by, u.Username AS creator_name,
               COUNT(gm2.User_id) AS member_count
        FROM ChatGroup cg
        JOIN GroupMember gm ON cg.Group_id = gm.Group_id AND gm.User_id = ?
        JOIN Users u ON cg.Created_by = u.UserID
        LEFT JOIN GroupMember gm2 ON cg.Group_id = gm2.Group_id
        GROUP BY cg.Group_id
        ORDER BY cg.Created_at DESC
    ");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $groups = [];
    while ($row = $result->fetch_assoc()) {
        $groups[] = $row;
    }
    echo json_encode($groups);
    exit;
}

/* Add a friend to an existing group (only group creator can add members) */
if (isset($_POST['addGroupMember'], $_POST['groupId'], $_POST['friendId'])) {
    if (!$_SESSION['userLogin']) {
        echo json_encode(['success' => false, 'error' => 'Not logged in']);
        exit;
    }
    $groupId = (int)$_POST['groupId'];
    $friendId = (int)$_POST['friendId'];
    $user = getUserInfo($_SESSION['username']);
    $userId = $user['UserID'];

    // Verify caller is the group creator
    $check = $connection->prepare("SELECT Created_by FROM ChatGroup WHERE Group_id = ?");
    $check->bind_param('i', $groupId);
    $check->execute();
    $checkResult = $check->get_result();
    $groupRow = $checkResult->fetch_assoc();
    if (!$groupRow || (int)$groupRow['Created_by'] !== $userId) {
        echo json_encode(['success' => false, 'error' => 'Only the group creator can add members']);
        exit;
    }

    $stmt = $connection->prepare("INSERT IGNORE INTO GroupMember (Group_id, User_id) VALUES (?, ?)");
    $stmt->bind_param('ii', $groupId, $friendId);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to add member']);
    }
    exit;
}

/* Get messages for a group */
if (isset($_POST['getGroupMessages'], $_POST['groupId'])) {
    if (!$_SESSION['userLogin']) {
        echo json_encode(['success' => false, 'error' => 'Not logged in']);
        exit;
    }
    $groupId = (int)$_POST['groupId'];
    $user = getUserInfo($_SESSION['username']);
    $userId = $user['UserID'];

    // Verify user is a member of the group
    $check = $connection->prepare("SELECT 1 FROM GroupMember WHERE Group_id = ? AND User_id = ?");
    $check->bind_param('ii', $groupId, $userId);
    $check->execute();
    $check->store_result();
    if ($check->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Not a member of this group']);
        exit;
    }

    $afterId = isset($_POST['afterId']) ? (int)$_POST['afterId'] : 0;
    $stmt = $connection->prepare("
        SELECT gm.Message_id, gm.Content, gm.Sent_at, u.Username AS sender_name
        FROM GroupMessage gm
        JOIN Users u ON gm.Sender_id = u.UserID
        WHERE gm.Group_id = ? AND gm.Message_id > ?
        ORDER BY gm.Message_id ASC
    ");
    $stmt->bind_param('ii', $groupId, $afterId);
    $stmt->execute();
    $result = $stmt->get_result();
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    echo json_encode(['success' => true, 'messages' => $messages]);
    exit;
}

/* Send a message to a group */
if (isset($_POST['sendGroupMessage'], $_POST['groupId'], $_POST['content'])) {
    if (!$_SESSION['userLogin']) {
        echo json_encode(['success' => false, 'error' => 'Not logged in']);
        exit;
    }
    $groupId = (int)$_POST['groupId'];
    $content = trim($_POST['content']);
    if ($content === '') {
        echo json_encode(['success' => false, 'error' => 'Message cannot be empty']);
        exit;
    }
    $user = getUserInfo($_SESSION['username']);
    $userId = $user['UserID'];

    // Verify user is a member
    $check = $connection->prepare("SELECT 1 FROM GroupMember WHERE Group_id = ? AND User_id = ?");
    $check->bind_param('ii', $groupId, $userId);
    $check->execute();
    $check->store_result();
    if ($check->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Not a member of this group']);
        exit;
    }

    $stmt = $connection->prepare("INSERT INTO GroupMessage (Group_id, Sender_id, Content) VALUES (?, ?, ?)");
    $stmt->bind_param('iis', $groupId, $userId, $content);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'messageId' => $connection->insert_id]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to send message']);
    }
    exit;
}

//get number of Friends either pending or accepted
function DisplayNumberOfFriends($connection, $status)
{
    $currentUser = getUserInfo($_SESSION["username"]);
    $currentUserID = $currentUser['UserID'];
    if ($status == "pending") {
        $totalNumberOfFreinds = $connection->prepare("SELECT count(*) FROM FriendList WHERE (UserA_ID = ? OR UserB_ID = ?) and status = 'pending'");
    } else {
        $totalNumberOfFreinds = $connection->prepare("SELECT count(*) FROM FriendList WHERE (UserA_ID = ? OR UserB_ID = ?) and status = 'accepted'");
    }
    $totalNumberOfFreinds->bind_param("ii", $currentUserID, $currentUserID);
    $totalNumberOfFreinds->execute();
    $result = $totalNumberOfFreinds->get_result();
    $totalFriends = $result->fetch_row()[0];
    return $totalFriends;
}

if (isset($_POST['getPendingRequests'])) {
    $pendingCount = DisplayNumberOfFriends($connection, "pending");
    $acceptedCount = DisplayNumberOfFriends($connection, "accepted");
    $currentUser = getUserInfo($_SESSION["username"]);
    $currentUserID = $currentUser['UserID'];
    $getPendingRequests = $connection->prepare("SELECT FriendList.*, u.Username, u.Email FROM FriendList JOIN Users u ON FriendList.requested_by = u.UserID WHERE (FriendList.UserA_ID = ? OR FriendList.UserB_ID = ?) AND FriendList.requested_by != ? AND FriendList.status = 'pending'");
    if (!$getPendingRequests) {
        echo json_encode(['error' => 'Prepare failed: ' . $connection->error, 'PendingRequests' => []]);
        exit;
    }
    $getPendingRequests->bind_param("iii", $currentUserID, $currentUserID, $currentUserID);
    if (!$getPendingRequests->execute()) {
        echo json_encode(['error' => 'Execute failed: ' . $getPendingRequests->error, 'PendingRequests' => []]);
        exit;
    }
    $result = $getPendingRequests->get_result();
    $pendingRequests = [];
    while ($row = $result->fetch_assoc()) {
        $pendingRequests[] = $row;
    }
    echo json_encode([
        'PendingRequests' => $pendingRequests,
        'PendingRequestsNumber' => $pendingCount,
        'AcceptedFriendsNumber' => $acceptedCount,
        'currentUserID' => $currentUserID
    ]);
}
