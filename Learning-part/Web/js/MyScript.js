$(start);

function start() {
  // we can start writing here
  updateNavbarSizeOnScroll();

  $(window).on("scroll", function () {
    updateNavbarSizeOnScroll();
    clearTimeout(timer);
    timer = setTimeout(() => {
      PageScrollDetector();
    }, 100);
  });

  $(window).on("resize", function () {
    updateNavbarSizeOnScroll();
  });

  // Run after all assets/styles are loaded so section offsets are accurate
  $(window).on("load", function () {
    PageScrollDetector();
  });

  highlightActiveNavLink();
  applySavedTheme();
  DisplayStationData();
  loadCollectionLoad();
  startNotificationPolling();

  $("#goToLogin").on("click", function () {
    window.location.href = "./sign_in_up.php";
  });

  /* sign in up overlayout trigger */
  $(".layoutTrigger").click(overlayoutTrigger);

  $("#saveBtn").on("click", saveChanges);
}

/* date formater for database */
function formatThisDate(input) {
  var date = new Date(input.replace("T", " "));

  var formatted =
    date.getFullYear() +
    "-" +
    String(date.getMonth() + 1).padStart(2, "0") +
    "-" +
    String(date.getDate()).padStart(2, "0") +
    " " +
    String(date.getHours()).padStart(2, "0") +
    ":" +
    String(date.getMinutes()).padStart(2, "0") +
    ":00";

  return formatted;
}

/* class toggle function */
function toggleMyClass(classTarget, className) {
  $("." + classTarget).each(function () {
    $(this).toggleClass(className);
  });
}
function Logout() {
  $.post(
    "../MyLibrary.php",
    { logoutBtn: true },
    function (res) {
      window.location.href = res.redirect || "./sign_in_up.php";
    },
    "json",
  );
}

/* Toggles dark mode and saves preference to localStorage */
function toggleDarkMode() {
  const isDark = document.documentElement.getAttribute("data-theme") === "dark";
  if (isDark) {
    document.documentElement.removeAttribute("data-theme");
    localStorage.setItem("theme", "light");
    document.getElementById("darkModeIcon").setAttribute("name", "moon");
    $("#darkModeBtn").attr("title", "Switch to dark mode");
  } else {
    document.documentElement.setAttribute("data-theme", "dark");
    localStorage.setItem("theme", "dark");
    document.getElementById("darkModeIcon").setAttribute("name", "sun");
    $("#darkModeBtn").attr("title", "Switch to light mode");
  }
}

/* Applies saved theme on page load */
function applySavedTheme() {
  if (localStorage.getItem("theme") === "dark") {
    document.documentElement.setAttribute("data-theme", "dark");
    const icon = document.getElementById("darkModeIcon");
    if (icon) {
      icon.setAttribute("name", "sun");
      $("#darkModeBtn").attr("title", "Switch to light mode");
    }
  }
}

function saveChanges() {
  /* only in case two passwords are match we can pass them */
  let pass = $("#passwordInput").val();
  let confirPass = $("#passwordConfirmationInput").val();
  if (pass !== "") {
    if (pass !== confirPass) {
      alert("Passwords do not match");
      return;
    }
  }
  let data = {
    saveButtonClicked: true,
    fullName: $("#fullNameInput").val(),
    userName: $("#usernameInput").val(),
    email: $("#emailInput").val(),
  };
  if (pass !== "") {
    data.pass = pass;
  }
  $.post("../MyLibrary.php", data, function (htmlReply) {
    // we get called here when the request was finished
    alert(htmlReply);
    toggleMyClass("info_row", "editing");
    $("#saveBtn").css("display", "none");
    $("#cancelBtn").css("display", "none");
    location.reload();
  });
}

function enableEditing() {
  // Enter edit mode for all fields
  $("#cancelBtn").css("display", "flex");
  $("#saveBtn").css("display", "flex");
  toggleMyClass("info_row", "editing");
  $("#passConfir").toggle();
}

function cancelEdit() {
  toggleMyClass("info_row", "editing");
  $("#saveBtn").css("display", "none");
  $("#cancelBtn").css("display", "none");
}

let timer;

/* Highlights the navbar link for the currently visible section (index.php scroll) */
function PageScrollDetector() {
  const navHeight = $("nav").outerHeight() || 0;
  let activeId = null;

  $("section[id]").each(function () {
    const rect = this.getBoundingClientRect();
    if (rect.top <= navHeight + 60) {
      activeId = this.id;
    }
  });

  $('nav a[href^="index.php#"]').removeClass("active");
  if (activeId) {
    $('nav a[href="index.php#' + activeId + '"]').addClass("active");
  }
}

/* Highlights the navbar link matching the current page filename */
function highlightActiveNavLink() {
  const page = window.location.pathname.split("/").pop().toLowerCase();

  const pageLinks = {
    "collection.php": "./Collection.php",
    "friendship.php": "./Friendship.php",
    "stationregistration.php": "./StationRegistration.php",
    "admin.php": "./admin.php",
    "sign_in_up.php": "./sign_in_up.php",
  };

  if (pageLinks[page]) {
    $('nav a[href="' + pageLinks[page] + '"]').addClass("active");
  }
}

function updateNavbarSizeOnScroll() {
  const startPercent = 20;
  const endPercent = 15;
  const maxScroll = 1000;
  const currentScroll = Math.max(
    0,
    window.scrollY ||
      window.pageYOffset ||
      document.documentElement.scrollTop ||
      0,
  );

  const progress = Math.min(currentScroll / maxScroll, 1);
  const viewportHeight =
    window.innerHeight || document.documentElement.clientHeight;
  const startPx = (viewportHeight * startPercent) / 100;
  const endPx = (viewportHeight * endPercent) / 100;
  const currentPx = startPx - (startPx - endPx) * progress;

  document.documentElement.style.setProperty(
    "--nav-height",
    `${currentPx.toFixed(2)}px`,
  );
}

/* sign in up overlayout trigger */
function overlayoutTrigger() {
  let currentPosition = parseInt($(".overlayout").css("left"));
  if (currentPosition == 0) {
    $(".overlayout").animate({ left: "+=450px" }, 500);
  } else {
    $(".overlayout").animate({ left: "0px" }, 500);
  }
}

// state managment
/* everytime we want to edit the initial data, we simply change the
value of the field and call function initializeOriginalData() */
function removeFriend(target_user) {
  $.post(
    "../MyLibrary.php",
    { removeFriend: true, target_user: target_user },
    function (friendRemoved) {
      alert(friendRemoved);
      window.location.href = "./Friendship.php";
    },
  );
}
// ====== Create Friend Card Function ======
function createFriendCard(friend, allowMultiSelect = false) {
  let card = $("<div>").addClass("friendCard").data("id", friend.id);

  let defaultProfileImg = "../img/User.png";

  // Selection square for multi-select
  let selectBox = $("<div>").addClass("selectBox");

  let avatar = $("<img>")
    .addClass("friendAvatar")
    .attr("src", friend.image || defaultProfileImg)
    .attr("alt", "Profile");

  let info = $("<div>").addClass("friendInfo");
  let username = $("<span>").addClass("friendUsername").text(friend.username);
  let email = $("<span>").addClass("friendEmail").text(friend.email);

  info.append(username, email);
  const pagePath = decodeURIComponent(window.location.pathname);
  const isCollectionPage = pagePath.endsWith("/Collection.php");
  // Remove friend button
  let removeBtn = $("<button>")
    .addClass("removeFriendBtn")
    .html("&times;")
    .toggle(!isCollectionPage)
    .on("click", function (e) {
      e.stopPropagation(); // Prevent selecting card
      removeFriend(friend.id);
      card.remove();
      updateShareButton(); // update selection count if necessary
    });

  // Multi-select behavior
  if (allowMultiSelect) {
    card.addClass("selectable");
    card.on("click", function () {
      $(this).toggleClass("selected");
      updateShareButton();
    });
  }

  card.append(selectBox, avatar, info, removeBtn);
  return card;
}

function updateShareButton() {
  const selectedCount = $(".friendCard.selected").length;
  $(".confirmShareWithFriendsBtn").prop("disabled", selectedCount === 0);
}

function DisplayFriends(targetCollection) {
  const isSharingMode =
    targetCollection !== undefined &&
    targetCollection !== null &&
    String(targetCollection) !== "" &&
    String(targetCollection) !== "0";

  // Remove previous overlay if exists
  $(".blur-background, .content").remove();

  let blurDiv = $("<div>").addClass("blur-background");
  let sectionContent = $("<section>").addClass("content");

  let exitBtn = $("<button>")
    .text("X")
    .addClass("exitChatBox")
    .on("click", CloseChatBox);

  let friendsList = $("<div>").addClass("friendsList");

  let confirmBtn = $("<button>")
    .text("Share")
    .addClass("confirmShareWithFriendsBtn")
    .prop("disabled", true)
    .on("click", function () {
      if (!isSharingMode) {
        return;
      }

      let selectedIds = [];
      friendsList.find(".friendCard.selected").each(function () {
        selectedIds.push($(this).data("id"));
      });

      if (selectedIds.length === 0) {
        alert("Please select at least one friend");
        return;
      }

      // Use the correct collection ID here
      $.post(
        "../MyLibrary.php",
        {
          shareWith: selectedIds,
          targetCollectionToShare: targetCollection,
        },
        function (res) {
          alert(res);
          CloseChatBox();
        },
      );
    });

  let title = $("<h3>").text("Friend List").addClass("friendListHeading");
  let subtitle = $("<p>")
    .text(
      isSharingMode
        ? "Choose one or more friends to share this collection."
        : "View and manage your current friends.",
    )
    .addClass("overlaySubheading");

  sectionContent.append(exitBtn, title, subtitle);
  if (isSharingMode) {
    sectionContent.append(confirmBtn);
  }
  sectionContent.append(friendsList);
  $("body").append(blurDiv, sectionContent);

  // Fetch friends from backend
  $.post(
    "../MyLibrary.php",
    { showFriends: "true" },
    function (friends) {
      if (!friends || friends.length === 0) {
        friendsList.html("<p>No friends found. Add friends first.</p>");
        return;
      }

      friendsList.empty();
      friends.forEach((friend) => {
        friendsList.append(createFriendCard(friend, isSharingMode));
      });
      if (isSharingMode) {
        updateShareButton();
      }
    },
    "json",
  ).fail(function () {
    friendsList.html("<p>Error loading friends.</p>");
  });

  blurDiv.on("click", CloseChatBox);
}
function CloseChatBox() {
  $(".blur-background").remove();
  $(".content").remove();
}

function DisplayPendingRequests() {
  // Remove previous overlay if exists
  $(".blur-background, .content").remove();

  let blurDiv = $("<div>").addClass("blur-background");
  let sectionContent = $("<section>").addClass("content");

  let exitBtn = $("<button>")
    .text("X")
    .addClass("exitChatBox")
    .on("click", CloseChatBox);

  let title = $("<h3>")
    .text("Friendship Requests")
    .addClass("friendListHeading");
  let subtitle = $("<p>")
    .text("Accept or delete incoming friendship requests.")
    .addClass("overlaySubheading");
  let requestsContainer = $("<div>").addClass("pendingRequestsContainer");

  function removeCardOrShowEmpty(card) {
    card.remove();
    if (requestsContainer.find(".pendingRequestCard").length === 0) {
      requestsContainer.html(
        '<p class="pendingRequestsEmpty">No pending requests right now.</p>',
      );
    }
  }

  function handleRequestAction(action, request, card) {
    const userA_id =
      request.UserA_ID || request.userA_id || request.user_a_id || null;
    const userB_id =
      request.UserB_ID || request.userB_id || request.user_b_id || null;
    const requestId = `${userA_id},${userB_id}`; // Construct a unique ID for the request card

    $.post(
      "../MyLibrary.php",
      {
        friendRequestAction: action,
        requestId: requestId,
      },
      function (serverRespond) {
        // serverRespond is expected to be JSON with either {success: true} or {error: '...'}
        try {
          const resp =
            typeof serverRespond === "object"
              ? serverRespond
              : JSON.parse(serverRespond);
          if (resp && resp.error) {
            alert("Server error: " + resp.error);
          } else {
            // success
            removeCardOrShowEmpty(card);
            window.location.href = "./Friendship.php";
          }
        } catch (e) {
          // non-JSON response: fallback to existing behavior
          removeCardOrShowEmpty(card);
          window.location.href = "./Friendship.php";
        }
      },
    ).fail(function () {
      // Keep optimistic fallback for frontend-only integration.
      removeCardOrShowEmpty(card);
    });
  }

  function createPendingRequestCard(request) {
    const userA_id =
      request.UserA_ID || request.userA_id || request.user_a_id || null;
    const userB_id =
      request.UserB_ID || request.userB_id || request.user_b_id || null;

    const requestId = `${userA_id},${userB_id}`; // Construct a unique ID for the request card

    const username = request.Username || "Unknown user";
    const email = request.Email || "No email provided";
    const profileImg = request.image || "../img/User.png";

    const card = $("<div>")
      .addClass("pendingRequestCard")
      .data("id", requestId);

    const avatar = $("<img>")
      .addClass("friendAvatar")
      .attr("src", profileImg)
      .attr("alt", "Profile");

    const info = $("<div>").addClass("friendInfo");
    info.append(
      $("<span>").addClass("friendUsername").text(username),
      $("<span>").addClass("friendEmail").text(email),
      $("<span>")
        .addClass("pendingRequestMeta")
        .text("Wants to connect with you."),
    );

    const actions = $("<div>").addClass("pendingRequestActions");

    const acceptBtn = $("<button>")
      .addClass("requestAcceptBtn")
      .text("Accept")
      .on("click", function (e) {
        e.stopPropagation();
        handleRequestAction("accept", request, card);
      });

    const deleteBtn = $("<button>")
      .addClass("requestDeleteBtn")
      .text("Delete")
      .on("click", function (e) {
        e.stopPropagation();
        handleRequestAction("delete", request, card);
      });
    actions.append(acceptBtn, deleteBtn);
    card.append(avatar, info, actions);
    return card;
  }

  sectionContent.append(exitBtn, title, subtitle, requestsContainer);
  $("body").append(blurDiv, sectionContent);

  $.post(
    "../MyLibrary.php",
    { getPendingRequests: true },
    function (requests) {
      if (
        !requests ||
        !requests.PendingRequests ||
        requests.PendingRequests.length === 0
      ) {
        requestsContainer.html(
          '<p class="pendingRequestsEmpty">No pending requests right now.</p>',
        );
        return;
      }

      requests.PendingRequests.forEach((request) => {
        requestsContainer.append(createPendingRequestCard(request));
      });
    },
    "json",
  ).fail(function () {
    requestsContainer.html(
      '<p class="pendingRequestsEmpty">Unable to load pending requests.</p>',
    );
  });

  blurDiv.on("click", CloseChatBox);
}

function DisplayStationData() {
  const displayContainer = $(".tempretureDisplay");
  displayContainer.empty();

  // === DROPDOWNS SECTION ===
  const dropdownsSection = $("<div>").addClass("dropdowns-container");

  const stationLabel = $("<label>").text("Select Station").css({
    "font-weight": "600",
    color: "var(--text-dark)",
    "margin-bottom": "0.5rem",
  });
  const selectBarStations = $("<select>").attr("id", "selectStation");
  selectBarStations.append($("<option>").val("0").text("-- All Stations --"));
  const stationGroup = $("<div>")
    .addClass("form-group")
    .append(stationLabel, selectBarStations);

  const collectionLabel = $("<label>").text("Display Collections").css({
    "font-weight": "600",
    color: "var(--text-dark)",
    "margin-bottom": "0.5rem",
  });
  const selectBarCollection = $("<select>")
    .attr("id", "selectBarCollection")
    .attr("aria-label", "Collections (view only)");
  selectBarCollection.append(
    $("<option>").val("0").text("-- Collections (View Only) --"),
  );
  const collectionGroup = $("<div>")
    .addClass("form-group")
    .append(collectionLabel, selectBarCollection);

  dropdownsSection.append(stationGroup, collectionGroup);
  displayContainer.append(dropdownsSection);

  // === CONTROLS SECTION (DateTime Inputs) ===
  const controlsSection = $("<div>").addClass("dashboard-controls");

  const startLabel = $("<label>").text("Start Date & Time").css({
    "font-weight": "600",
    color: "var(--text-dark)",
  });
  const DateAndTimeStart = $("<input>").attr({
    type: "datetime-local",
    id: "meeting-time-start",
    value: "2026-01-01T00:00",
  });
  const startGroup = $("<div>")
    .addClass("form-group")
    .append(startLabel, DateAndTimeStart);

  const endLabel = $("<label>").text("End Date & Time").css({
    "font-weight": "600",
    color: "var(--text-dark)",
  });
  const DateAndTimeEnd = $("<input>").attr({
    type: "datetime-local",
    id: "meeting-time-end",
    value: "2026-12-31T23:59",
  });
  const endGroup = $("<div>")
    .addClass("form-group")
    .append(endLabel, DateAndTimeEnd);

  controlsSection.append(startGroup, endGroup);
  displayContainer.append(controlsSection);

  // === BUTTONS SECTION ===
  const dispalyMeasuBtn = $("<button>")
    .attr("id", "displayDateBtn")
    .addClass("btn btn-save")
    .text("📊 Display Measurements");

  const collectionCreateBtn = $("<button>")
    .attr("id", "createCollectionBtn")
    .addClass("btn btn-approve")
    .prop("disabled", true)
    .text("💾 Create Collection");

  const liveModeBtn = $("<button>")
    .attr("id", "liveModeBtn")
    .addClass("btn btn-info")
    .text("🔴 Enable Live Mode")
    .data("isLive", false);

  const btnContainer = $("<div>")
    .attr("id", "btnContainer")
    .append(dispalyMeasuBtn, collectionCreateBtn, liveModeBtn);

  displayContainer.append(btnContainer);

  // === MEASUREMENTS TABLE ===
  const tableContainer = $("<div>").addClass("displayTable");
  const table = $("<table>").attr("id", "measurementsTable");

  // Create table head
  const thead = $("<thead>");
  const headerRow = $("<tr>");
  const headers = [
    "Timestamp",
    "Humidity",
    "Air Pressure",
    "Light Intensity",
    "Air Quality",
    "Station ID",
  ];

  headers.forEach((header) => {
    headerRow.append($("<th>").text(header));
  });

  thead.append(headerRow);
  table.append(thead);

  // Create table body
  const tbody = $("<tbody>");
  table.append(tbody);

  tableContainer.append(table);
  displayContainer.append(tableContainer);

  // === LOAD DATA ===
  // Load stations
  $.post(
    "../MyLibrary.php",
    { displayStaion: true },
    function (stations) {
      stations.forEach((station) => {
        selectBarStations.append(
          $("<option>").val(station.stationId).text(station.stationName),
        );
      });

      // populate the dashboard metric cards station dropdown with same data
      const $dash = $("#dashboardStationSelect");
      $dash.empty().append($("<option>").val("0").text("-- All Stations --"));
      stations.forEach((station) => {
        $dash.append(
          $("<option>").val(station.stationId).text(station.stationName),
        );
      });
      // default: first real station if available
      if (stations.length > 0) {
        $dash.val(stations[0].stationId);
        loadDashboardMetrics(stations[0].stationId);
      } else {
        loadDashboardMetrics(0);
      }

      const defaultDateStart = $("#meeting-time-start").val();
      const defaultDateEnd = $("#meeting-time-end").val();

      // If no station is selected or "All Stations" is chosen, load all measurements for user-owned stations.
      loadMeasurements(0, defaultDateStart, defaultDateEnd);
    },
    "json",
  );

  // Load collections
  $.post(
    "../MyLibrary.php",
    { displayCollections: true },
    function (Collections) {
      selectBarCollection.empty();
      selectBarCollection.append(
        $("<option>").val("0").text("-- Collections (View Only) --"),
      );

      if (Collections && Collections.length > 0) {
        Collections.forEach((col) => {
          selectBarCollection.append(
            $("<option>").val(col.Collection_id).text(col.Collection_name),
          );
        });
      }
    },
    "json",
  );

  // Station selection change handler (auto-update if in live mode)
  $(document).on("change", "#selectStation", function () {
    const isLive = $("#liveModeBtn").data("isLive");
    const newStationId = $(this).val();
    if (isLive) {
      // If 0 or no station, poll all own stations
      startRealtimeMeasurementPolling(newStationId);
    }
  });

  // Display button click
  $(document).on("click", "#displayDateBtn", function () {
    const stationId = $("#selectStation").val();
    const dateTimeStart = formatThisDate($("#meeting-time-start").val());
    const dateTimeEnd = formatThisDate($("#meeting-time-end").val());

    const start = new Date(dateTimeStart);
    const end = new Date(dateTimeEnd);
    if (start > end) {
      alert("End time cannot be earlier than start time");
      return;
    }

    loadMeasurements(stationId, dateTimeStart, dateTimeEnd);
  });

  // Live Mode button click
  $(document).on("click", "#liveModeBtn", function () {
    const $btn = $(this);
    const isLive = $btn.data("isLive");
    const stationId = $("#selectStation").val();

    if (!isLive) {
      // Enable live mode
      const selectedStationLabel =
        stationId === "0" ? "all stations" : "station " + stationId;

      // Load initial measurements and start polling after load finishes
      const defaultDateStart = $("#meeting-time-start").val();
      const defaultDateEnd = formatThisDate($("#meeting-time-end").val());
      loadMeasurements(
        stationId,
        formatThisDate(defaultDateStart),
        defaultDateEnd,
        function () {
          // Start real-time polling after measurements are rendered
          startRealtimeMeasurementPolling(stationId);
        },
      );

      // Update button appearance
      $btn
        .data("isLive", true)
        .text("🟢 Disable Live Mode")
        .css({ backgroundColor: "#10b981", color: "white" });

      alert("Live mode enabled! New measurements will appear every 1 second.");
    } else {
      // Disable live mode
      stopRealtimeMeasurementPolling();

      // Update button appearance
      $btn
        .data("isLive", false)
        .text("🔴 Enable Live Mode")
        .css({ backgroundColor: "", color: "" });

      alert("Live mode disabled.");
    }
  });

  // Create collection button click
  $(document).on("click", "#createCollectionBtn", function () {
    const measurements = [];
    $("#measurementsTable tbody tr").each(function () {
      const measurementId = $(this).data("measurement-id");
      if (measurementId !== undefined) {
        measurements.push([measurementId]);
      }
    });

    if (measurements.length === 0) {
      alert("No measurements to save!");
      return;
    }

    const collectionName = prompt("Collection name:");
    if (!collectionName || !collectionName.trim()) {
      alert("Error, a collection must have a name");
      return;
    }

    const collectionDescription = prompt("A description:");

    $.post(
      "../MyLibrary.php",
      {
        measurementValues: JSON.stringify(measurements),
        CollecionN: collectionName,
        CollecionD: collectionDescription,
      },
      function (response) {
        alert(response);
        location.reload();
      },
    );
  });
}

let dashboardMetricPollingInterval = null;

function loadDashboardMetrics(stationId) {
  $.post(
    "../MyLibrary.php",
    { getLatestMeasurement: true, stationId: stationId },
    function (response) {
      if (response && response.success) {
        updateMetricCards(response.measurement);
      }
    },
    "json",
  );
}

function updateMetricCards(m) {
  if (!m) return;
  $("#metric-humidity").text(
    m.Humidity !== null ? parseFloat(m.Humidity).toFixed(2) + "%" : "--%",
  );
  $("#metric-pressure").text(
    m.Air_pressure !== null
      ? parseFloat(m.Air_pressure).toFixed(2) + " hPa"
      : "-- hPa",
  );
  $("#metric-light").text(
    m.Light_intensity !== null
      ? parseFloat(m.Light_intensity).toFixed(2) + " lx"
      : "-- lx",
  );
  $("#metric-airquality").text(
    m.Air_quality !== null ? m.Air_quality + " ppm" : "-- ppm",
  );
  $(
    "#metric-humidity-trend, #metric-pressure-trend, #metric-light-trend, #metric-airquality-trend",
  ).text(m.Timestamp ? "Last: " + m.Timestamp : "--");
}

function startDashboardMetricPolling(stationId) {
  stopDashboardMetricPolling();
  loadDashboardMetrics(stationId);
  dashboardMetricPollingInterval = setInterval(function () {
    loadDashboardMetrics(stationId);
  }, 5000);
}

function stopDashboardMetricPolling() {
  if (dashboardMetricPollingInterval) {
    clearInterval(dashboardMetricPollingInterval);
    dashboardMetricPollingInterval = null;
  }
}

$(document).on("change", "#dashboardStationSelect", function () {
  const stationId = $(this).val();
  loadDashboardMetrics(stationId);
  startDashboardMetricPolling(stationId);
});

function loadMeasurements(stationId, start, end, doneCallback) {
  $.post(
    "../MyLibrary.php",
    {
      selectedOption: stationId,
      filterDateStart: start,
      filterDateEnd: end,
    },
    function (measurements) {
      const tbody = $("#measurementsTable tbody");
      tbody.empty();

      const sortedMeasurements = [...measurements].sort(
        (a, b) => new Date(a.Timestamp) - new Date(b.Timestamp),
      );

      sortedMeasurements.forEach((row) => {
        const tr = $("<tr>").data("measurement-id", row.Measurement_id);
        tr.append($("<td>").text(row.Timestamp));
        tr.append($("<td>").text(row.Humidity));
        tr.append($("<td>").text(row.Air_pressure));
        tr.append($("<td>").text(row.Light_intensity));
        tr.append($("<td>").text(row.Air_quality));
        tr.append($("<td>").text(row.Station_id));
        tbody.append(tr);
      });

      $("#createCollectionBtn").prop("disabled", measurements.length === 0);

      // Call optional callback after measurements are loaded
      if (typeof doneCallback === "function") {
        try {
          doneCallback();
        } catch (e) {
          console.error("loadMeasurements(doneCallback) threw:", e);
        }
      }
    },
    "json",
  );
}

// ===== REAL-TIME MEASUREMENT POLLING =====
let realtimePollingInterval = null;
let currentStationForPolling = null;
let lastMeasurementTimestamp = null;

function startRealtimeMeasurementPolling(stationId) {
  // Stop any existing polling
  stopRealtimeMeasurementPolling();

  currentStationForPolling = stationId;

  // Set initial timestamp to now
  // Prefer the timestamp of the last measurement already displayed in the table
  // so we don't miss recent rows loaded just before enabling live mode.
  const lastRowTs = $("#measurementsTable tbody tr:last td:first")
    .text()
    .trim();
  if (lastRowTs) {
    lastMeasurementTimestamp = lastRowTs;
  } else {
    lastMeasurementTimestamp = new Date()
      .toISOString()
      .slice(0, 19)
      .replace("T", " ");
  }

  console.log(
    "[RealtimePolling] startRealtimeMeasurementPolling called for station:",
    currentStationForPolling,
  );
  console.log(
    "[RealtimePolling] initial lastMeasurementTimestamp:",
    lastMeasurementTimestamp,
  );

  // Poll every 1 second for new measurements
  realtimePollingInterval = setInterval(function () {
    console.log(
      "[RealtimePolling] poll tick - asking for measurements since:",
      lastMeasurementTimestamp,
      "for station:",
      currentStationForPolling,
    );
    $.post(
      "../MyLibrary.php",
      {
        getNewMeasurements: true,
        stationId: currentStationForPolling,
        lastTimestamp: lastMeasurementTimestamp,
      },
      function (response) {
        console.log("[RealtimePolling] server response:", response);
        if (
          response &&
          response.success &&
          response.newMeasurements.length > 0
        ) {
          const tbody = $("#measurementsTable tbody");

          // Add new measurements to the table
          response.newMeasurements.forEach((row) => {
            // Avoid duplicating rows that are already present (safe-guard)
            if (
              tbody.find(`tr[data-measurement-id=\"${row.Measurement_id}\"]`)
                .length > 0
            ) {
              return;
            }
            const tr = $("<tr>").data("measurement-id", row.Measurement_id);
            tr.append($("<td>").text(row.Timestamp));
            tr.append($("<td>").text(row.Humidity));
            tr.append($("<td>").text(row.Air_pressure));
            tr.append($("<td>").text(row.Light_intensity));
            tr.append($("<td>").text(row.Air_quality));
            tr.append($("<td>").text(row.Station_id));
            tbody.append(tr);
          });

          // Update timestamp for next poll
          lastMeasurementTimestamp = response.lastTimestamp;

          // Update the dashboard metric cards with the latest measurement
          const latest =
            response.newMeasurements[response.newMeasurements.length - 1];
          updateMetricCards(latest);

          // Enable collection button if we have measurements
          const totalRows = tbody.find("tr").length;
          $("#createCollectionBtn").prop("disabled", totalRows === 0);

          // Auto-scroll to bottom
          $("#measurementsTable")
            .parent()
            .scrollTop($("#measurementsTable").height());
        }
      },
      "json",
    ).fail(function (jqXHR, textStatus, errorThrown) {
      console.log(
        "[RealtimePolling] AJAX fail:",
        textStatus,
        errorThrown,
        jqXHR && jqXHR.responseText,
      );
    });
  }, 1000); // Poll every 1 second
}

function stopRealtimeMeasurementPolling() {
  if (realtimePollingInterval) {
    console.log(
      "[RealtimePolling] stopRealtimeMeasurementPolling called - clearing interval",
    );
    clearInterval(realtimePollingInterval);
    realtimePollingInterval = null;
  }
}
// unassign my station
function removeMyStation(targetStationId) {
  $.post(
    "../MyLibrary.php",
    { targetID: targetStationId },
    function (removeRespond) {
      window.location.href = "./StationRegistration.php";
    },
  );
}

function editStation(stationId) {
  const card = $("#stationCard-" + stationId);
  card.find(".station-name-display, .station-desc-display").hide();
  card.find(".station-edit-form").show();
  card.find(".edit-station-btn").hide();
}

function cancelStationEdit(stationId) {
  const card = $("#stationCard-" + stationId);
  card.find(".station-name-display, .station-desc-display").show();
  card.find(".station-edit-form").hide();
  card.find(".edit-station-btn").show();
}

function saveStationEdit(stationId) {
  const card = $("#stationCard-" + stationId);
  const newName = card.find(".station-edit-name").val().trim();
  const newDesc = card.find(".station-edit-desc").val().trim();
  if (!newName) {
    alert("Station name cannot be empty.");
    return;
  }
  $.post(
    "../MyLibrary.php",
    {
      updateStation: true,
      stationId: stationId,
      stationName: newName,
      stationDesc: newDesc,
    },
    function (response) {
      if (response.success) {
        card.find(".station-name-display").text(newName).show();
        card.find(".station-desc-display").text(newDesc).show();
        card.find(".station-edit-form").hide();
        card.find(".edit-station-btn").show();
      } else {
        alert("Failed to update station.");
      }
    },
    "json",
  );
}
// share this collection (vlaue of btn is the collection id)
$(document).on("click", ".shareCollectionBtn, .share-btn", function () {
  const collectionID = $(this).data("id") || $(this).val();
  if (!collectionID) {
    console.error("No collection ID found");
    return;
  }
  DisplayFriends(collectionID); // Make sure this passes the ID
});

// Contact form submission
$(document).on("submit", "#contactForm", function (e) {
  e.preventDefault();

  const name = $("#contactName").val().trim();
  const email = $("#contactEmail").val().trim();
  const subject = $("#contactSubject").val();
  const message = $("#contactMessage").val().trim();

  if (!name || !email || !subject || !message) {
    alert("Please fill in all fields");
    return;
  }

  // Basic email validation
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email)) {
    alert("Please enter a valid email address");
    return;
  }

  // Simulate form submission (in a real app, this would send to server)
  alert(`Thank you for your message, ${name}! We'll get back to you soon.`);

  // Clear form
  $("#contactForm")[0].reset();
});

/* Collection.php */
function loadCollectionLoad() {
  $(document).ready(function () {
    const $myTab = $(".Collections_container");
    const $sharedTab = $(".Collections_shared_container");
    const $sectionInfo = $("#sectionInfo");

    // Default tab
    switchSection("my");

    // Tab click handlers
    $myTab.on("click", () => switchSection("my"));
    $sharedTab.on("click", () => switchSection("shared"));

    // === MOVE THIS FUNCTION OUTSIDE switchSection ===
    function buildCollectionHTML(collection, cid, isSharedByMe) {
      let html = `
    <div class="collection-block displayTable">
      <div class="collection-header">
        <h2>${collection.Name}</h2>
        ${
          isSharedByMe
            ? `
        <div class="collection-buttons top-actions">
          <button class='cancel-share-btn' value='${cid}'>
            <i class="fas fa-times"></i> Cancel Share
          </button>
        </div>`
            : ""
        }
      </div>
      <p>${collection.Description}</p>

      <table>
        <thead>
          <tr>
            <th>Timestamp</th>
            <th>Humidity</th>
            <th>Air Pressure</th>
            <th>Light Intensity</th>
            <th>Air Quality</th>
          </tr>
        </thead>
        <tbody>
  `;

      collection.Measurements.forEach((m) => {
        html += `
      <tr>
        <td>${m.Timestamp}</td>
        <td>${m.Humidity}</td>
        <td>${m.Air_pressure}</td>
        <td>${m.Light_intensity}</td>
        <td>${m.Air_quality}</td>
      </tr>
    `;
      });

      html += `
        </tbody>
      </table>

      <!-- Buttons outside the table -->
      <div class="collection-buttons">
  `;

      html += `
      </div> <!-- collection-buttons -->
    </div> <!-- collection-block -->
  `;

      return html;
    }
    // === END OF MOVED FUNCTION ===

    function switchSection(section) {
      $myTab.removeClass("active");
      $sharedTab.removeClass("active");
      $sectionInfo.empty();

      if (section === "my") {
        $myTab.addClass("active");

        $.post(
          "../MyLibrary.php",
          { DisplayCollection: true },
          function (collections) {
            if (collections.message) {
              $sectionInfo.html(`<p>${collections.message}</p>`);
              return;
            }

            collections.forEach((collection) => {
              let tableHtml = `
              <div class="collection-block displayTable">
                <div class="collection-header">
                  <h2>${collection.Name}</h2>
                  <div class="collection-buttons top-actions">
                    <button class='share-btn' value='${collection.Collection_id}'>
                      <i class="fas fa-share"></i> Share
                    </button>
                    <button class='remove-btn' value='${collection.Collection_id}'>
                      <i class="fas fa-trash"></i> Remove
                    </button>
                  </div>
                </div>
                <p>${collection.Description}</p>

                <div class="table-container">
                  <table>
                  <thead>
                    <tr>
                      <th>Timestamp</th>
                      <th>Humidity</th>
                      <th>Air pressure</th>
                      <th>Light intensity</th>
                      <th>Air quality</th>
                    </tr>
                  </thead>
                  <tbody>
            `;

              collection.Measurements.forEach((m) => {
                tableHtml += `
                <tr>
                  <td>${m.Timestamp}</td>
                  <td>${m.Humidity}</td>
                  <td>${m.Air_pressure}</td>
                  <td>${m.Light_intensity}</td>
                  <td>${m.Air_quality}</td>
                </tr>
              `;
              });

              tableHtml += `
                  </tbody>
                </table>
                </div>
              </div>
            `;

              $sectionInfo.append(tableHtml);
            });

            reattachEventHandlers();
          },
          "json",
        );
      } else {
        $sharedTab.addClass("active");

        $.post(
          "../MyLibrary.php",
          { FetchSharedCollection: true },
          function (response) {
            try {
              const SharedCollections =
                typeof response === "string" ? JSON.parse(response) : response;

              if (!SharedCollections.success) {
                $sectionInfo.html(
                  "<p>Error loading shared collections: " +
                    SharedCollections.message +
                    "</p>",
                );
                return;
              }

              let html = "";

              // --- Shared With Me ---
              const sharedWithMe = SharedCollections.sharedWithMeCollections;
              if (Object.keys(sharedWithMe).length > 0) {
                html += "<h2>Shared With Me</h2>";
                html += "<p>Collections shared with you by other users.</p>";

                for (const cid in sharedWithMe) {
                  const collection = sharedWithMe[cid];
                  html += buildCollectionHTML(collection, cid, false);
                }
              } else {
                html += "<h2>Shared With Me</h2>";
                html += "<p>No collections have been shared with you yet.</p>";
              }

              // --- Separator ---
              if (
                Object.keys(sharedWithMe).length > 0 &&
                Object.keys(SharedCollections.sharedByMeCollections).length > 0
              ) {
                html += '<hr style="margin:40px 0; border:1px solid #ccc;">';
              }

              // --- Shared By Me ---
              const sharedByMe = SharedCollections.sharedByMeCollections;
              if (Object.keys(sharedByMe).length > 0) {
                html += "<h2>Shared By Me</h2>";
                html += "<p>Collections you have shared with other users.</p>";

                for (const cid in sharedByMe) {
                  const collection = sharedByMe[cid];
                  html += buildCollectionHTML(collection, cid, true);
                }
              } else {
                html += "<h2>Shared By Me</h2>";
                html += "<p>You haven't shared any collections yet.</p>";
              }

              $sectionInfo.html(html);
              reattachEventHandlers();
            } catch (error) {
              $sectionInfo.html("<p>Error parsing server response.</p>");
            }
          },
          "json",
        ).fail(function () {
          $sectionInfo.html("<p>Failed to load shared collections.</p>");
        });
      }
    }

    // --- Attach button event handlers ---
    function reattachEventHandlers() {
      // Share button
      $(".share-btn, .shareCollectionBtn")
        .off("click")
        .on("click", function () {
          const collectionID = $(this).data("id") || $(this).val();
          if (!collectionID || collectionID === "0") {
            alert("Invalid collection selected");
            return;
          }
          console.log("Share collection:", collectionID);
          DisplayFriends(collectionID); // corrected behavior
        });

      // Remove button
      $(".remove-btn, .deleteCollectionBtn")
        .off("click")
        .on("click", function () {
          const collectionID = $(this).data("id") || $(this).val();
          if (!collectionID || collectionID === "0") {
            alert("Invalid collection selected");
            return;
          }
          console.log("Remove collection:", collectionID);
          // Add remove collection logic if needed
          if (
            !confirm(
              "Are you sure you want to remove this collection? This action cannot be undone.",
            )
          ) {
            return;
          }
          $.post(
            "../MyLibrary.php",
            { targetCollectionID: collectionID, removeCollection: true },
            function (res) {
              const response = typeof res === "string" ? JSON.parse(res) : res;
              if (response.success) {
                alert(response.success);
                window.location.href = "./Collection.php";
              } else {
                alert("Failed to delete collection: " + response.error);
              }
            },
          );
        });

      // Cancel Share button
      $(".cancel-share-btn")
        .off("click")
        .on("click", function () {
          const collectionID = $(this).val(); // Use .val() since button has value attribute
          if (!collectionID || collectionID === "0") {
            alert("Invalid collection selected");
            return;
          }

          console.log("Canceling share for collection:", collectionID);

          $.post(
            "../MyLibrary.php",
            { CancelSharedCollection: collectionID },
            function (res) {
              const response = typeof res === "string" ? JSON.parse(res) : res;
              if (response.success) {
                alert("Share canceled successfully!");
                switchSection("shared"); // refresh shared collections
              } else {
                alert("Failed to cancel share: " + response.message);
              }
            },
          );
        });
    }
  });
}
/* ==================== ADMIN DELETE BUTTONS ==================== */

// Handle all delete buttons
$(document).on("click", ".delete-btn", function () {
  var value = $(this).val(); // Gets "user_123" or "station_456"

  if (value.startsWith("user_")) {
    // Delete user
    var userId = value.replace("user_", "");
    if (confirm("Delete this user?")) {
      $.post(
        "../MyLibrary.php",
        {
          delete_user: true,
          user_id: userId,
        },
        function (response) {
          alert(response);
          // Reload users
          $.post("../MyLibrary.php", { get_all_users: true }, function (data) {
            $("#usersList").html(data);
          });
        },
      );
    }
  } else if (value.startsWith("station_")) {
    // Delete station
    var stationId = value.replace("station_", "");
    if (confirm("Delete this station?")) {
      $.post(
        "../MyLibrary.php",
        {
          delete_station: true,
          station_id: stationId,
        },
        function (response) {
          alert(response);
          // Reload stations
          $.post(
            "../MyLibrary.php",
            { get_all_stations: true },
            function (data) {
              $("#stationsList").html(data);
            },
          );
        },
      );
    }
  }
});

/* ==================== GROUP CHAT ==================== */

let groupPollingInterval = null;
let currentGroupLastMessageId = 0;

/**
 * Opens the "My Groups" overlay — lists all groups the user is in,
 * plus a button to create a new group.
 */
function ShowGroupChats() {
  $(".blur-background, .content").remove();

  const blurDiv = $("<div>")
    .addClass("blur-background")
    .on("click", CloseChatBox);
  const section = $("<section>").addClass("content");

  const exitBtn = $("<button>")
    .text("X")
    .addClass("exitChatBox")
    .on("click", CloseChatBox);

  const heading = $("<h2>").text("Group Chats").addClass("messageAllHeading");
  const subHeading = $("<p>")
    .text("Create a group or open an existing one.")
    .addClass("overlaySubheading");

  const createBtn = $("<button>")
    .addClass("btn btn-approve")
    .text("+ Create New Group")
    .css("margin-bottom", "1rem")
    .on("click", function () {
      CloseChatBox();
      CreateGroupChat();
    });

  const groupList = $("<div>").addClass("friendsList").attr("id", "groupList");
  groupList.text("Loading groups...");

  section.append(exitBtn, heading, subHeading, createBtn, groupList);
  $("body").append(blurDiv, section);

  $.post(
    "../MyLibrary.php",
    { getMyGroups: true },
    function (groups) {
      groupList.empty();
      if (!groups || groups.length === 0) {
        groupList.html("<p>You have no groups yet. Create one!</p>");
        return;
      }
      groups.forEach(function (g) {
        const card = $("<div>").addClass("friendCard").css("cursor", "default");
        const nameSpan = $("<span>")
          .addClass("friendUsername")
          .text(g.Group_name);
        const metaSpan = $("<span>")
          .addClass("friendEmail")
          .text(
            "Created by " +
              g.creator_name +
              " · " +
              g.member_count +
              " member(s)",
          );
        const openBtn = $("<button>")
          .addClass("btn btn-save")
          .css({ "margin-left": "auto", "flex-shrink": "0" })
          .text("Open")
          .on("click", function () {
            CloseChatBox();
            OpenGroupChat(g.Group_id, g.Group_name);
          });
        const info = $("<div>")
          .addClass("friendInfo")
          .append(nameSpan, metaSpan);
        card.append(info, openBtn);
        groupList.append(card);
      });
    },
    "json",
  ).fail(function () {
    groupList.html("<p>Error loading groups.</p>");
  });
}

/**
 * Shows a form to create a new group: enter group name + select friends to add.
 */
function CreateGroupChat() {
  $(".blur-background, .content").remove();

  const blurDiv = $("<div>")
    .addClass("blur-background")
    .on("click", CloseChatBox);
  const section = $("<section>").addClass("content");

  const exitBtn = $("<button>")
    .text("X")
    .addClass("exitChatBox")
    .on("click", CloseChatBox);

  const heading = $("<h2>")
    .text("Create Group Chat")
    .addClass("messageAllHeading");
  const subHeading = $("<p>")
    .text("Give your group a name and select friends to add.")
    .addClass("overlaySubheading");

  const nameInput = $("<input>")
    .attr({ type: "text", placeholder: "Group name...", maxlength: "100" })
    .css({
      width: "100%",
      "margin-bottom": "1rem",
      padding: "0.5rem",
      "box-sizing": "border-box",
    });

  const friendsLabel = $("<p>")
    .text("Select friends to add:")
    .css({ "font-weight": "600", "margin-bottom": "0.5rem" });

  const friendsList = $("<div>")
    .addClass("friendsList")
    .attr("id", "createGroupFriendList");
  friendsList.text("Loading friends...");

  const createBtn = $("<button>")
    .addClass("btn btn-approve")
    .text("Create Group")
    .css("margin-top", "1rem")
    .on("click", function () {
      const groupName = nameInput.val().trim();
      if (!groupName) {
        alert("Please enter a group name.");
        return;
      }
      const selectedIds = [];
      friendsList.find(".friendCard.selected").each(function () {
        selectedIds.push($(this).data("id"));
      });
      $.post(
        "../MyLibrary.php",
        { createGroup: true, groupName: groupName, memberIds: selectedIds },
        function (res) {
          if (res && res.success) {
            CloseChatBox();
            OpenGroupChat(res.groupId, res.groupName);
          } else {
            alert(res && res.error ? res.error : "Failed to create group.");
          }
        },
        "json",
      ).fail(function () {
        alert("Request failed.");
      });
    });

  section.append(
    exitBtn,
    heading,
    subHeading,
    nameInput,
    friendsLabel,
    friendsList,
    createBtn,
  );
  $("body").append(blurDiv, section);

  // Load friends as selectable cards
  $.post(
    "../MyLibrary.php",
    { showFriends: "true" },
    function (friends) {
      friendsList.empty();
      if (!friends || friends.length === 0) {
        friendsList.html("<p>No friends found. Add friends first.</p>");
        return;
      }
      friends.forEach(function (f) {
        const card = createFriendCard(f, true);
        // Remove the remove-friend button inside creation context
        card.find(".removeFriendBtn").hide();
        friendsList.append(card);
      });
    },
    "json",
  ).fail(function () {
    friendsList.html("<p>Error loading friends.</p>");
  });
}

/**
 * Opens a group chat window for the given groupId.
 */
function OpenGroupChat(groupId, groupName) {
  $(".blur-background, .content").remove();
  if (groupPollingInterval) {
    clearInterval(groupPollingInterval);
    groupPollingInterval = null;
  }
  currentGroupLastMessageId = 0;

  const blurDiv = $("<div>")
    .addClass("blur-background")
    .on("click", function () {
      if (groupPollingInterval) {
        clearInterval(groupPollingInterval);
        groupPollingInterval = null;
      }
      CloseChatBox();
    });
  const section = $("<section>").addClass("content");

  const exitBtn = $("<button>")
    .text("X")
    .addClass("exitChatBox")
    .on("click", function () {
      if (groupPollingInterval) {
        clearInterval(groupPollingInterval);
        groupPollingInterval = null;
      }
      CloseChatBox();
    });

  const heading = $("<h2>").text(groupName).addClass("messageAllHeading");

  const backBtn = $("<button>")
    .addClass("btn btn-info")
    .text("← Back to Groups")
    .css("margin-bottom", "0.5rem")
    .on("click", function () {
      if (groupPollingInterval) {
        clearInterval(groupPollingInterval);
        groupPollingInterval = null;
      }
      CloseChatBox();
      ShowGroupChats();
    });

  const messageList = $("<div>").addClass("messageList");

  const inputContainer = $("<div>").addClass("inputContainer");
  const composerLabel = $("<span>")
    .text("Send Message")
    .addClass("composerLabel");
  const input = $("<input>").attr({
    type: "text",
    placeholder: "Write a message...",
    maxlength: "255",
  });
  const sendBtn = $("<button>")
    .html("<i class='bx bx-send'></i> Send")
    .on("click", function () {
      const message = input.val().trim();
      if (!message) return;
      $.post(
        "../MyLibrary.php",
        { sendGroupMessage: true, groupId: groupId, content: message },
        function (res) {
          if (res && res.success) {
            // Optimistically render own message immediately; poll will skip it
            // since currentGroupLastMessageId is updated to res.messageId
            appendGroupMessage(messageList, {
              sender_name: window.currentUsername || "You",
              Content: message,
              Sent_at: new Date().toLocaleTimeString([], {
                hour: "2-digit",
                minute: "2-digit",
              }),
              Message_id: res.messageId,
            });
            currentGroupLastMessageId = Math.max(
              currentGroupLastMessageId,
              res.messageId,
            );
            messageList.scrollTop(messageList[0].scrollHeight);
          } else {
            alert(res && res.error ? res.error : "Failed to send message.");
          }
        },
        "json",
      ).fail(function () {
        alert("Request failed.");
      });
      input.val("");
    });

  // Allow Enter key to send
  input.on("keypress", function (e) {
    if (e.which === 13) sendBtn.trigger("click");
  });

  inputContainer.append(composerLabel, input, sendBtn);
  section.append(exitBtn, heading, backBtn, messageList, inputContainer);
  $("body").append(blurDiv, section);

  // Load all existing messages first
  $.post(
    "../MyLibrary.php",
    { getGroupMessages: true, groupId: groupId, afterId: 0 },
    function (res) {
      if (res && res.success) {
        res.messages.forEach(function (msg) {
          appendGroupMessage(messageList, msg);
          currentGroupLastMessageId = Math.max(
            currentGroupLastMessageId,
            parseInt(msg.Message_id),
          );
        });
        messageList.scrollTop(messageList[0].scrollHeight);
      }
    },
    "json",
  );

  // Poll for new messages every second
  groupPollingInterval = setInterval(function () {
    $.post(
      "../MyLibrary.php",
      {
        getGroupMessages: true,
        groupId: groupId,
        afterId: currentGroupLastMessageId,
      },
      function (res) {
        if (res && res.success && res.messages.length > 0) {
          res.messages.forEach(function (msg) {
            appendGroupMessage(messageList, msg);
            currentGroupLastMessageId = Math.max(
              currentGroupLastMessageId,
              parseInt(msg.Message_id),
            );
          });
          messageList.scrollTop(messageList[0].scrollHeight);
        }
      },
      "json",
    );
  }, 1500);
}

function appendGroupMessage(messageList, msg) {
  const isMine =
    typeof window.currentUsername !== "undefined" &&
    msg.sender_name === window.currentUsername;

  const container = $("<div>").addClass(
    isMine
      ? "sent_message_container sent"
      : "received_message_container received",
  );
  const profileImg = $("<img>")
    .attr("src", "../img/User.png")
    .addClass("profileImg");
  const contentHolder = $("<div>").addClass("message_content_holder");
  contentHolder.append(
    $("<div>").addClass("username_holder").text(msg.sender_name),
    $("<div>").text(msg.Content),
    $("<div>").addClass("message_timestamp").text(msg.Sent_at),
  );
  container.append(profileImg, contentHolder);
  messageList.append(container);
}

/* ==================== NOTIFICATIONS ==================== */

function startNotificationPolling() {
  pollNotifications();
  setInterval(pollNotifications, 20000);
}

function pollNotifications() {
  $.post(
    "../MyLibrary.php",
    { getNotifCounts: true },
    function (data) {
      updateNotifBadge("#friendsNotifBadge", data.friend_request);
      updateNotifBadge("#collectionNotifBadge", data.collection_share);
    },
    "json",
  );
}

function updateNotifBadge(selector, count) {
  const badge = $(selector);
  if (!badge.length) return;
  if (count > 0) {
    badge.show();
  } else {
    badge.hide();
  }
}

$(document).on("click", "#navFriendsLink", function () {
  $.post("../MyLibrary.php", {
    markNotifRead: true,
    notifType: "friend_request",
  });
  $("#friendsNotifBadge").hide();
});

$(document).on("click", "#navCollectionLink", function () {
  $.post("../MyLibrary.php", {
    markNotifRead: true,
    notifType: "collection_share",
  });
  $("#collectionNotifBadge").hide();
});
