<?php
$host = "localhost";
$username = "root";
$pass = "";
$db = "messages";

$connection = mysqli_connect($host, $username, $pass, $db);

if (isset($_POST['pageLoadedAll'])) {
    /*  echo "working"; */
    $stst = $connection->prepare("select * from users");
    $stst->execute();
    $result = $stst->get_result();

    $userInfo = [];
    while ($row = $result->fetch_assoc()) {
        $userId = $row['userId'];
        $userName = $row['userName'];
        $userInfo[] = [
            "userID" => $userId,
            "username" => $userName
        ];
    };
    echo json_encode($userInfo);
}

if (isset($_POST['btnClicked']) && isset($_POST['newUsername'])) {
    $createNewUser = $connection->prepare("insert into users(userName) VALUES (?)");
    $createNewUser->bind_param('s', $_POST['newUsername']);

    if ($createNewUser->execute()) {
        echo "User " . $_POST['newUsername'] . " created successfully!";
    }
}

if (isset($_POST['toUser']) && isset($_POST['mesg'])) {
    $sendSMS = $connection->prepare("insert into messages(userId, messageText) VALUES (?,?)");
    $sendSMS->bind_param('is', $_POST['toUser'], $_POST['mesg']);
    if ($sendSMS->execute()) {
        echo "working";
    }
}

if (isset($_POST['msgBelongTo'])) {
    $showMsg = $connection->prepare("select * from messages where userId = ? order by desc");
    $showMsg->bind_param('i', $_POST['msgBelongTo']);
    $showMsg->execute();
    $result = $showMsg->get_result();
    $allMsg = [];
    while ($row = $result->fetch_assoc()) {
        $msg = $row['messageText'];
        $allMsg[] = [
            "msg" => $msg
        ];
    }
    echo json_encode($allMsg);
}
