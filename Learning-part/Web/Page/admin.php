<?php
include_once("../MyLibrary.php");

if (!$_SESSION["Admin"]) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script> -->
    <script src="../js/jquery.js"></script>
    <script src="../js/MyScript.js"></script>
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EnvMonitor - Admin Dashboard</title>
    <link rel="stylesheet" href="../MyStyle.css">
</head>

<body>
    <?php NavigationBarE(); ?>

    <div class="admin-container">
        <div class="admin-header">
            <div>
                <h1><i class='bx bx-shield-alt-2'></i> Admin Dashboard</h1>
                <p>Welcome back, <strong><?= htmlspecialchars($_SESSION["username"]) ?></strong></p>
            </div>
        </div>

        <!-- Stats Row -->
        <div class="admin-stats-row" id="adminStatsRow">
            <div class="admin-stat-card">
                <i class='bx bx-user'></i>
                <div>
                    <div class="admin-stat-value" id="statUsers">--</div>
                    <div class="admin-stat-label">Total Users</div>
                </div>
            </div>
            <div class="admin-stat-card">
                <i class='bx bx-broadcast'></i>
                <div>
                    <div class="admin-stat-value" id="statStations">--</div>
                    <div class="admin-stat-label">Total Stations</div>
                </div>
            </div>
            <div class="admin-stat-card">
                <i class='bx bx-data'></i>
                <div>
                    <div class="admin-stat-value" id="statMeasurements">--</div>
                    <div class="admin-stat-label">Measurements</div>
                </div>
            </div>
            <div class="admin-stat-card">
                <i class='bx bx-folder'></i>
                <div>
                    <div class="admin-stat-value" id="statCollections">--</div>
                    <div class="admin-stat-label">Collections</div>
                </div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="admin-tabs">
            <button class="admin-tab active" data-tab="users"><i class='bx bx-group'></i> Users</button>
            <button class="admin-tab" data-tab="stations"><i class='bx bx-broadcast'></i> Stations</button>
            <button class="admin-tab" data-tab="measurements"><i class='bx bx-line-chart'></i> Measurements</button>
            <button class="admin-tab" data-tab="assign"><i class='bx bx-link'></i> Assign Data</button>
        </div>

        <div class="admin-grid">

            <!-- USERS TAB -->
            <div class="admin-tab-panel active" id="tab-users">
                <div class="admin-card">
                    <h3><i class='bx bx-megaphone'></i> Publish Public Message</h3>
                    <div class="admin-form" id="publicMessageForm">
                        <div class="admin-form-row">
                            <textarea id="public_message_text" placeholder="Write a public message for all users..." maxlength="255" style="width:100%; min-height:100px; resize:vertical;"></textarea>
                        </div>
                        <div class="admin-form-row">
                            <button class="admin-btn admin-btn-blue" id="publishPublicMessageBtn"><i class='bx bx-send'></i> Publish Message</button>
                        </div>
                        <div class="admin-feedback" id="publicMessageFeedback"></div>
                    </div>
                </div>
                <div class="admin-card">
                    <h3><i class='bx bx-user-plus'></i> Create New User</h3>
                    <div class="admin-form" id="createUserForm">
                        <div class="admin-form-row">
                            <input type="text" id="new_username" placeholder="Username" required>
                            <input type="password" id="new_password" placeholder="Password" required>
                        </div>
                        <div class="admin-form-row">
                            <input type="text" id="new_fullname" placeholder="Full Name" required>
                            <input type="email" id="new_email" placeholder="Email" required>
                        </div>
                        <div class="admin-form-row">
                            <select id="new_role">
                                <option value="">Select Role</option>
                                <option value="1">Admin</option>
                                <option value="2">Dev</option>
                                <option value="3">User</option>
                            </select>
                            <button class="admin-btn admin-btn-green" id="createUserBtn"><i class='bx bx-user-plus'></i> Create User</button>
                        </div>
                        <div class="admin-feedback" id="createUserFeedback"></div>
                    </div>
                </div>
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h3><i class='bx bx-group'></i> All Users</h3>
                        <button class="admin-btn admin-btn-blue admin-btn-sm" id="refreshUsers"><i class='bx bx-refresh'></i> Refresh</button>
                    </div>
                    <div class="admin-data-container" id="usersList">Loading users...</div>
                </div>
            </div>

            <!-- STATIONS TAB -->
            <div class="admin-tab-panel" id="tab-stations">
                <div class="admin-card">
                    <h3><i class='bx bx-plus-circle'></i> Create New Station</h3>
                    <div class="admin-form">
                        <div class="admin-form-row">
                            <input type="text" id="station_name" placeholder="Station Name" required>
                            <input type="text" id="station_serial" placeholder="Serial Number" required>
                        </div>
                        <div class="admin-form-row">
                            <input type="text" id="station_description" placeholder="Description">
                            <button class="admin-btn admin-btn-green" id="createStationBtn"><i class='bx bx-plus'></i> Create Station</button>
                        </div>
                        <div class="admin-feedback" id="createStationFeedback"></div>
                    </div>
                </div>
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h3><i class='bx bx-broadcast'></i> All Stations</h3>
                        <button class="admin-btn admin-btn-blue admin-btn-sm" id="refreshStations"><i class='bx bx-refresh'></i> Refresh</button>
                    </div>
                    <div class="admin-data-container" id="stationsList">Loading stations...</div>
                </div>
            </div>

            <!-- MEASUREMENTS TAB -->
            <div class="admin-tab-panel" id="tab-measurements">
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h3><i class='bx bx-line-chart'></i> Latest 50 Measurements</h3>
                        <button class="admin-btn admin-btn-blue admin-btn-sm" id="refreshMeasurements"><i class='bx bx-refresh'></i> Refresh</button>
                    </div>
                    <div class="admin-data-container" id="measurementsList">Loading measurements...</div>
                </div>
            </div>

            <!-- ASSIGN TAB -->
            <div class="admin-tab-panel" id="tab-assign">
                <div class="admin-card">
                    <h3><i class='bx bx-link'></i> Assign Measurements to Collection</h3>
                    <div class="admin-form">
                        <div class="admin-form-row">
                            <select id="collectionSelect" required>
                                <option value="">Select Collection</option>
                            </select>
                        </div>
                        <div class="admin-form-row">
                            <select id="measurementSelect" multiple style="height:160px;">
                                <option value="">Select Measurements (Ctrl+Click for multiple)</option>
                            </select>
                        </div>
                        <button class="admin-btn admin-btn-green" id="assignMeasurementsBtn"><i class='bx bx-check'></i> Assign to Collection</button>
                        <div class="admin-feedback" id="assignFeedback"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        $(document).ready(function() {

            // === TAB SWITCHING ===
            $(".admin-tab").on("click", function() {
                $(".admin-tab").removeClass("active");
                $(".admin-tab-panel").removeClass("active");
                $(this).addClass("active");
                $("#tab-" + $(this).data("tab")).addClass("active");
            });

            // === LOAD STATS ===
            function loadStats() {
                $.post("../MyLibrary.php", {
                    get_admin_stats: true
                }, function(data) {
                    if (data) {
                        $("#statUsers").text(data.users);
                        $("#statStations").text(data.stations);
                        $("#statMeasurements").text(data.measurements);
                        $("#statCollections").text(data.collections);
                    }
                }, "json");
            }

            // === LOAD LISTS ===
            function loadUsers() {
                $("#usersList").html('<span class="admin-loading">Loading...</span>');
                $.post("../MyLibrary.php", {
                    get_all_users: true
                }, function(data) {
                    $("#usersList").html(data);
                });
            }

            function loadStations() {
                $("#stationsList").html('<span class="admin-loading">Loading...</span>');
                $.post("../MyLibrary.php", {
                    get_all_stations: true
                }, function(data) {
                    $("#stationsList").html(data);
                });
            }

            function loadMeasurementsList() {
                $("#measurementsList").html('<span class="admin-loading">Loading...</span>');
                $.post("../MyLibrary.php", {
                    get_all_measurements: true
                }, function(data) {
                    $("#measurementsList").html(data);
                });
            }

            loadStats();
            loadUsers();
            loadStations();
            loadMeasurementsList();

            $.post("../MyLibrary.php", {
                get_collections_dropdown: true
            }, function(data) {
                $("#collectionSelect").html(data);
            });
            $.post("../MyLibrary.php", {
                get_measurements_dropdown: true
            }, function(data) {
                $("#measurementSelect").html(data);
            });

            // Pre-load user list for station owner dropdowns
            let allUsers = [];
            $.post("../MyLibrary.php", {
                get_users_for_select: true
            }, function(data) {
                allUsers = data;
            }, "json");

            // === REFRESH BUTTONS ===
            $("#refreshUsers").on("click", loadUsers);
            $("#refreshStations").on("click", loadStations);
            $("#refreshMeasurements").on("click", loadMeasurementsList);

            // === CREATE USER ===
            $("#createUserBtn").on("click", function() {
                const u = $("#new_username").val().trim();
                const p = $("#new_password").val().trim();
                const f = $("#new_fullname").val().trim();
                const e = $("#new_email").val().trim();
                const r = $("#new_role").val();
                if (!u || !p || !f || !e || !r) {
                    showFeedback("#createUserFeedback", "Please fill in all fields.", "error");
                    return;
                }
                $.post("../MyLibrary.php", {
                    create_user: true,
                    new_username: u,
                    new_password: p,
                    new_fullname: f,
                    new_email: e,
                    new_role: r
                }, function(res) {
                    showFeedback("#createUserFeedback", res, res.includes("success") ? "success" : "error");
                    if (res.includes("success")) {
                        $("#new_username, #new_password, #new_fullname, #new_email").val("");
                        $("#new_role").val("");
                        loadUsers();
                        loadStats();
                    }
                });
            });

            // === PUBLISH PUBLIC MESSAGE ===
            $("#publishPublicMessageBtn").on("click", function() {
                const msg = $("#public_message_text").val().trim();
                if (!msg) {
                    showFeedback("#publicMessageFeedback", "Please write a message before publishing.", "error");
                    return;
                }
                $.post("../MyLibrary.php", {
                    publish_public_message: true,
                    public_message: msg
                }, function(res) {
                    showFeedback("#publicMessageFeedback", res.message || "Done", res.success ? "success" : "error");
                    if (res.success) {
                        $("#public_message_text").val("");
                    }
                }, "json").fail(function() {
                    showFeedback("#publicMessageFeedback", "Failed to publish message.", "error");
                });
            });

            // === CREATE STATION ===
            $("#createStationBtn").on("click", function() {
                const n = $("#station_name").val().trim();
                const s = $("#station_serial").val().trim();
                const d = $("#station_description").val().trim();
                if (!n || !s) {
                    showFeedback("#createStationFeedback", "Name and serial number are required.", "error");
                    return;
                }
                $.post("../MyLibrary.php", {
                    create_station: true,
                    station_name: n,
                    station_serial: s,
                    station_description: d
                }, function(res) {
                    showFeedback("#createStationFeedback", res, res.includes("success") ? "success" : "error");
                    if (res.includes("success")) {
                        $("#station_name, #station_serial, #station_description").val("");
                        loadStations();
                        loadStats();
                    }
                });
            });

            // === ASSIGN MEASUREMENTS ===
            $("#assignMeasurementsBtn").on("click", function() {
                const cid = $("#collectionSelect").val();
                const mids = $("#measurementSelect").val();
                if (!cid || !mids || mids.length === 0) {
                    showFeedback("#assignFeedback", "Select a collection and at least one measurement.", "error");
                    return;
                }
                $.post("../MyLibrary.php", {
                    assign_measurements: true,
                    collection_id: cid,
                    "measurement_ids[]": mids
                }, function(res) {
                    showFeedback("#assignFeedback", res, "success");
                });
            });

            // === DELETE & CHANGE ROLE (delegated) ===
            $(document).on("click", ".admin-delete-btn", function() {
                const type = $(this).data("type");
                const id = $(this).data("id");
                if (!confirm("Are you sure you want to delete this " + type + "?")) return;
                const $row = $(this).closest("tr");
                if (type === "user") {
                    $.post("../MyLibrary.php", {
                        delete_user: true,
                        user_id: id
                    }, function(res) {
                        if (res.includes("success")) {
                            $row.fadeOut(300, function() {
                                $(this).remove();
                            });
                            loadStats();
                        } else {
                            alert(res);
                        }
                    });
                } else if (type === "station") {
                    $.post("../MyLibrary.php", {
                        delete_station: true,
                        station_id: id
                    }, function(res) {
                        if (res.includes("success")) {
                            $row.fadeOut(300, function() {
                                $(this).remove();
                            });
                            loadStats();
                        } else {
                            alert(res);
                        }
                    });
                }
            });

            $(document).on("change", ".role-select", function() {
                const userId = $(this).data("user-id");
                const newRole = $(this).val();
                $.post("../MyLibrary.php", {
                    change_role: true,
                    user_id: userId,
                    new_role: newRole
                }, function(res) {
                    if (res && res.success) {
                        loadStats();
                    } else if (res && res.message) {
                        alert(res.message);
                        loadUsers();
                    }
                }, "json").fail(function() {
                    loadUsers();
                });
            });

            // === STATION EDIT (inline) ===
            $(document).on("click", ".admin-edit-station-btn", function() {
                const sid = $(this).data("id");
                const currentOwner = $(this).data("owner");
                const $editRow = $("#station-edit-" + sid);

                // Close any other open edit rows
                $(".station-edit-row").not($editRow).hide();

                if ($editRow.is(":visible")) {
                    $editRow.hide();
                    return;
                }

                // Populate owner dropdown from cached user list
                const $ownerSelect = $editRow.find(".station-edit-owner-select");
                $ownerSelect.find("option:not(:first)").remove();
                allUsers.forEach(function(u) {
                    const selected = String(u.UserID) === String(currentOwner) ? " selected" : "";
                    $ownerSelect.append('<option value="' + u.UserID + '"' + selected + '>' + u.Username + ' (' + u.Fullname + ')</option>');
                });
                if (!currentOwner) $ownerSelect.val("");

                $editRow.show();
                $editRow.find(".station-edit-name-input").focus();
            });

            $(document).on("click", ".cancel-station-edit-btn", function() {
                const sid = $(this).data("id");
                $("#station-edit-" + sid).hide();
            });

            $(document).on("click", ".save-station-edit-btn", function() {
                const sid = $(this).data("id");
                const $editRow = $("#station-edit-" + sid);
                const newName = $editRow.find(".station-edit-name-input").val().trim();
                const newOwner = $editRow.find(".station-edit-owner-select").val();
                const $feedback = $("#station-edit-feedback-" + sid);

                if (!newName) {
                    $feedback.text("Station name cannot be empty.").addClass("feedback-error").show();
                    return;
                }

                $.post("../MyLibrary.php", {
                    update_station_admin: true,
                    station_id: sid,
                    station_name: newName,
                    new_owner_id: newOwner
                }, function(res) {
                    if (res.success) {
                        // Update the main row cells in place
                        const $mainRow = $("#station-row-" + sid);
                        $mainRow.find("td:nth-child(2)").text(res.name);
                        const statusHtml = res.status === "assigned" ?
                            '<span class="admin-badge admin-badge-green">Assigned</span>' :
                            '<span class="admin-badge admin-badge-gray">Available</span>';
                        $mainRow.find("td:nth-child(4)").html(statusHtml);
                        $mainRow.find("td:nth-child(5)").text(res.owner);
                        // Update edit button data attributes
                        $mainRow.find(".admin-edit-station-btn")
                            .data("name", res.name)
                            .data("owner", newOwner);
                        $editRow.hide();
                        loadStats();
                    } else {
                        $feedback.text(res.message || "Error saving changes.").addClass("feedback-error").show();
                    }
                }, "json");
            });

            function showFeedback(selector, msg, type) {
                $(selector).text(msg).removeClass("feedback-success feedback-error")
                    .addClass(type === "success" ? "feedback-success" : "feedback-error")
                    .show().delay(4000).fadeOut();
            }
        });
    </script>
</body>

</html>