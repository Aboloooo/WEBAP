<?php
include_once("../MyLibrary.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- CDN jQuery pull -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="../js/jquery.js"></script>
    <!-- my vanila js script -->
    <script src="../js/MyScript.js"></script>
    <!-- bank of icons -->
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EnvMonitor - Friendship</title>
    <link rel="stylesheet" href="../MyStyle.css">
</head>

<body>
    <?php
    NavigationBarE();
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
                <form method="post" class="friendship-form-card" onsubmit="FriendshipRequest(event)">
                    <h2>Add Friend</h2>
                    <p>Enter a username to send a friendship request.</p>
                    <input type="text" name="username" id="targetUsernameToBeFriend" placeholder="Enter username" required>
                    <button type="submit" name="submitBtn">
                        <i class='bx bx-plus-circle'></i> Add Friend
                    </button>
                </form>
            </div>
        </div>
    </section>

</body>