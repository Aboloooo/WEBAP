$(start);

function start() {
  /* DOM now is loaded */
  changeH1();
  create();
}

function btnClicked() {
  let inputV = Number($("#inputValue").val());
  console.log("type of variabl inputV is: " + typeof inputV);
  alert(inputV);
}

function changeH1() {
  $("#h1").html("changed h1");
}

function create() {
  let div = $("<div>");
  let input = $("<input>").attr("type", "number").attr("id", "inputValue");

  let btn = $("<button>").attr("id", "submitBtn").text("submitBtn");

  div.append(input);
  div.append(btn);

  $("body").append(div);
  /* addEventListiner must be define for an element after appending to body always
        otherwise nothing would work!
    */
  $("#submitBtn").on("click", btnClicked);
  /*  $("#submitBtn").on("click", function () {
    alert("btn working");
  }); */
}
