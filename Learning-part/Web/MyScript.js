$(start);

function start() {
  // we can start writing here
  initScrollToTopBubble();

  $(window).on("scroll", function () {
    toggleScrollToTopBubble();
    clearTimeout(timer);
    timer = setTimeout(() => {
      PageScrollDetector(); // call the function each time user scrolls
    }, 100); // debounce delay
  });

  PageScrollDetector();
  DisplayStationData();
  loadCollectionLoad();

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
  let data = {
    logoutBtn: true,
  };
  $.post("../MyLibrary.php", data, function () {
    location.reload(); // Changed from history.go(0)
  });
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
function initScrollToTopBubble() {
  if ($("#scrollTopBubble").length === 0) {
    const bubble = $("<button>")
      .attr("id", "scrollTopBubble")
      .attr("type", "button")
      .attr("aria-label", "Back to top")
      .html("&#8593;")
      .on("click", function () {
        window.scrollTo({ top: 0, behavior: "smooth" });
      });

    $("body").append(bubble);
  }

  toggleScrollToTopBubble();
}

function toggleScrollToTopBubble() {
  const currentScroll = Math.max(
    0,
    window.pageYOffset || document.documentElement.scrollTop || 0,
  );
  $("#scrollTopBubble").toggleClass("visible", currentScroll > 220);
}

function PageScrollDetector() {
  const scrollPosition = $(this).scrollTop();
  $("section").each(function () {
    let sectionId = $(this).attr("id");
    const sectionTop = $(this).offset().top;
    const sectionHeight = $(this).outerHeight();
    if (
      scrollPosition >= sectionTop - sectionHeight / 3 &&
      scrollPosition < sectionTop + sectionHeight - sectionHeight / 3
    ) {
      if (sectionId) {
        $('nav a[href="index.php#' + sectionId + '"]').addClass("active");
      }
    } else {
      $('nav a[href="index.php#' + sectionId + '"]').removeClass("active");
    }
  });
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
  console.log(target_user);
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

function MessageAll() {
  let blurDiv = $("<div>").attr("class", "blur-background");
  let sectionContent = $("<section>").attr("class", "content");
  let exitBtn = $("<button>")
    .on("click", CloseChatBox)
    .text("X")
    .addClass("exitChatBox");
  sectionContent.append(exitBtn);

  blurDiv.on("click", CloseChatBox);

  $("body").append(blurDiv).append(sectionContent);
  /* 
  - add message list
  */
  let container = $("<div>").addClass("messageContainer");
  let heading = $("<h2>").text("Messaging List").addClass("messageAllHeading");
  let subHeading = $("<p>")
    .text("Send and receive quick updates with your friends.")
    .addClass("overlaySubheading");
  container.append(heading, subHeading);
  let messageList = $("<div>").addClass("messageList");
  let input_container = $("<div>").addClass("inputContainer");
  let composerLabel = $("<span>")
    .text("Send Message")
    .addClass("composerLabel");
  let input = $("<input>")
    .attr("type", "text")
    .attr("placeholder", "Write a message...")
    .attr("maxlength", "255");
  let sendBtn = $("<button>")
    .html("<i class='bx bx-send'></i> Send")
    .on("click", function () {
      let message = input.val().trim();
      if (!message) {
        return;
      }
      let profileImg = "../img/User.png";
      // Create message content holder with username
      let message_content_holder = $("<div>").addClass(
        "message_content_holder",
      );

      // Add timestamp
      let now = new Date();
      let timeString =
        now.getHours().toString().padStart(2, "0") +
        ":" +
        now.getMinutes().toString().padStart(2, "0");
      message_content_holder.append(
        $("<div>").addClass("message_timestamp").text(timeString),
      );
      // sending message to backend (expect JSON response)
      $.post(
        "../MyLibrary.php",
        { sendMessage: true, message: message, timestamp: timeString },
        function (response) {
          // response is parsed JSON because we request json below
          if (response && response.success) {
            message_content_holder.append(
              $("<div>").addClass("username_holder").text("You"),
            );
            message_content_holder.append($("<div>").text(message));
          } else {
            const err =
              response && response.error
                ? response.error
                : "Error sending message";
            alert(err);
          }
        },
        "json",
      ).fail(function (post, textStatus, error) {
        alert("Request failed: " + textStatus + (error ? " - " + error : ""));
      });

      let message_container = $("<div>").addClass(
        "sent_message_container sent",
      );
      message_container
        .prepend($("<img>").attr("src", profileImg).addClass("profileImg"))
        .append(message_content_holder);

      messageList.append(message_container);

      // Auto-scroll to the latest message
      messageList.scrollTop(messageList[0].scrollHeight);

      input.val(""); // Clear input after sending
    });
  // ask server frequently for new messages
  setInterval(function () {
    $.post(
      "../MyLibrary.php",
      { getNewMessages: true },
      function (response) {
        if (response && response.success) {
          // Append new messages to the message list
          response.messages.forEach(function (message) {
            let message_container = $("<div>").addClass(
              "received_message_container received",
            );
            message_container
              .prepend(
                $("<img>").attr("src", profileImg).addClass("profileImg"),
              )
              .append(
                $("<div>").addClass("message_content").text(message.content),
              )
              .append(
                $("<div>")
                  .addClass("message_timestamp")
                  .text(message.timestamp),
              );

            messageList.append(message_container);
          });
        }
      },
      "json",
    ).fail(function (post, textStatus, error) {
      alert("Request failed: " + textStatus + (error ? " - " + error : ""));
    });
  }, 1000); // Check for new messages every 1 second

  input_container.append(composerLabel, input, sendBtn);
  container.append(messageList, input_container);
  sectionContent.append(container);
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

  if (isSharingMode) {
    console.log("Sharing collection ID:", targetCollection);
  }

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
$(document).on("click", ".shareCollectionBtn, .share-btn", function () {
  const collectionID = $(this).data("id") || $(this).val();
  DisplayFriends(collectionID); // pass it to your existing function
});
function CloseChatBox() {
  /* close the chatbox */
  /* $(".blur-background").hide();
  $(".content").hide(); */
  $(".blur-background").remove();
  $(".content").remove();
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
      const logStation = newStationId === "0" ? "all stations" : newStationId;
      console.log("Live mode switched to station: " + logStation);
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

      // Load initial measurements and start polling
      const defaultDateStart = $("#meeting-time-start").val();
      const defaultDateEnd = formatThisDate($("#meeting-time-end").val());
      loadMeasurements(
        stationId,
        formatThisDate(defaultDateStart),
        defaultDateEnd,
      );

      // Start real-time polling
      startRealtimeMeasurementPolling(stationId);

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
      const rowData = $(this)
        .find("td")
        .map(function () {
          return $(this).text();
        })
        .get();
      measurements.push(rowData);
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

function loadMeasurements(stationId, start, end) {
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

      measurements.forEach((row) => {
        const tr = $("<tr>");
        tr.append($("<td>").text(row.Timestamp));
        tr.append($("<td>").text(row.Humidity));
        tr.append($("<td>").text(row.Air_pressure));
        tr.append($("<td>").text(row.Light_intensity));
        tr.append($("<td>").text(row.Air_quality));
        tr.append($("<td>").text(row.Station_id));
        tbody.append(tr);
      });

      $("#createCollectionBtn").prop("disabled", measurements.length === 0);
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
  lastMeasurementTimestamp = new Date()
    .toISOString()
    .slice(0, 19)
    .replace("T", " ");

  // Poll every 1 second for new measurements
  realtimePollingInterval = setInterval(function () {
    $.post(
      "../MyLibrary.php",
      {
        getNewMeasurements: true,
        stationId: currentStationForPolling,
        lastTimestamp: lastMeasurementTimestamp,
      },
      function (response) {
        if (
          response &&
          response.success &&
          response.newMeasurements.length > 0
        ) {
          const tbody = $("#measurementsTable tbody");

          // Add new measurements to the table
          response.newMeasurements.forEach((row) => {
            const tr = $("<tr>");
            tr.append($("<td>").text(row.Measurement_id));
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
    );
  }, 1000); // Poll every 1 second
}

function stopRealtimeMeasurementPolling() {
  if (realtimePollingInterval) {
    clearInterval(realtimePollingInterval);
    realtimePollingInterval = null;
  }
}
// unassign my station
function removeMyStation(targetStationId) {
  $.post(
    "../MyLibrary.php", // Fixed: removed double slash
    { targetID: targetStationId },
    function (removeRespond) {
      console.log(removeRespond);
      window.location.href = "./StationRegistration.php";
    },
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
      <h2>${collection.Name}</h2>
      <p>${collection.Description}</p>

      <table>
        <thead>
          <tr>
            <th>Measurement ID</th>
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

      if (isSharedByMe) {
        html += `<button class='cancel-share-btn' value='${cid}'>
          <i class="fas fa-times"></i> Cancel Share
        </button>`;
      }

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
                      <th>Measurement ID</th>
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
                  <td>${m.Measurement_id}</td>
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

        console.log("DEBUG: Fetching shared collections...");

        $.post(
          "../MyLibrary.php",
          { FetchSharedCollection: true },
          function (response) {
            console.log("DEBUG: Raw response from server:", response);

            try {
              const SharedCollections =
                typeof response === "string" ? JSON.parse(response) : response;
              console.log("DEBUG: Parsed response:", SharedCollections);

              if (!SharedCollections.success) {
                console.error(
                  "DEBUG: Server returned error:",
                  SharedCollections.message,
                );
                $sectionInfo.html(
                  "<p>Error loading shared collections: " +
                    SharedCollections.message +
                    "</p>",
                );
                return;
              }

              let html = "";

              // Check what we received
              console.log(
                "DEBUG: sharedWithMe keys:",
                Object.keys(SharedCollections.sharedWithMeCollections),
              );
              console.log(
                "DEBUG: sharedByMe keys:",
                Object.keys(SharedCollections.sharedByMeCollections),
              );

              // --- Shared With Me ---
              const sharedWithMe = SharedCollections.sharedWithMeCollections;
              if (Object.keys(sharedWithMe).length > 0) {
                html += "<h2>Shared With Me</h2>";
                html += "<p>Collections shared with you by other users.</p>";

                console.log(
                  "DEBUG: Found " +
                    Object.keys(sharedWithMe).length +
                    " collections shared WITH me",
                );

                for (const cid in sharedWithMe) {
                  const collection = sharedWithMe[cid];
                  console.log(
                    "DEBUG: Processing collection shared WITH me:",
                    cid,
                    collection.Name,
                  );
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

                console.log(
                  "DEBUG: Found " +
                    Object.keys(sharedByMe).length +
                    " collections shared BY me",
                );

                for (const cid in sharedByMe) {
                  const collection = sharedByMe[cid];
                  console.log(
                    "DEBUG: Processing collection shared BY me:",
                    cid,
                    collection.Name,
                  );
                  html += buildCollectionHTML(collection, cid, true);
                }
              } else {
                html += "<h2>Shared By Me</h2>";
                html += "<p>You haven't shared any collections yet.</p>";
              }

              $sectionInfo.html(html);
              reattachEventHandlers();
            } catch (error) {
              console.error("DEBUG: JSON parse error:", error);
              console.error("DEBUG: Response was:", response);
              $sectionInfo.html(
                "<p>Error parsing server response. Check console for details.</p>",
              );
            }
          },
          "json",
        ).fail(function (jqXHR, textStatus, errorThrown) {
          console.error("DEBUG: AJAX request failed:", textStatus, errorThrown);
          $sectionInfo.html(
            "<p>Failed to load shared collections. Check console.</p>",
          );
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
