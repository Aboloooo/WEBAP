<?php
$connection = new mysqli("localhost", "root", "", "MiniWebchat");

if (isset($_POST["userName"], $_POST["message"])) {

    // find out if the userName is ALREADY in the db
    $sqlFindUser = $connection->prepare("SELECT * FROM Users WHERE userName=?");
    $sqlFindUser->bind_param("s", $_POST["userName"]);
    $sqlFindUser->execute();
    $result = $sqlFindUser->get_result();

    if ($result->num_rows == 0) {
        // insert the user into the db
        $sqlCreateUser = $connection->prepare("INSERT INTO Users(userName) VALUES(?)");
        $sqlCreateUser->bind_param("s", $_POST["userName"]);
        $sqlCreateUser->execute();
    }

    // find the id of the user
    $sqlFindUser = $connection->prepare("SELECT * FROM Users WHERE userName=?");
    $sqlFindUser->bind_param("s", $_POST["userName"]);
    $sqlFindUser->execute();
    $result = $sqlFindUser->get_result();
    $row = $result->fetch_assoc();
    $userId = $row["userId"];

    $sqlCreateMessage = $connection->prepare(
        "INSERT INTO Messages(MessageText, userId) VALUES(?, ?)"
    );
    $sqlCreateMessage->bind_param("si", $_POST["message"], $userId);
    $sqlCreateMessage->execute();
}

if (isset($_GET["lastMessageSeen"])) {

    $sqlSelectMessages = $connection->prepare(
        "SELECT * FROM Messages NATURAL JOIN Users WHERE MessageId > ?"
    );
    $sqlSelectMessages->bind_param("i", $_GET["lastMessageSeen"]);
    $sqlSelectMessages->execute();
    $result = $sqlSelectMessages->get_result();

    while ($row = $result->fetch_assoc()) {
?>
        <div>
            <span class="MsgId"><?= $row["MessageId"] ?></span>
            <?= $row["userName"] ?> wrote: <?= $row["MessageText"] ?>
        </div>
<?php
    }
}
?>