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
function CloseChatBox() {
  /* close the chatbox */
  /* $(".blur-background").hide();
  $(".content").hide(); */
  $(".blur-background").remove();
  $(".content").remove();
}

function extractMeasurements() {}

function DisplayStationData() {
  let displayContainer = $(".tempretureDisplay");

  let selectBar = $("<select>").attr("id", "selectStation");
  let optionDefault = $("<option>").attr("value", "0").text("--choose--");
  selectBar.append(optionDefault);
  displayContainer.append(selectBar);

  let assingedStation = {
    displayStaion: true,
  };
  $.post(
    "../MyLibrary.php",
    assingedStation,
    function (replay) {
      replay.forEach((row) => {
        let option = $("<option>")
          .attr("value", row.stationId)
          .text(row.stationName);
        selectBar.append(option);
      });

      let tableMeasurement = $("<table>");
      let tableRows = [
        "Measurement id",
        "Timestamp",
        "Humidity",
        "Air pressure",
        "Light intensity",
        "Air quality",
        "Station id",
        "Collection id",
      ];
      let tr = $("<tr>");
      tableRows.forEach((tableRow) => {
        tr.append($("<th>").text(tableRow));
        tableMeasurement.append(tr);
      });

      /* let extractedData = extractMeasurements(); */

      let dev = $("<dev>").addClass("displayTable");
      displayContainer.append(selectBar).append(dev);
      displayContainer.append(tableMeasurement);
    },
    "json",
  );
}
