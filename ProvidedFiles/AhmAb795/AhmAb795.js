$(start);

function start() {
  let inputField = $("<input>").attr("type", "number").attr("id", "input");
  $("body").append(inputField);

  let selectionBar = $("<select>")
    .attr("name", "selctionBarN")
    .attr("id", "selctionBar");

  let option1 = $("<option>").html("red").val("0");
  let option2 = $("<option>").html("blue").val("1");
  let option3 = $("<option>").html("yellow").val("2");

  selectionBar.append(option1).append(option2).append(option3);
  $("body").append(selectionBar);

  let CreateBtn = $("<button>").attr("id", "CreateBtn").html("Create");
  $("body").append(CreateBtn);

  let ResetBtn = $("<button>").attr("id", "ResetBtn").html("Reset");
  $("body").append(ResetBtn);

  /* create boxs */
  let resultDiv = $("<div>").attr("id", "ParentDiv");
  $("body").append(resultDiv);

  $("#CreateBtn").on("click", function () {
    let selectedColor = $("#selctionBar").val();
    let color;
    if (selectedColor == "0") {
      color = "red";
    } else if (selectedColor == "1") {
      color = "blue";
    } else {
      color = "yellow";
    }
    let inputV = Number($("#input").val());
    for (i = 0; i < inputV; i++) {
      /* $("body").append($("<div>").addClass("box").addClass("")); */
      $("#ParentDiv").append($("<div>").addClass("box").addClass(color));
      $("body").append(resultDiv);
    }
  });
  $("#ResetBtn").on("click", function () {
    $("#input").val($("#input").val(""));
    $(resultDiv).html("");
  });
}
