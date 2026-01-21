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

function createFriendCard(friend) {
  let card = $("<div>").addClass("friendCard");

  let avatar = $("<img>")
    .addClass("friendAvatar")
    .attr("src", friend.image || "default-profile.png")
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
  /*   let friends = [
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

  ($.post("../MyLibrary.php"),
    { showFriends: true },
    function (friends) {
      console.log(friends);
      /*  friends.forEach((friend) => {
        sectionContent.append(createFriendCard(friend));
      }); */
    });

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
      $("#measurementsTable tbody").remove();
      let tbody = $("<tbody>");

      measurements.forEach((row) => {
        let tr = $("<tr>");
        tr.append($("<td>").text(row.Measurement_id));
        tr.append($("<td>").text(row.Timestamp));
        tr.append($("<td>").text(row.Humidity));
        tr.append($("<td>").text(row.Air_pressure));
        tr.append($("<td>").text(row.Light_intensity));
        tr.append($("<td>").text(row.Air_quality));
        tr.append($("<td>").text(row.Station_id));
        tr.append($("<td>").text(row.Collection_id));
        tbody.append(tr);
      });

      $("#measurementsTable").append(tbody);
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
    .addClass("btn btn-approve")
    .prop("disabled", true) // Disabled by default
    .text("Create Collection");

  // ===== Select Dropdown for Stations =====
  const selectBar = $("<select>").attr("id", "selectStation");
  const optionDefault = $("<option>").attr("value", "0").text("-- choose --");
  selectBar.append(optionDefault);

  // Append controls to display container
  displayContainer.append(
    selectBar,
    DateAndTimeStart,
    DateAndTimeEnd,
    dispalyMeasuBtn,
    collectionCreateBtn,
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
    "Collection id",
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
        selectBar.append(
          $("<option>").val(station.stationId).text(station.stationName),
        );
      });

      const defaultStationChose = stations.length ? stations[0].stationId : "0";
      const defaultDate = $("#meeting-time").val();

      loadMeasurements(defaultStationChose, defaultDate);
    },
    "json",
  ).fail(function (jqXHR, textStatus, errorThrown) {
    console.error("Failed to load stations:", textStatus, errorThrown);
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
      const stationId = $("#selectStation").val();
      const dateTime = $("#meeting-time").val();

      if (stationId === "0") {
        alert("Please select a station before creating a collection");
        return;
      }

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

      alert("Collection creation triggered!"); // placeholder
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
