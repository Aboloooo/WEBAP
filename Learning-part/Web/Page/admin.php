<?php
include_once("../MyLibrary.php");

// Check if user is admin
if (!$_SESSION["Admin"]) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="../MyScript.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../MyStyle.css">
</head>

<body>
    <?php NavigationBarE(); ?>

    <div class="admin-container">
        <h1>Admin Dashboard</h1>
        <p>Welcome, <?= $_SESSION["username"] ?> (Admin)</p>

        <div class="admin-grid">
            <!-- Manage Users Card -->
            <div class="admin-card">
                <h3>Manage Users</h3>
                <div class="admin-form">
                    <h4>Create New User</h4>
                    <form method="post" action="../MyLibrary.php">
                        <input type="text" name="new_username" placeholder="Username" required>
                        <input type="password" name="new_password" placeholder="Password" required>
                        <input type="text" name="new_fullname" placeholder="Full Name" required>
                        <input type="email" name="new_email" placeholder="Email" required>
                        <select name="new_role" required>
                            <option value="">Select Role</option>
                            <option value="1">Admin</option>
                            <option value="2">Dev</option>
                            <option value="3">User</option>
                        </select>
                        <button type="submit" name="create_user">Create User</button>
                    </form>
                </div>
                <div class="admin-data-container" id="usersList">
                    Loading users...
                </div>
            </div>

            <!-- Manage Stations Card -->
            <div class="admin-card">
                <h3>Manage Stations</h3>
                <div class="admin-form">
                    <h4>Create New Station</h4>
                    <form method="post" action="../MyLibrary.php">
                        <input type="text" name="station_name" placeholder="Station Name" required>
                        <input type="text" name="station_serial" placeholder="Serial Number" required>
                        <textarea name="station_description" placeholder="Description"></textarea>
                        <button type="submit" name="create_station">Create Station</button>
                    </form>
                </div>
                <div class="admin-data-container" id="stationsList">
                    Loading stations...
                </div>
            </div>

            <!-- View Measurements Card -->
            <div class="admin-card">
                <h3>View Measurements</h3>
                <div class="admin-data-container" id="measurementsList">
                    Loading measurements...
                </div>
            </div>

            <!-- Assign Measurements Card -->
            <div class="admin-card">
                <h3>Assign Measurements</h3>
                <div class="admin-form">
                    <form method="post" action="../MyLibrary.php">
                        <select name="collection_id" id="collectionSelect" required>
                            <option value="">Select Collection</option>
                        </select>
                        <select name="measurement_ids[]" id="measurementSelect" multiple required style="height: 150px;">
                            <option value="">Select Measurements (Ctrl+Click for multiple)</option>
                        </select>
                        <button type="submit" name="assign_measurements">Assign to Collection</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Load users
            $.post("../MyLibrary.php", {
                get_all_users: true
            }, function(data) {
                $("#usersList").html(data);
            });

            // Load stations
            $.post("../MyLibrary.php", {
                get_all_stations: true
            }, function(data) {
                $("#stationsList").html(data);
            });

            // Load measurements
            $.post("../MyLibrary.php", {
                get_all_measurements: true
            }, function(data) {
                $("#measurementsList").html(data);
            });

            // Load collections for dropdown
            $.post("../MyLibrary.php", {
                get_collections_dropdown: true
            }, function(data) {
                $("#collectionSelect").html(data);
            });

            // Load measurements for dropdown
            $.post("../MyLibrary.php", {
                get_measurements_dropdown: true
            }, function(data) {
                $("#measurementSelect").html(data);
            });
        });
    </script>
</body>

</html>