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
        if (isset($_POST['serialN_input']) && !empty(trim($_POST['serialN_input']))) {
            $stationFinder = $connection->prepare("SELECT * FROM Station WHERE Serial_number =?");
            $stationFinder->bind_param('s', $_POST['serialN_input']);
            $stationFinder->execute();
            $result = $stationFinder->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $SerialNumber = $row['Serial_number'];
                $Name = $row['Name'];
                $Status = $row['Status'];
                $New_status = 'assigned';
                $station_owner = $row['Owner_ID'];
                /* if user is not login there must be an error */
                $curentUser = getUserInfo($_SESSION['username']);
                echo "<script>alert('station found');</script>";

                if ($curentUser) {
                    $curentUser_ID = $curentUser['UserID'];
                    /* assign the station if station is not already assigned to someone or current user */
                    if ($Status == 'assigned' && $station_owner == $curentUser_ID) {
                        echo "This station already assigned to you!";
                        return;
                    }
                    if ($Status == 'assigned') {
                        echo "This station already assigned to another user!";
                        return;
                    }
                    echo "<script>alert('current user found and two condition passed');</script>";

                    $addStation = $connection->prepare("UPDATE Station SET Owner_ID = ? , Status = ? WHERE Serial_number =? AND Owner_ID IS NULL");
                    $addStation->bind_param("iss", $curentUser_ID, $New_status, $_POST['serialN_input']);

                    if ($addStation->execute()) {
                        echo "<script>alert('Station $Name with $SerialNumber serial number added to your list successfully!');</script>";
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
    <div>
        <div class="layer-content">
            <h3>Register your station</h3>
            <div class="">
                <form method="post">
                    <h2>Find</h2>
                    <input type="text" name="serialN_input">
                    <button type="submit" name="submitBtn">Add</button>
                </form>
            </div>
        </div>
    </div>


</body>