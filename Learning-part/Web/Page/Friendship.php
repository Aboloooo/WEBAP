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
    <!-- bank of icons -->
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friendship</title>
    <link rel="stylesheet" href="../MyStyle.css">
</head>

<body>
    <?php
    NavigationBarE();
    ?>
    <?php

    if (isset($_POST['submitBtn'])) {
        if (!$_SESSION["userLogin"]) {
            echo "<script>
        alert('Please login first');
        window.location.href = 'sign_in_up.php';
    </script>";
            exit;
        }
        if (isset($_POST['username']) && !empty(trim($_POST['username']))) {
            $friendFinder = $connection->prepare("SELECT * FROM Users WHERE username =?");
            $friendFinder->bind_param('s', $_POST['username']);
            $friendFinder->execute();
            $result = $friendFinder->get_result();

            if ($result->num_rows > 0) {
                $rowB = $result->fetch_assoc();
                $UserB_ID = $rowB['UserID'];
                $userA = getUserInfo($_SESSION['username']);
                if ($userA) {
                    $UserA_ID = $userA['UserID'];
                    if ($UserA_ID == $UserB_ID) {
                        echo "<script>alert('You cannot add yourself as a friend!');
                       window.location.href = 'Friendship.php';
                        </script>";
                        return;
                    }

                    $checkQuery = $connection->prepare("SELECT * FROM FriendList WHERE (UserA_ID = ? AND UserB_ID = ?) OR (UserB_ID = ? AND UserA_ID = ?) ");
                    $checkQuery->bind_param("iiii", $UserA_ID, $UserB_ID, $UserB_ID, $UserA_ID);
                    $checkQuery->execute();
                    $checkResult = $checkQuery->get_result();
                    $numberOfRow = $checkResult->num_rows;
                    if ($numberOfRow > 0) {
                        echo "You are already friend with this user";
                    } else {
                        $createFriendship = $connection->prepare("insert into FriendList(UserA_ID ,UserB_ID,status,requested_by) VALUES (?,?,?,?)");
                        $status = 'pending';
                        $requested_by = $UserA_ID;
                        $createFriendship->bind_param('iisi', $UserA_ID, $UserB_ID, $status, $requested_by);
                        if ($createFriendship->execute()) {
                            echo "<script>alert('Friendship request sent successfully!');</script>";
                        } else {
                            echo "<script>alert('Error adding friend: ' . $connection->error);</script>";
                        }
                    }
                }
            } else {
                echo "User didnt found";
            }
        } else {
            echo "User ID is required";
        }
    }
    $totalFriends = DisplayNumberOfFriends($connection, 'accepted');
    $totalPendingRequests = DisplayNumberOfFriends($connection, 'pending');

    ?>
    <script>
        window.currentUsername = <?= json_encode($_SESSION["username"]) ?>;
    </script>
    <section id="Friendship" class="friendship-page">
        <div class="friendship-header">
            <h1 class="section-title">Grow Your Friend Network</h1>
            <p class="section-description">
                Add friends, manage your social circle, and quickly message everyone in one place.
            </p>
        </div>

        <div class="layer-content friendship-content-wrap">
            <div class="cards-grid friendship-stats-grid">
                <div class="card friendship-stat-card" onclick="DisplayFriends()" role="button" tabindex="0">
                    <div class="friendship-stat-icon"><i class='bx bx-group'></i></div>
                    <span class="friendship-stat-label">Total Friends</span>
                    <span class="friendship-stat-value"><?= $totalFriends ?></span>
                </div>
                <div class="card friendship-stat-card" onclick="ShowGroupChats()" role="button" tabindex="0">
                    <div class="friendship-stat-icon"><i class='bx bx-group'></i></div>
                    <span class="friendship-stat-label">Group Chats</span>
                    <span class="friendship-stat-value">Create / Open</span>
                </div>
                <div class="card friendship-stat-card" onclick="DisplayPendingRequests()" role="button" tabindex="0">
                    <div class="friendship-stat-icon"><i class='bx bx-user-plus'></i></div>
                    <span class="friendship-stat-label">Friendship Requests</span>
                    <span class="friendship-stat-value"><?= $totalPendingRequests ?></span>
                </div>
            </div>

            <div class="friendFinderContainer friendship-finder-wrap">
                <form method="post" class="friendship-form-card">
                    <h2>Add Friend</h2>
                    <p>Enter a username to send a friendship request.</p>
                    <input type="text" name="username" placeholder="Enter username" required>
                    <button type="submit" name="submitBtn">
                        <i class='bx bx-plus-circle'></i> Add Friend
                    </button>
                </form>
            </div>
        </div>
    </section>

</body>