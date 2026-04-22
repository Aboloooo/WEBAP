<?php
session_start();
require_once(__DIR__ . '/db_config.php');
/* connection to database */
$connection = createDatabaseConnection();

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

    // Check if user created any collection
    $stmt = $connection->prepare("SELECT 1 FROM Collection WHERE Creator_ID = ? LIMIT 1");
    if (!$stmt) return false;
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) return true;

    // Check if any collection is shared with the user
    $stmt = $connection->prepare("SELECT 1 FROM CollectionShare WHERE Shared_with = ? LIMIT 1");
    if (!$stmt) return false;
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 0;
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
    echo json_encode(['redirect' => './sign_in_up.php']);
    exit;
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
            // notify the recipient about the shared collection
            $senderName = $_SESSION['username'] ?? 'Someone';
            $notifMsg = "$senderName shared a collection with you";
            $notifStmt = $connection->prepare("INSERT INTO Notifications (user_id, type, message) VALUES (?, 'collection_share', ?)");
            if ($notifStmt) {
                $notifStmt->bind_param('is', $friendId, $notifMsg);
                $notifStmt->execute();
            }
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


// get unread notification counts per type
if (isset($_POST['getNotifCounts'])) {
    $counts = ['friend_request' => 0, 'collection_share' => 0];
    $user = getUserInfo($_SESSION['username'] ?? '');
    if ($user) {
        $userId = $user['UserID'];
        $stmt = $connection->prepare("SELECT type, COUNT(*) as cnt FROM Notifications WHERE user_id = ? AND is_read = 0 GROUP BY type");
        if ($stmt) {
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $counts[$row['type']] = (int)$row['cnt'];
            }
        }
    }
    echo json_encode($counts);
    exit;
}

// mark notifications as read for a given type
if (isset($_POST['markNotifRead'], $_POST['notifType'])) {
    $user = getUserInfo($_SESSION['username'] ?? '');
    if ($user) {
        $userId = $user['UserID'];
        $type = $_POST['notifType'];
        $stmt = $connection->prepare("UPDATE Notifications SET is_read = 1 WHERE user_id = ? AND type = ? AND is_read = 0");
        $stmt->bind_param('is', $userId, $type);
        $stmt->execute();
    }
    exit;
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

// update station name and description
if (isset($_POST['updateStation'], $_POST['stationId'], $_POST['stationName'], $_POST['stationDesc'])) {
    $user = getUserInfo($_SESSION['username'] ?? '');
    if ($user) {
        $userId = $user['UserID'];
        $stationId = (int) $_POST['stationId'];
        $newName = trim($_POST['stationName']);
        $newDesc = trim($_POST['stationDesc']);
        $stmt = $connection->prepare("UPDATE Station SET Name = ?, Description = ? WHERE Station_id = ? AND Owner_id = ?");
        $stmt->bind_param('ssii', $newName, $newDesc, $stationId, $userId);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

// remove friend
if (isset($_POST['removeFriend']) && isset($_POST['target_user'])) {
    $MyInfo = getUserInfo($_SESSION["username"]);
    $MyID = $MyInfo['UserID'];
    $removeFriend = $connection->prepare("DELETE FROM FriendList WHERE (UserA_ID = ? AND UserB_ID = ?) OR (UserB_ID = ? AND UserA_ID = ?);");
    $removeFriend->bind_param('iiii', $MyID, $_POST['target_user'], $MyID, $_POST['target_user']);
    if ($removeFriend->execute()) {
        echo "Friendship with user ID: " . $_POST['target_user'] . " ended successfully.";
    }
}
// remove collection
if (isset($_POST['removeCollection'], $_POST['targetCollectionID']) && $_POST['removeCollection'] == true) {
    $targetCollectionID = (int) $_POST['targetCollectionID'];

    // 1. Remove from CollectionContains
    $stmt = $connection->prepare('DELETE FROM CollectionContains WHERE Collection_id = ?');
    $stmt->bind_param("i", $targetCollectionID);
    $stmt->execute();

    // 2. Remove from CollectionShare (FK blocks Collection deletion if skipped)
    $stmt = $connection->prepare('DELETE FROM CollectionShare WHERE Collection_id = ?');
    $stmt->bind_param("i", $targetCollectionID);
    $stmt->execute();

    // 3. Now safely delete from Collection
    $stmt = $connection->prepare('DELETE FROM Collection WHERE Collection_id = ?');
    $stmt->bind_param("i", $targetCollectionID);
    if ($stmt->execute()) {
        echo json_encode(['success' => "Collection $targetCollectionID deleted successfully."]);
    } else {
        echo json_encode(['error' => "Failed to delete collection: " . $stmt->error]);
    }
    exit;
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

// get latest single measurement for the dashboard metric cards
if (isset($_POST['getLatestMeasurement'])) {
    $user = getUserInfo($_SESSION['username'] ?? '');
    if (!$user) {
        echo json_encode(['success' => false]);
        exit;
    }
    $ownerId = $user['UserID'];
    $stationId = (int)($_POST['stationId'] ?? 0);
    if ($stationId == 0) {
        $sql = "SELECT m.* FROM Measurement m INNER JOIN Station s ON m.Station_id = s.Station_id WHERE s.Owner_id = ? ORDER BY m.Timestamp DESC LIMIT 1";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param('i', $ownerId);
    } else {
        $sql = "SELECT m.* FROM Measurement m INNER JOIN Station s ON m.Station_id = s.Station_id WHERE m.Station_id = ? AND s.Owner_id = ? ORDER BY m.Timestamp DESC LIMIT 1";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param('ii', $stationId, $ownerId);
    }
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    echo json_encode(['success' => (bool)$row, 'measurement' => $row]);
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
        // Filter based on date only, oldest -> newest for top-to-bottom display
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
    $MyInfo = getUserInfo($_SESSION['username'] ?? '');
    $isLoggedIn = $_SESSION["userLogin"] ?? false;
?>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">EnvMonitor</div>
            <ul class="nav-links">
                <li><a href="index.php#Home">Home</a></li>
                <li><a href="index.php#About">About</a></li>
                <li><a href="index.php#Service">Service</a></li>
                <li><a href="index.php#Dashboard">Dashboard</a></li>
                <?php if ($isLoggedIn): ?>
                    <li><a href="./StationRegistration.php"><i class='bx bx-plus-circle'></i> Register Station</a></li>
                    <li><a href="./Friendship.php" id="navFriendsLink"><i class='bx bx-group'></i> Friends<span class="notif-badge" id="friendsNotifBadge" style="display:none"></span></a></li>
                    <?php if ($MyInfo && userHasCollections($MyInfo['UserID'])): ?>
                        <li><a href="./Collection.php" id="navCollectionLink">My Collection<span class="notif-badge" id="collectionNotifBadge" style="display:none"></span></a></li>
                    <?php endif; ?>
                    <?php if ($_SESSION["Admin"]): ?>
                        <li><a href="./admin.php">Admin Panel</a></li>
                    <?php endif; ?>
                <?php else: ?>
                    <li><a href="./sign_in_up.php" class="nav-cta-btn"><i class='bx bx-user-plus'></i> Create Account</a></li>
                <?php endif; ?>
                <li><a href="index.php#Contact">Contact</a></li>
            </ul>
            <button class="public-message-notif-bell" onclick="showPublicMessage()" id="DisplayPublicMessage" title="Display Public Message">
                <box-icon name="bell"></box-icon>
            </button>
        </div>
    </nav>
    <div class="login_container_indexPage">
        <div id="goToLogin">
            <img src="../img/User.png" alt="not found">
            <span><?php if ($isLoggedIn) {
                        print($_SESSION["username"]);
                    ?>
                    <br>
                    <?php if ($_SESSION["Admin"]) echo "<small>(Admin)</small>"; ?>
                <?php
                    } else {
                        print("username");
                    } ?></span>
        </div>
        <?php if ($isLoggedIn): ?>
            <button class="nav-logout-btn" onclick="Logout()"><i class='bx bx-log-out'></i> Logout</button>
        <?php endif; ?>
    </div>
    <!-- Fixed dark/light mode toggle -->
    <button class="theme-toggle-fab" onclick="toggleDarkMode()" id="darkModeBtn" title="Switch to dark mode">
        <box-icon name="moon"></box-icon>
    </button>
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

    $html = '<table>
                <thead><tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr></thead><tbody>';

    if ($result->num_rows == 0) {
        $html .= '<tr><td colspan="6" style="text-align:center; padding:20px;">No users found</td></tr>';
    } else {
        while ($row = $result->fetch_assoc()) {
            $uid = $row['UserID'];
            $roleVal = $row['AccessLevelID'];
            $html .= '<tr>
                        <td>' . $uid . '</td>
                        <td>' . htmlspecialchars($row['Username']) . '</td>
                        <td>' . htmlspecialchars($row['Fullname']) . '</td>
                        <td>' . htmlspecialchars($row['Email']) . '</td>
                        <td>
                          <select class="role-select admin-inline-select" data-user-id="' . $uid . '">
                            <option value="1"' . ($roleVal == 1 ? ' selected' : '') . '>Admin</option>
                            <option value="2"' . ($roleVal == 2 ? ' selected' : '') . '>Dev</option>
                            <option value="3"' . ($roleVal == 3 ? ' selected' : '') . '>User</option>
                          </select>
                        </td>
                        <td>
                          <button class="admin-delete-btn" data-type="user" data-id="' . $uid . '">🗑 Delete</button>
                        </td>
                      </tr>';
        }
    }

    $html .= '</tbody></table>';
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

    $html = '<table>
                <thead><tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Serial</th>
                    <th>Status</th>
                    <th>Owner</th>
                    <th>Actions</th>
                </tr></thead><tbody>';

    if ($result->num_rows == 0) {
        $html .= '<tr><td colspan="6" style="text-align:center; padding:20px;">No stations found</td></tr>';
    } else {
        while ($row = $result->fetch_assoc()) {
            $sid = $row['Station_id'];
            $ownerId = $row['Owner_id'] ?? '';
            $statusBadge = $row['Status'] === 'assigned'
                ? '<span class="admin-badge admin-badge-green">Assigned</span>'
                : '<span class="admin-badge admin-badge-gray">Available</span>';
            $html .= '<tr id="station-row-' . $sid . '">
                        <td>' . $sid . '</td>
                        <td>' . htmlspecialchars($row['Name']) . '</td>
                        <td>' . htmlspecialchars($row['Serial_number']) . '</td>
                        <td>' . $statusBadge . '</td>
                        <td>' . htmlspecialchars($row['Owner'] ?: 'None') . '</td>
                        <td>
                          <button class="admin-edit-station-btn admin-btn admin-btn-blue admin-btn-sm"
                            data-id="' . $sid . '"
                            data-name="' . htmlspecialchars($row['Name'], ENT_QUOTES) . '"
                            data-owner="' . $ownerId . '">
                            ✏️ Edit
                          </button>
                          <button class="admin-delete-btn" data-type="station" data-id="' . $sid . '">🗑 Delete</button>
                        </td>
                      </tr>
                      <tr class="station-edit-row" id="station-edit-' . $sid . '" style="display:none;">
                        <td colspan="6">
                          <div class="station-admin-edit-form">
                            <div class="station-admin-edit-fields">
                              <div class="station-admin-edit-field">
                                <label>Station Name</label>
                                <input type="text" class="station-edit-name-input" value="' . htmlspecialchars($row['Name'], ENT_QUOTES) . '">
                              </div>
                              <div class="station-admin-edit-field">
                                <label>Owner</label>
                                <select class="station-edit-owner-select">
                                  <option value="">— No owner (unassign) —</option>
                                </select>
                              </div>
                            </div>
                            <div class="station-admin-edit-actions">
                              <button class="admin-btn admin-btn-green save-station-edit-btn" data-id="' . $sid . '" data-current-owner="' . $ownerId . '">💾 Save</button>
                              <button class="admin-btn cancel-station-edit-btn" data-id="' . $sid . '">Cancel</button>
                            </div>
                            <div class="station-edit-feedback" id="station-edit-feedback-' . $sid . '"></div>
                          </div>
                        </td>
                      </tr>';
        }
    }

    $html .= '</tbody></table>';
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

    $html = '<table>
                <thead><tr>
                    <th>ID</th>
                    <th>Timestamp</th>
                    <th>Station</th>
                    <th>Humidity (%)</th>
                    <th>Air Pressure (hPa)</th>
                    <th>Light (lux)</th>
                    <th>Air Quality</th>
                </tr></thead><tbody>';

    if ($result->num_rows == 0) {
        $html .= '<tr><td colspan="7" style="text-align:center; padding:20px;">No measurements found</td></tr>';
    } else {
        while ($row = $result->fetch_assoc()) {
            $html .= '<tr>
                        <td>' . $row['Measurement_id'] . '</td>
                        <td>' . htmlspecialchars($row['Timestamp']) . '</td>
                        <td>' . htmlspecialchars($row['StationName']) . '</td>
                        <td>' . $row['Humidity'] . '</td>
                        <td>' . $row['Air_pressure'] . '</td>
                        <td>' . $row['Light_intensity'] . '</td>
                        <td>' . $row['Air_quality'] . '</td>
                      </tr>';
        }
    }

    $html .= '</tbody></table>';
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

/* Delete station (Admin only) */
if (isset($_POST['delete_station']) && isset($_POST['station_id'])) {
    if (!$_SESSION["Admin"]) {
        echo "Unauthorized";
        exit;
    }
    $station_id = (int)$_POST['station_id'];
    $stmt = $connection->prepare("DELETE FROM Station WHERE Station_id = ?");
    $stmt->bind_param("i", $station_id);
    echo $stmt->execute() ? "Station deleted successfully" : "Error deleting station";
    exit;
}

/* Change user role (Admin only) */
if (isset($_POST['change_role'], $_POST['user_id'], $_POST['new_role'])) {
    if (!$_SESSION["Admin"]) {
        echo "Unauthorized";
        exit;
    }
    $userId = (int)$_POST['user_id'];
    $newRole = (int)$_POST['new_role'];
    $current_user = getUserInfo($_SESSION['username']);
    if ($current_user['UserID'] == $userId) {
        echo json_encode(['success' => false, 'message' => 'Cannot change your own role']);
        exit;
    }
    $stmt = $connection->prepare("UPDATE Users SET AccessLevelID = ? WHERE UserID = ?");
    $stmt->bind_param("ii", $newRole, $userId);
    echo $stmt->execute() ? json_encode(['success' => true]) : json_encode(['success' => false]);
    exit;
}

/* Get admin stats (Admin only) */
if (isset($_POST['get_admin_stats'])) {
    if (!$_SESSION["Admin"]) {
        echo json_encode([]);
        exit;
    }
    $users = $connection->query("SELECT COUNT(*) as c FROM Users")->fetch_assoc()['c'];
    $stations = $connection->query("SELECT COUNT(*) as c FROM Station")->fetch_assoc()['c'];
    $measurements = $connection->query("SELECT COUNT(*) as c FROM Measurement")->fetch_assoc()['c'];
    $collections = $connection->query("SELECT COUNT(*) as c FROM Collection")->fetch_assoc()['c'];
    echo json_encode(['users' => $users, 'stations' => $stations, 'measurements' => $measurements, 'collections' => $collections]);
    exit;
}

/* ==================== GROUP CHAT HANDLERS ==================== */

/* Get all users as JSON for admin dropdowns (Admin only) */
if (isset($_POST['get_users_for_select'])) {
    if (!$_SESSION["Admin"]) {
        echo json_encode([]);
        exit;
    }
    $result = $connection->query("SELECT UserID, Username, Fullname FROM Users ORDER BY Username");
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    echo json_encode($users);
    exit;
}

/* Update station name and/or owner (Admin only) */
if (isset($_POST['update_station_admin'], $_POST['station_id'])) {
    if (!$_SESSION["Admin"]) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    $stationId = (int)$_POST['station_id'];
    $newName   = trim($_POST['station_name'] ?? '');
    $newOwner  = $_POST['new_owner_id'] !== '' ? (int)$_POST['new_owner_id'] : null;

    if ($newName === '') {
        echo json_encode(['success' => false, 'message' => 'Station name cannot be empty']);
        exit;
    }

    // Determine status based on owner
    $newStatus = $newOwner !== null ? 'assigned' : 'available';

    if ($newOwner !== null) {
        $stmt = $connection->prepare("UPDATE Station SET Name = ?, Owner_id = ?, Status = ? WHERE Station_id = ?");
        $stmt->bind_param("sisi", $newName, $newOwner, $newStatus, $stationId);
    } else {
        $stmt = $connection->prepare("UPDATE Station SET Name = ?, Owner_id = NULL, Status = ? WHERE Station_id = ?");
        $stmt->bind_param("ssi", $newName, $newStatus, $stationId);
    }

    if ($stmt->execute()) {
        // Fetch updated owner username for response
        $ownerName = 'None';
        if ($newOwner !== null) {
            $r = $connection->prepare("SELECT Username FROM Users WHERE UserID = ?");
            $r->bind_param("i", $newOwner);
            $r->execute();
            $r->bind_result($ownerName);
            $r->fetch();
        }
        echo json_encode(['success' => true, 'name' => $newName, 'owner' => $ownerName, 'status' => $newStatus]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
    exit;
}

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
    $currentUser = getUserInfo($_SESSION["username"] ?? '');
    if (!$currentUser) return 0;
    $currentUserID = $currentUser['UserID'];
    if ($status == "pending") {
        $totalNumberOfFreinds = $connection->prepare("SELECT count(*) FROM FriendList WHERE (UserA_ID = ? OR UserB_ID = ?) and status = 'pending' and requested_by != ?");
        $totalNumberOfFreinds->bind_param("iii", $currentUserID, $currentUserID, $currentUserID);
    } else {
        $totalNumberOfFreinds = $connection->prepare("SELECT count(*) FROM FriendList WHERE (UserA_ID = ? OR UserB_ID = ?) and status = 'accepted'");
        $totalNumberOfFreinds->bind_param("ii", $currentUserID, $currentUserID);
    }
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
if (isset($_POST['friendRequestAction'], $_POST['requestId'])) {
    // Return JSON so client can inspect errors on the webserver
    header('Content-Type: application/json');

    if (!isset($_SESSION["username"]) || empty($_SESSION["username"])) {
        echo json_encode(['error' => 'Not authenticated']);
        exit;
    }

    $current_user = getUserInfo($_SESSION["username"]);
    if (!$current_user) {
        echo json_encode(['error' => 'Invalid session user']);
        exit;
    }
    $current_userId = $current_user['UserID'];

    $parts = explode(',', $_POST['requestId']);
    if (count($parts) !== 2) {
        echo json_encode(['error' => 'Invalid requestId format']);
        exit;
    }

    $UserA_ID = (int) $parts[0];
    $UserB_ID = (int) $parts[1];

    $action = $_POST['friendRequestAction'];
    if ($action === "accept") {
        $newStatus = 'accepted';
    } elseif ($action === "delete") {
        $newStatus = 'rejected';
    } else {
        echo json_encode(['error' => 'Unknown action']);
        exit;
    }

    // Update regardless of user order (UserA/UserB) and ensure the actor is not the one who requested it
    $sql = "UPDATE FriendList SET status = ? WHERE ((UserA_ID = ? AND UserB_ID = ?) OR (UserA_ID = ? AND UserB_ID = ?)) AND requested_by != ?";
    $changeFriendshipStatus = $connection->prepare($sql);
    if (!$changeFriendshipStatus) {
        echo json_encode(['error' => 'Prepare failed: ' . $connection->error]);
        exit;
    }

    // bind: s (status) then 5 ints
    $changeFriendshipStatus->bind_param("siiiii", $newStatus, $UserA_ID, $UserB_ID, $UserB_ID, $UserA_ID, $current_userId);

    if ($changeFriendshipStatus->execute()) {
        if ($changeFriendshipStatus->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => "Friendship status updated to $newStatus"]);
        } else {
            echo json_encode(['error' => 'No rows updated. Either the request does not exist or you are the requester.']);
        }
    } else {
        echo json_encode(['error' => 'Execute failed: ' . $changeFriendshipStatus->error]);
    }
    exit;
}
