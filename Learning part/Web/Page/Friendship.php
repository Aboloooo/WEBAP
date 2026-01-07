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
    <title>index</title>
    <style>
        body {
            background-image: url("../img/FriendshipImg.jpg");
            background-repeat: no-repeat;
            background-size: cover;
        }
    </style>
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
        if (isset($_POST['public_id']) && !empty(trim($_POST['public_id']))) {
            $friendFinder = $connection->prepare("SELECT * FROM Users WHERE Public_UserID =?");
            $friendFinder->bind_param('s', $_POST['public_id']);
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
                       window.location.href = 'index.php';
                        </script>";
                    }

                    $checkQuery = $connection->prepare("SELECT * FROM FriendList WHERE (UserA_ID = ? AND UserB_ID = ?) OR (UserB_ID = ? AND UserA_ID = ?) ");
                    $checkQuery->bind_param("iiii", $UserA_ID, $UserB_ID, $UserB_ID, $UserA_ID);
                    $checkQuery->execute();
                    $checkResult = $checkQuery->get_result();
                    if ($checkResult->num_rows > 0) {
                        echo "You are already friend with this user";
                    } else {
                        $createFriendship = $connection->prepare("insert into FriendList(UserA_ID ,UserB_ID) VALUES (?,?)");
                        $createFriendship->bind_param('ii', $UserA_ID, $UserB_ID);
                        if ($createFriendship->execute()) {
                            echo "<script>alert('Friend added successfully!');</script>";
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
    ?>
    <div>
        <div class="layer-content">
            <h3>Friend Statistics</h3>
            <div class="cards-grid">
                <div class="card">
                    <span>Totol Friends:</span>
                    <span>0</span>
                </div>
                <div class="card" onclick="MessageAll()">
                    <span>Message all</span>
                </div>
                <div class="card">
                    <span>Pending Friendship request:</span>
                    <span>0</span>
                </div>
            </div>
            <div class="friendFinderContainer">
                <form method="post">
                    <h2>Find</h2>
                    <input type="text" name="public_id" placeholder="Enter ID here">
                    <button type="submit" name="submitBtn">Add</button>
                </form>
            </div>
        </div>
    </div>


</body>