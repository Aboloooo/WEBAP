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
        if (isset($_POST['serialN_input']) && !empty(trim($_POST['serialN_input']))) {
            $stationFinder = $connection->prepare("SELECT * FROM Station WHERE Serial_number =?");
            $stationFinder->bind_param('s', $_POST['serialN_input']);
            $stationFinder->execute();
            $result = $stationFinder->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $station_ID = $row['Station_id'];
                $SerialNumber = $row['Serial_number'];
                $Name = $row['Name'];
                $Status = $row['Status'];
                $New_status = 'assigned';
                $station_owner = $row['Owner_ID'];
                $curentUser = getUserInfo($_SESSION['username']);
                if ($curentUser) {
                    $curentUser_ID = $curentUser['UserID'];
                    /* assign the station if station is not already assigned to someone or current user */
                    if ($Status == 'assigned' && $station_owner == $curentUser_ID) {
                        echo "<script>alert('This station already assigned to you!');</script>";
                    }
                    if ($Status == 'assigned') {
                        echo "<script>alert('This station already assigned to another user!');</script>";
                    }
                    /* update station table (assigning owner) */
                    $updateStationOwner = $connection->prepare("UPDATE Station SET Owner_ID = ? , Status = ? WHERE Serial_number =? AND Owner_ID IS NULL");
                    $updateStationOwner->bind_param("iss", $curentUser_ID, $New_status, $_POST['serialN_input']);

                    /* update user table(assigning station) */
                    $updateUserStation = $connection->prepare("UPDATE Users SET owner_of_station = ?  WHERE UserID =?");
                    $updateUserStation->bind_param("si", $station_ID, $curentUser_ID);
                    $updateUserStation->execute();

                    if ($updateStationOwner->execute()) {
                        echo "<script>alert('$Name with $SerialNumber serial number added to your list successfully!');</script>";
                    }
                } else {
                    /* if user is not login there must be an error */
                }
            } else {
                echo "<script>alert('Station didnt found');</script>";
            }
        } else {
            echo "<script>alert('Serial number of station is required');</script>";
        }
    }
    ?>
    <section>
        <h1>Register Your Station</h1>
        <div class="">
            <form method="post">
                <h3>Enter Station Serial Number</h3>
                <input type="text" name="serialN_input">
                <button type="submit" name="submitBtn">Register</button>
            </form>
        </div>
        <h2>My Stations</h2>
        <!-- display stations -->
        <div class="mainStationDisplay">
            <?php
            $curentUser = getUserInfo($_SESSION['username']);
            if ($curentUser) {
                $curentUser_ID = $curentUser['UserID'];
            }
            $displyStations = $connection->prepare("SELECT * FROM Station WHERE Owner_ID = ?");
            $displyStations->bind_param('i', $curentUser_ID);
            $displyStations->execute();
            $result = $displyStations->get_result();
            if ($result->num_rows > 0) {
                while ($stationRow = $result->fetch_assoc()) {
                    $name = $stationRow['Name'];
                    $Description = $stationRow['Description'];
            ?>
                    <div class="stationCard">
                        <h3><?= $name ?></h3>
                        <p><?= $Description ?></p>
                    </div>
            <?php
                }
            } else {
                /* what if there is no station assigned */
                ?>
                <p>There is no station assigned to you</p>
                <?php
            }
            ?>
        </div>
    </section>


</body>