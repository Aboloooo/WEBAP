$(start);

function start() {
  // we can start writing here
  $(window).on("scroll", function () {
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
    history.go(0);
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
function createFriendCard(friend) {
  let card = $("<div>").addClass("friendCard");
  let defaultProfileImg = "../img/User.png";

  let avatar = $("<img>")
    .addClass("friendAvatar")
    .attr("src", friend.image || defaultProfileImg)
    .attr("alt", "Profile");

  let info = $("<div>").addClass("friendInfo");
  let username = $("<span>").addClass("friendUsername").text(friend.username);

  let email = $("<span>").addClass("friendEmail").text(friend.email);

  info.append(username, email);

  let removeBtn = $("<button>")
    .addClass("removeFriendBtn")
    .html("&times;")
    .on("click", function (e) {
      e.stopPropagation(); // prevent closing modal
      removeFriend(friend.id);
      card.remove();
    });

  card.append(avatar, info, removeBtn);
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
  - check if user is login 
  - for each new message there must be a notification in message all option 
  */
}
function DisplayFriends() {
  let blurDiv = $("<div>").addClass("blur-background");
  let sectionContent = $("<section>").addClass("content");

  let exitBtn = $("<button>")
    .text("X")
    .addClass("exitChatBox")
    .on("click", CloseChatBox);

  sectionContent.append(exitBtn);
  // Example friend data (replace with AJAX)
  /* let friends = [
    {
      id: 1,
      username: "john_doe",
      email: "john@example.com",
      image: null,
    },
    {
      id: 2,
      username: "jane_smith",
      email: "jane@example.com",
      image: null,
    },
  ]; */
  console.log("btn friends clicked");

  $.post(
    "../MyLibrary.php",
    { showFriends: "true" },
    function (friends) {
      console.log(friends); // should log an array
      friends.forEach((friend) => {
        sectionContent.append(createFriendCard(friend));
      });
    },
    "json", // <-- important
  );

  blurDiv.on("click", CloseChatBox);

  $("body").append(blurDiv, sectionContent);
}

function CloseChatBox() {
  /* close the chatbox */
  /* $(".blur-background").hide();
  $(".content").hide(); */
  $(".blur-background").remove();
  $(".content").remove();
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
      console.log(measurements);

      $("#measurementsTable tbody").remove();
      let tbody = $("<tbody>").addClass("");

      measurements.forEach((row) => {
        let tr = $("<tr>");
        tr.append($("<td>").text(row.Measurement_id));
        tr.append($("<td>").text(row.Timestamp));
        tr.append($("<td>").text(row.Humidity));
        tr.append($("<td>").text(row.Air_pressure));
        tr.append($("<td>").text(row.Light_intensity));
        tr.append($("<td>").text(row.Air_quality));
        tr.append($("<td>").text(row.Station_id));
        tbody.append(tr);
      });

      $("#measurementsTable").append(tbody);
      $("#createCollectionBtn").prop("disabled", measurements.length === 0);
    },
    "json",
  );
}

function DisplayStationData() {
  const displayContainer = $(".tempretureDisplay");
  displayContainer.empty(); // Clear previous content

  // ===== Date & Time Input =====
  const DateAndTimeStart = $("<input>")
    .attr("type", "datetime-local")
    .attr("name", "meeting-time")
    .attr("id", "meeting-time-start")
    .attr("value", "2026-01-01T00:00")
    .attr("min", "2026-01-01T00:00")
    .attr("max", "2026-12-31T00:00");
  const DateAndTimeEnd = $("<input>")
    .attr("type", "datetime-local")
    .attr("name", "meeting-time")
    .attr("id", "meeting-time-end")
    .attr("value", "2026-12-31T23:59")
    .attr("min", "2026-01-01T00:00")
    .attr("max", "2026-12-31T23:59");

  // ===== Filter Button =====
  const dispalyMeasuBtn = $("<button>")
    .attr("id", "displayDateBtn")
    .attr("type", "button")
    .addClass("btn btn-save")
    .text("Display Measurements");

  // ===== Create Collection Button =====
  const collectionCreateBtn = $("<button>")
    .attr("id", "createCollectionBtn")
    .attr("type", "button")
    .addClass("btn btn-approve collectionBtn")
    .prop("disabled", true) // Disabled by default
    .text("Create Collection");

  // ===== Controller Container =====
  const btnContainer = $("<div>")
    .attr("id", "btnContainer")
    .append(dispalyMeasuBtn, collectionCreateBtn);

  // ===== Select Dropdown for Stations =====
  const selectBarStations = $("<select>").attr("id", "selectStation");
  const optionDefaultStations = $("<option>")
    .attr("value", "0")
    .text("-- Stations --");
  selectBarStations.append(optionDefaultStations);

  // ===== Select Dropdown for Collection =====
  const selectBarCollection = $("<select>").attr("id", "selectBarCollection");
  const optionDefaultCollecton = $("<option>")
    .attr("value", "0")
    .text("-- Collections --");
  selectBarCollection.append(optionDefaultCollecton);

  // Append controls to display container
  displayContainer.append(
    selectBarStations,
    DateAndTimeStart,
    DateAndTimeEnd,
    selectBarCollection,
    btnContainer,
  );

  // ===== Table Container =====
  const tableContainer = $("<div>").addClass("displayTable");
  const tableMeasurement = $("<table>").attr("id", "measurementsTable");
  const tableHeader = $("<tr>");
  const tableRows = [
    "Measurement id",
    "Timestamp",
    "Humidity",
    "Air pressure",
    "Light intensity",
    "Air quality",
    "Station id",
  ];
  tableRows.forEach((header) => tableHeader.append($("<th>").text(header)));
  tableMeasurement.append(tableHeader);
  tableContainer.append(tableMeasurement);
  displayContainer.append(tableContainer);

  // ===== Fetch Stations from Backend =====
  $.post(
    "../MyLibrary.php",
    { displayStaion: true },
    function (stations) {
      stations.forEach((station) => {
        selectBarStations.append(
          $("<option>").val(station.stationId).text(station.stationName),
        );
      });

      const defaultStationChose = stations.length ? stations[0].stationId : "0";
      const defaultDateStart = $("#meeting-time-start").val();
      const defaultDateEnd = $("#meeting-time-end").val();

      loadMeasurements(defaultStationChose, defaultDateStart, defaultDateEnd);
    },
    "json",
  ).fail(function (jqXHR, textStatus, errorThrown) {
    console.error("Failed to load stations:", textStatus, errorThrown);
  });
  // ===== Fetch Collections from Backend =====
  $.post(
    "../MyLibrary.php",
    { displayCollections: true },
    function (Collections) {
      selectBarCollection.empty(); // reset dropdown

      if (!Collections || Collections.length === 0) {
        selectBarCollection.append(
          $("<option>").val("").text("No collections available"),
        );
        return;
      } else {
        selectBarCollection.append(
          $("<option>").val("0").text("-- Collections --"),
        );
      }

      Collections.forEach((Collection) => {
        selectBarCollection.append(
          $("<option>")
            .val(Collection.Collection_id)
            .text(Collection.Collection_name),
        );
      });
    },
    "json",
  ).fail(function (jqXHR, textStatus, errorThrown) {
    console.error("Failed to load Collections:", textStatus, errorThrown);
  });

  // ===== Display Button Click Event =====
  $(document)
    .off("click", "#displayDateBtn")
    .on("click", "#displayDateBtn", function (e) {
      e.preventDefault();

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

  // ===== Create Collection Button Click Event =====
  $(document)
    .off("click", "#createCollectionBtn")
    .on("click", "#createCollectionBtn", function () {
      // Collect displayed measurements
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

      // TODO: send measurements to backend for creating a collection
      console.log("Create collection with data:", measurements);
      let collectionName = prompt("Collection name: ");
      let collectionDescription = prompt("A description: ");
      if (!collectionName || !collectionName.trim()) {
        alert("Error, a collection must have a name");
        return;
      }

      console.log(collectionName);
      // if you want to send a array it must be properly formatted
      ($.post(
        "../MyLibrary.php",
        {
          measurementValues: JSON.stringify(measurements),
          CollecionN: collectionName,
          CollecionD: collectionDescription,
          ActiveNav: true,
        },
        function (serverAnswer) {
          console.log(serverAnswer);
          location.reload();
        },
      ),
        "json");
    });
}
// unassign my station
function removeMyStation(targetStationId) {
  $.post(
    "..//MyLibrary.php",
    { targetID: targetStationId },
    function (removeRespond) {
      console.log(removeRespond);
      window.location.href = "./StationRegistration.php";
    },
  );
}
/* share this collection (vlaue of btn is the collection id)*/
$(document).on("click", ".shareCollectionBtn", function () {
  const btnValue = $(this).val();
  alert(btnValue);
});

/* Collection.php */
function loadCollectionLoad() {
  // Collections switcher functionality
  $(document).ready(function () {
    // Cache elements for better performance
    const $myTab = $(".Collections_container");
    const $sharedTab = $(".Collections_shared_container");
    const $sectionInfo = $("#sectionInfo");
    switchSection("my"); // default section to display
    // Tab click handlers - using your pattern
    $myTab.on("click", function () {
      switchSection("my");
    });

    $sharedTab.on("click", function () {
      switchSection("shared");
    });

    // Function to switch sections - similar to your pattern
    function switchSection(section) {
      // Remove active class from all tabs
      $myTab.removeClass("active");
      $sharedTab.removeClass("active");

      if (section === "my") {
        $myTab.addClass("active");
        /* check if there is collection owned by current user */
        $sectionInfo.html(`
                          <p>Here you can view and manage all your personal collections. Add new items, edit existing ones, or explore your past collections.</p>

                          <table id="measurementsTable" class="collectionTable">
                            <tr>
                              <th>collection name</th>
                            </tr>
                            <tr>
                              <th>collection desc</th>
                            </tr>
                            <tr>
                              <td>Measurement</td>
                              <td>Timestamp</td>
                              <td>Humidity</td>
                              <td>Air pressure</td>
                              <td>Light intensity</td>
                              <td>Air quality</td>
                              <td>Station id</td>
                              <td>etc</td>
                            </tr>
                          </table>
                    `);
        $.post(
          "..//MyLibrary.php",
          { DisplayCollection: true },
          function (collections) {
            // clear previous content
            $sectionInfo.empty();

            // if no collections
            if (collections.message) {
              $sectionInfo.html(`<p>${collections.message}</p>`);
              return;
            }

            // loop through collections
            collections.forEach((collection) => {
              // collection header
              let tableHtml = `
        <div class="collection-block displayTable">
          <h2>${collection.Name}</h2>
          <p>${collection.Description}</p>

          <table id="measurementsTable">
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
            <button class = 'shareCollectionBtn' value=${collection.Collection_id}>Share</button>
            <button class = 'deleteCollectionBtn' value=${collection.Collection_id}>Remove</button>
      `;

              // rows
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
      `;

              $sectionInfo.append(tableHtml);
            });
          },
          "json",
        );
      } else {
        $sharedTab.addClass("active");
        $sectionInfo.html(`
                        <h2>Shared Collections</h2>
                        <p>This section shows collections shared with you by other users. You can view, comment, or collaborate with others.</p>
                        
                        <ul class="collections-list">
                            <li>View collections shared with you</li>
                            <li>Collaborate with other users</li>
                            <li>See who shared each collection</li>
                            <li>Track changes and updates</li>
                        </ul>
                        
                        <div class="collection-actions">
                            <button class="collection-btn btn-save" id="createCollectionBtn">
                                <i class="fas fa-plus"></i> Create Shared
                            </button>
                            <button class="collection-btn btn-approve" id="viewCollectionsBtn">
                                <i class="fas fa-users"></i> View Shared
                            </button>
                            <button class="collection-btn btn-cancel" id="exportBtn">
                                <i class="fas fa-share-alt"></i> Share More
                            </button>
                        </div>
                    `);
      }

      // Re-attach event handlers to newly created buttons
      reattachEventHandlers();
    }

    // Function to reattach event handlers after content changes
    function reattachEventHandlers() {}

    // If you want to integrate with your existing start() function
    function initializeCollections() {
      // This would be called from your start() function
      console.log("Collections page initialized");

      // You could load collections data here
      // $.post("../MyLibrary.php", { displayCollections: true }, function(data) {
      //     // Process and display collections
      // }, "json");
    }
  });
}
