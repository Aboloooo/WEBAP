<?php
session_start();
require_once(__DIR__ . '/db_config.php');
// connect to the database
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


// get user info by username
function getUserInfo($username)
{
    global $connection;
    $userInfo = $connection->prepare("SELECT * FROM Users WHERE Username = ?");
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

function getNotificationTypeId(string $typeKey): ?int
{
    global $connection;

    static $typeCache = [];
    if (array_key_exists($typeKey, $typeCache)) {
        return $typeCache[$typeKey];
    }

    $stmt = $connection->prepare("SELECT NotificationType_ID FROM NotificationType WHERE type_key = ? LIMIT 1");
    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('s', $typeKey);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $typeCache[$typeKey] = $row ? (int)$row['NotificationType_ID'] : null;

    return $typeCache[$typeKey];
}

function createNotification(int $userId, string $typeKey, string $message): bool
{
    global $connection;

    $notificationTypeId = getNotificationTypeId($typeKey);
    if (!$notificationTypeId) {
        return false;
    }

    $stmt = $connection->prepare("INSERT INTO Notifications (user_id, notification_type_id, message) VALUES (?, ?, ?)");
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('iis', $userId, $notificationTypeId, $message);
    return $stmt->execute();
}

// Remove a collection
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

// Logout
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

// Share a collection with friends
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
            createNotification((int)$friendId, 'collection_share', $notifMsg);
        }
    }

    echo "Shared with " . $success . " friend(s)";
    exit;
}

// Stop sharing a collection
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
// Return collections related to me
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
    $counts = ['friend_request' => 0, 'collection_share' => 0, 'message' => 0, 'public_announcement' => 0];
    $user = getUserInfo($_SESSION['username'] ?? '');
    if ($user) {
        $userId = $user['UserID'];
        $stmt = $connection->prepare("SELECT nt.type_key, COUNT(*) as cnt FROM Notifications n JOIN NotificationType nt ON n.notification_type_id = nt.NotificationType_ID WHERE n.user_id = ? AND n.is_read = 0 GROUP BY nt.type_key");
        if ($stmt) {
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $counts[$row['type_key']] = (int)$row['cnt'];
            }
        }
    }
    echo json_encode($counts);
    exit;
}

// Simple poll endpoint: returns a single number 'total_unread'
// Use this when your client just wants to know if there are any unread items
// Example AJAX: { simplePollNotif: true }
if (isset($_POST['simplePollNotif'])) {
    $total = 0;
    $user = getUserInfo($_SESSION['username'] ?? '');
    if ($user) {
        $uid = (int)$user['UserID'];
        $s = $connection->prepare("SELECT COUNT(*) as c FROM Notifications WHERE user_id = ? AND is_read = 0");
        if ($s) {
            $s->bind_param('i', $uid);
            $s->execute();
            $r = $s->get_result();
            if ($row = $r->fetch_assoc()) {
                $total = (int)$row['c'];
            }
        }
    }
    echo json_encode(['total_unread' => $total]);
    exit;
}

if (isset($_POST['getAllNotifications'])) {
    $user = getUserInfo($_SESSION['username'] ?? '');
    if (!$user) {
        echo json_encode(['success' => false, 'notifications' => []]);
        exit;
    }

    $userId = (int)$user['UserID'];
    $stmt = $connection->prepare("SELECT n.id, n.message, n.is_read, n.created_at, nt.type_key, nt.display_name FROM Notifications n JOIN NotificationType nt ON n.notification_type_id = nt.NotificationType_ID WHERE n.user_id = ? ORDER BY n.created_at DESC, n.id DESC LIMIT 50");
    if (!$stmt) {
        echo json_encode(['success' => false, 'notifications' => []]);
        exit;
    }

    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }

    echo json_encode(['success' => true, 'notifications' => $notifications]);
    exit;
}

// mark notifications as read for a given type
if (isset($_POST['markNotifRead'], $_POST['notifType'])) {
    $user = getUserInfo($_SESSION['username'] ?? '');
    if ($user) {
        $userId = $user['UserID'];
        $type = $_POST['notifType'];
        $stmt = $connection->prepare("UPDATE Notifications n JOIN NotificationType nt ON n.notification_type_id = nt.NotificationType_ID SET n.is_read = 1 WHERE n.user_id = ? AND nt.type_key = ? AND n.is_read = 0");
        $stmt->bind_param('is', $userId, $type);
        $stmt->execute();
    }
    exit;
}

if (isset($_POST['markAllNotificationsRead'])) {
    $user = getUserInfo($_SESSION['username'] ?? '');
    if ($user) {
        $userId = (int)$user['UserID'];
        $stmt = $connection->prepare("UPDATE Notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
        if ($stmt) {
            $stmt->bind_param('i', $userId);
            $stmt->execute();
        }
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

// get trend measurements for dashboard chart
if (isset($_POST['getTrendMeasurements'])) {
    $user = getUserInfo($_SESSION['username'] ?? '');
    if (!$user) {
        echo json_encode(['success' => false, 'data' => []]);
        exit;
    }

    $ownerId = (int)$user['UserID'];
    $stationId = (int)($_POST['stationId'] ?? 0);
    $period = $_POST['period'] ?? '24h';
    $metricKey = $_POST['metric'] ?? 'humidity';

    $metricColumnMap = [
        'humidity'   => 'Humidity',
        'pressure'   => 'Air_pressure',
        'light'      => 'Light_intensity',
        'airquality' => 'Air_quality',
        'temperature' => 'Temperature',
    ];
    $metricColumn = $metricColumnMap[$metricKey] ?? 'Humidity';

    $intervalSecondsMap = [
        '1h'  => 3600,
        '24h' => 86400,
        '7d'  => 604800,
        '30d' => 2592000,
    ];
    $intervalSeconds = $intervalSecondsMap[$period] ?? 86400;

    // Step 1: find the latest available timestamp for this owner/station
    if ($stationId === 0) {
        $anchorSql = "SELECT MAX(m.Timestamp) AS max_ts
            FROM Measurement m
            INNER JOIN Station s ON m.Station_id = s.Station_id
            WHERE s.Owner_id = ?";
        $anchorStmt = $connection->prepare($anchorSql);
        $anchorStmt->bind_param('i', $ownerId);
    } else {
        $anchorSql = "SELECT MAX(m.Timestamp) AS max_ts
            FROM Measurement m
            INNER JOIN Station s ON m.Station_id = s.Station_id
            WHERE m.Station_id = ? AND s.Owner_id = ?";
        $anchorStmt = $connection->prepare($anchorSql);
        $anchorStmt->bind_param('ii', $stationId, $ownerId);
    }
    $anchorStmt->execute();
    $anchorRow = $anchorStmt->get_result()->fetch_assoc();
    $anchorTs  = $anchorRow['max_ts'] ?? null;

    if (!$anchorTs) {
        echo json_encode(['success' => true, 'data' => [], 'metric' => $metricKey]);
        exit;
    }

    $startTs = date('Y-m-d H:i:s', strtotime($anchorTs) - $intervalSeconds);

    // Step 2: fetch measurements in the computed time window
    if ($stationId === 0) {
        $sql = "SELECT m.Timestamp, m.`$metricColumn` AS value
            FROM Measurement m
            INNER JOIN Station s ON m.Station_id = s.Station_id
            WHERE s.Owner_id = ?
              AND m.Timestamp BETWEEN ? AND ?
            ORDER BY m.Timestamp ASC
            LIMIT 500";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param('iss', $ownerId, $startTs, $anchorTs);
    } else {
        $sql = "SELECT m.Timestamp, m.`$metricColumn` AS value
            FROM Measurement m
            INNER JOIN Station s ON m.Station_id = s.Station_id
            WHERE m.Station_id = ? AND s.Owner_id = ?
              AND m.Timestamp BETWEEN ? AND ?
            ORDER BY m.Timestamp ASC
            LIMIT 500";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param('iiss', $stationId, $ownerId, $startTs, $anchorTs);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $points = [];
    while ($row = $result->fetch_assoc()) {
        $points[] = [
            'Timestamp' => $row['Timestamp'],
            'value'     => $row['value'] !== null ? (float)$row['value'] : null,
        ];
    }

    echo json_encode(['success' => true, 'data' => $points, 'metric' => $metricKey]);
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

// Get new measurements since a timestamp (real-time)
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

// Save changes to user credentials

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
            <button class="public-message-notif-bell" onclick="DisplayNotification()" id="DisplayPublicMessage" title="Display Public Message">
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


// === Admin functions ===

// Check if a user is an admin
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

// Update session admin flag after login
if ($_SESSION["userLogin"]) {
    $_SESSION["Admin"] = isAdmin($_SESSION["username"]);
}

// Admin POST handlers

// Create a new user (admin only)
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

// Publish a public announcement to all users (admin only)
if (isset($_POST['publish_public_message'], $_POST['public_message'])) {
    if (!$_SESSION["Admin"]) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $publicMessage = trim($_POST['public_message']);
    if ($publicMessage === '') {
        echo json_encode(['success' => false, 'message' => 'Message cannot be empty']);
        exit;
    }
    if (strlen($publicMessage) > 255) {
        echo json_encode(['success' => false, 'message' => 'Message is too long (max 255 characters)']);
        exit;
    }

    $usersResult = $connection->query("SELECT UserID FROM Users");
    if (!$usersResult || $usersResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'No users found']);
        exit;
    }

    $publicAnnouncementTypeId = getNotificationTypeId('public_announcement');
    if (!$publicAnnouncementTypeId) {
        echo json_encode(['success' => false, 'message' => 'Notification type not found']);
        exit;
    }

    $insertNotif = $connection->prepare("INSERT INTO Notifications (user_id, notification_type_id, message) VALUES (?, ?, ?)");
    if (!$insertNotif) {
        echo json_encode(['success' => false, 'message' => 'Failed to prepare notification insert']);
        exit;
    }

    $inserted = 0;
    $connection->begin_transaction();
    try {
        while ($userRow = $usersResult->fetch_assoc()) {
            $targetUserId = (int)$userRow['UserID'];
            $insertNotif->bind_param('iis', $targetUserId, $publicAnnouncementTypeId, $publicMessage);
            if (!$insertNotif->execute()) {
                throw new Exception('Insert failed');
            }
            $inserted++;
        }
        $connection->commit();
        echo json_encode(['success' => true, 'message' => "Public message published to {$inserted} users."]);
    } catch (Exception $e) {
        $connection->rollback();
        echo json_encode(['success' => false, 'message' => 'Failed to publish public message']);
    }
    exit;
}

// Get public announcements for the logged-in user
if (isset($_POST['get_public_announcements'])) {
    if (!($_SESSION['userLogin'] ?? false)) {
        echo json_encode(['success' => false, 'messages' => []]);
        exit;
    }

    $user = getUserInfo($_SESSION['username'] ?? '');
    if (!$user) {
        echo json_encode(['success' => false, 'messages' => []]);
        exit;
    }

    $userId = (int)$user['UserID'];
    $stmt = $connection->prepare("SELECT n.id, n.message, n.created_at FROM Notifications n JOIN NotificationType nt ON n.notification_type_id = nt.NotificationType_ID WHERE n.user_id = ? AND nt.type_key = 'public_announcement' ORDER BY n.created_at DESC LIMIT 20");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }

    echo json_encode(['success' => true, 'messages' => $messages]);
    exit;
}

// Get all users (admin only)
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

// === Group chat handlers ===

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

    $stmt = $connection->prepare("INSERT INTO ChatGroup (Group_name, Creator_id) VALUES (?, ?)");
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Failed to prepare group creation: ' . $connection->error]);
        exit;
    }
    $stmt->bind_param('si', $groupName, $creatorId);
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'error' => 'Failed to create group: ' . $stmt->error]);
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
        SELECT cg.Group_id, cg.Group_name, cg.Creator_id AS Created_by, u.Username AS creator_name,
               COUNT(gm2.User_id) AS member_count
        FROM ChatGroup cg
        JOIN GroupMember gm ON cg.Group_id = gm.Group_id AND gm.User_id = ?
        JOIN Users u ON cg.Creator_id = u.UserID
        LEFT JOIN GroupMember gm2 ON cg.Group_id = gm2.Group_id
        GROUP BY cg.Group_id
        ORDER BY cg.Created_at DESC
    ");
    if (!$stmt) {
        echo json_encode([]);
        exit;
    }
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
    $check = $connection->prepare("SELECT Creator_id FROM ChatGroup WHERE Group_id = ?");
    if (!$check) {
        echo json_encode(['success' => false, 'error' => 'Failed to verify group creator: ' . $connection->error]);
        exit;
    }
    $check->bind_param('i', $groupId);
    $check->execute();
    $checkResult = $check->get_result();
    $groupRow = $checkResult->fetch_assoc();
    if (!$groupRow || (int)$groupRow['Creator_id'] !== $userId) {
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

    // Fetch group messages directly from Message table; Group_id is stored on Message
    $stmt = $connection->prepare(
        "SELECT m.Message_ID AS Message_id, m.Message_content AS Content, m.Message_time AS Sent_at, u.Username AS sender_name FROM Message m JOIN Users u ON m.Sender_ID = u.UserID WHERE m.Group_id = ? AND m.Message_ID > ? ORDER BY m.Message_ID ASC"
    );
    $stmt->bind_param('ii', $groupId, $afterId);
    $stmt->execute();
    $result = $stmt->get_result();
    $messages = [];
    $messageIds = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
        $messageIds[] = (int)$row['Message_id'];
    }

    // Mark fetched messages as read by the current user
    if (!empty($messageIds)) {
        $markRead = $connection->prepare('INSERT IGNORE INTO MessageRead (message_id, user_id) VALUES (?, ?)');
        if ($markRead) {
            foreach ($messageIds as $mid) {
                $markRead->bind_param('ii', $mid, $userId);
                $markRead->execute();
            }
            $markRead->close();
        }
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

    // Insert message into Message table and set Group_id so group messages live only in Message
    $currentTime = date('Y-m-d H:i:s');
    $insertMessage = $connection->prepare("INSERT INTO Message (Message_content, Sender_ID, Group_id, Message_time) VALUES (?, ?, ?, ?)");
    if (!$insertMessage) {
        echo json_encode(['success' => false, 'error' => 'Failed to prepare message insert']);
        exit;
    }
    $insertMessage->bind_param('siis', $content, $userId, $groupId, $currentTime);
    if ($insertMessage->execute()) {
        $messageId = $insertMessage->insert_id;

        // Mark message as read by the sender immediately
        $markRead = $connection->prepare('INSERT IGNORE INTO MessageRead (message_id, user_id) VALUES (?, ?)');
        if ($markRead) {
            $markRead->bind_param('ii', $messageId, $userId);
            $markRead->execute();
            $markRead->close();
        }

        echo json_encode(['success' => true, 'messageId' => $messageId]);
        // Notify all other group members (exclude sender)
        // Fetch group name for nicer notification text
        $groupName = '';
        $gstmt = $connection->prepare("SELECT Group_name FROM ChatGroup WHERE Group_id = ? LIMIT 1");
        if ($gstmt) {
            $gstmt->bind_param('i', $groupId);
            $gstmt->execute();
            $gstmt->bind_result($groupName);
            $gstmt->fetch();
            $gstmt->close();
        }

        $NewMessageDefault = "New message from {$user['Username']}";
        if (!empty($groupName)) {
            $NewMessageDefault .= " in group '{$groupName}'";
        }

        $membersStmt = $connection->prepare("SELECT User_id FROM GroupMember WHERE Group_id = ? AND User_id != ?");
        if ($membersStmt) {
            $membersStmt->bind_param('ii', $groupId, $userId);
            $membersStmt->execute();
            $membersResult = $membersStmt->get_result();
            while ($mrow = $membersResult->fetch_assoc()) {
                $targetId = (int)$mrow['User_id'];
                // best-effort: create notification for each member; ignore failures
                createNotification($targetId, 'message', $NewMessageDefault);
            }
            $membersStmt->close();
        }
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
// Sending friendship request
if (isset($_POST['FriendshipDemand'], $_POST['targetFriend'])) {
    //Get your userID and target friend ID
    $current_user = getUserInfo($_SESSION["username"]);
    if (!$current_user) {
        echo json_encode(['error' => 'Current user could not be loaded. Please sign in again.']);
        exit;
    }

    $current_user_ID = $current_user['UserID'];
    $targetUsername = trim($_POST['targetFriend']);
    $targetFriend = getUserInfo($targetUsername);
    if (!$targetFriend) {
        echo json_encode(['error' => 'User not found.']);
        exit;
    }

    $targetFriend_ID = $targetFriend['UserID'];

    //check current friendship status
    //Get your userID
    //Check that for doublication demand
    //You can not add yourself as friend
    if ($current_user_ID === $targetFriend_ID) {
        echo json_encode(['error' => 'You cannot add yourself as a friend.']);
        exit;
    }

    // Check for duplicate friendship demand
    $checkDuplicate = $connection->prepare('SELECT * FROM FriendList WHERE (UserA_ID = ? AND UserB_ID = ?) OR (UserA_ID = ? AND UserB_ID = ?)');
    $checkDuplicate->bind_param('iiii', $current_user_ID, $targetFriend_ID, $targetFriend_ID, $current_user_ID);
    $checkDuplicate->execute();
    $result = $checkDuplicate->get_result();
    if ($result->num_rows > 0) {
        $existingRow = $result->fetch_assoc();
        $existingStatus = $existingRow['status'];
        if ($existingStatus === 'pending') {
            echo json_encode(['error' => 'A friendship request is already pending with this user.']);
            exit;
        } elseif ($existingStatus === 'accepted') {
            echo json_encode(['error' => 'You are already friends with this user.']);
            exit;
        } elseif ($existingStatus === 'rejected') {
            // Allow resending — reset the existing record to pending
            $resend = $connection->prepare("UPDATE FriendList SET status = 'pending', requested_by = ? WHERE (UserA_ID = ? AND UserB_ID = ?) OR (UserA_ID = ? AND UserB_ID = ?)");
            $resend->bind_param('iiiii', $current_user_ID, $current_user_ID, $targetFriend_ID, $targetFriend_ID, $current_user_ID);
            if ($resend->execute() && $resend->affected_rows > 0) {
                echo json_encode(['success' => 'Your friendship request has been sent.']);
            } else {
                echo json_encode(['error' => 'Failed to resend friendship request: ' . $resend->error]);
            }
            exit;
        }
    }

    // Insert new friendship demand
    $insertFriendshipDemand = $connection->prepare('INSERT INTO FriendList (UserA_ID, UserB_ID, status, requested_by) VALUES (?, ?, ?, ?)');
    $status = 'pending';
    $insertFriendshipDemand->bind_param('iisi', $current_user_ID, $targetFriend_ID, $status, $current_user_ID);
    if ($insertFriendshipDemand->execute()) {
        echo json_encode(['success' => 'Your friendship request has been sent.']);
        // Optionally, create a notification for the target user
        createNotification($targetFriend_ID, 'friend_request', "You have a new friendship request from {$current_user['Username']}");
    } else {
        echo json_encode(['error' => 'Failed to send friendship request: ' . $insertFriendshipDemand->error]);
    }
    exit;
}
