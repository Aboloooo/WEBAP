$(start);

function start() {
  data = {
    pageLoadedAll: true,
  };
  $.post(
    "./backEnd.php",
    data,
    function (reply) {
      reply.forEach((row) => {
        $userID = row.userID;
        $username = row.username;
        let newOption = $("<option>").val($userID).text($username);
        let selectList = $("#Users");
        selectList.append(newOption);
      });
    },
    "json"
  );

  $("#AddUser").on("click", function () {
    let newUserInput = $("#NewUser").val();
    if (newUserInput !== "") {
      userCreationInfo = {
        btnClicked: true,
        newUsername: newUserInput,
      };
      $.post("./backEnd.php", userCreationInfo, function (addNewUserResult) {
        alert(addNewUserResult);
        $("#NewUser").val("");
      });
    } else {
      alert("username required");
    }
  });

  $("#sendMessage").on("click", function () {
    let currentUserSelected = $("#Users").val();
    let mesg = $("#NewMessage").val();
    msgTo = {
      toUser: currentUserSelected,
      mesg: mesg,
    };
    $.post("./backEnd.php", msgTo, function (response) {
      console.log(response);
    });
  });

  $("#Users").on("change", function () {
    $selectedUser = $("#Users").val();
    mesgOf = {
      msgBelongTo: $selectedUser,
    };
    $.post("./backEnd.php", mesgOf, function (replyHTML) {
      replyHTML.forEach((row) => {
        $("#messages").append(row.msg);
      });
      console.log(replyHTML);
    });
  });
}
