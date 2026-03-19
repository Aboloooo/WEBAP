let currentUser = null;
let lastSeenMessageId = 0;

function start() {
  let userName = $("<input>");
  userName.attr("placeholder", "Please pick a username");
  $("body").append(userName);

  let btnLogin = $("<button>");
  btnLogin.html("Login");
  $("body").append(btnLogin);

  btnLogin.on("click", function () {
    currentUser = userName.val();
    startChat();
  });
}

function startChat() {
  $("body").html("");

  let messageBoard = $("<div>");
  messageBoard.attr("class", "myMessages");
  $("body").append(messageBoard);
  messageBoard.attr("id", "messages");

  let welcomeDiv = $("<div>");
  welcomeDiv.html(
    "Welcome to the webchat " +
      currentUser +
      ". Please type a message and click send:",
  );
  $("body").append(welcomeDiv);

  let message = $("<input>");
  message.attr("placeholder", "Your message");
  $("body").append(message);

  let btnSendMessage = $("<button>");
  btnSendMessage.html("Send");

  btnSendMessage.on("click", function () {
    $.post("server.php", {
      userName: currentUser,
      message: message.val(),
    });
    message.val("");
  });

  $("body").append(btnSendMessage);

  setInterval(function () {
    $.get(
      "server.php",
      { lastMessageSeen: lastSeenMessageId },
      function (replyFromServer) {
        messageBoard.append(replyFromServer);
        lastSeenMessageId = Number($(".MsgId:last-of-type").html());
        console.log($("#messages").html());
      },
    );
  }, 2000);
}

$(document).ready(function () {
  start();
});
