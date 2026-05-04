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
    <title>EnvMonitor - Station Registration</title>
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
                $curentUser = getUserInfo($_SESSION['username']);
                if ($curentUser) {
                    $curentUser_ID = $curentUser['UserID'];
                    if ($Status == 'assigned') {
                        echo "<script>alert('This station already assigned!');</script>";
                    } else {
                        /* update StationOwnership table (assigning owner) */
                        $recordOwnership = $connection->prepare("UPDATE Station SET Status = ?, Owner_id = ? where Station_id = ?");
                        $recordOwnership->bind_param("sii", $New_status, $curentUser_ID, $station_ID);
                        if ($recordOwnership->execute()) {
                            echo "<script>alert('$Name with $SerialNumber serial number added to your list successfully!');</script>";
                            /* update user table(assigning station) */
                            $updateUserStation = $connection->prepare("UPDATE Station SET Status = ?  WHERE Station_id =?");
                            $updateUserStation->bind_param("si", $New_status, $station_ID);
                            $updateUserStation->execute();
                        }
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
    <section id="StationRegistration">
        <div class="station-reg-header">
            <h1 class="section-title">Register Your Station</h1>
            <p class="section-text">Add new environmental monitoring stations to your network by entering their serial numbers.</p>
        </div>

        <div class="station-reg-form">
            <div class="form-card">
                <h3>Enter Station Serial Number</h3>
                <form method="post" class="registration-form">
                    <div class="form-group">
                        <label for="serialN_input">Station Serial Number</label>
                        <input type="text" id="serialN_input" name="serialN_input" placeholder="Enter serial number..." required>
                    </div>
                    <button type="submit" name="submitBtn" class="btn btn-primary">Register Station</button>
                </form>
            </div>
        </div>

        <div class="my-stations-section">
            <h2>My Stations</h2>
            <p class="section-subtitle">Manage your registered environmental monitoring stations</p>

            <div class="mainStationDisplay">
                <?php
                $curentUser = getUserInfo($_SESSION['username']);
                if ($curentUser) {
                    $curentUser_ID = $curentUser['UserID'];
                }
                $displyStations = $connection->prepare("SELECT * FROM Station WHERE Owner_id = ?");
                $displyStations->bind_param('i', $curentUser_ID);
                $displyStations->execute();
                $result = $displyStations->get_result();
                if ($result->num_rows > 0) {
                    while ($stationRow = $result->fetch_assoc()) {
                        $ID = $stationRow['Station_id'];
                        $name = $stationRow['Name'];
                        $Description = $stationRow['Description'];
                ?>
                        <div class="stationCard" id="stationCard-<?= $ID ?>">
                            <button onclick="removeMyStation(<?= $ID ?>)" class="remove-station-btn" title="Remove station">×</button>
                            <button onclick="editStation(<?= $ID ?>)" class="edit-station-btn" title="Edit station">✏️</button>
                            <div class="station-icon">📡</div>
                            <h3 class="station-name-display"><?= htmlspecialchars($name) ?></h3>
                            <p class="station-desc-display"><?= htmlspecialchars($Description) ?></p>
                            <div class="station-edit-form" style="display:none;">
                                <input type="text" class="station-edit-name" value="<?= htmlspecialchars($name) ?>" placeholder="Station name" maxlength="50">
                                <textarea class="station-edit-desc" placeholder="Description" maxlength="255"><?= htmlspecialchars($Description) ?></textarea>
                                <div class="station-edit-actions">
                                    <button onclick="saveStationEdit(<?= $ID ?>)" class="btn btn-save">Save</button>
                                    <button onclick="cancelStationEdit(<?= $ID ?>)" class="btn btn-secondary">Cancel</button>
                                </div>
                            </div>
                        </div>
                    <?php
                    }
                } else {
                    ?>
                    <div class="no-stations">
                        <div class="no-stations-icon">📭</div>
                        <h3>No Stations Yet</h3>
                        <p>You haven't registered any stations yet. Use the form above to add your first station.</p>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>
    </section>


</body>