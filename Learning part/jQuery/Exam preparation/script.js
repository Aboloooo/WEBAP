$(document).ready(function () {
  $(start);
});

var inputNameArray = ["Row", "Col", "targetX", "targetY"];

function start() {
  /* creating template */
  inputNameArray.forEach(function (inputName) {
    template(inputName);
  });
  let submitBtn = $("<button>");
  submitBtn.append("Click");
  submitBtn.attr("id", "submitBtn");
  $("body").append(submitBtn);

  $("#submitBtn").click(create_table);
}

function template(inputN) {
  let input = $("<input/>");
  input.attr("type", "number");
  input.attr("id", inputN);
  input.attr("placeholder", inputN);
  $("body").append(input);
}

function create_table() {
  let row = Number($("#Row").val());
  let col = Number($("#Col").val());
  let targetX = Number($("#targetX").val());
  let targetY = Number($("#targetY").val());

  let inputValuesArray = [row, col, targetX, targetY];

  /* validation */
  if (targetX > row || targetY > col) {
    alert("wrong input");
    return false;
  }
  for (let inputV = 0; inputV < inputValuesArray.length; inputV++) {
    if (inputValuesArray[inputV] < 1) {
      alert("input should not be less than 1");
      return false;
    }
  }

  $("body").html("");
  let table = $("<table>");
  for (let i = 0; i < row; i++) {
    let tr = $("<tr>");
    for (let g = 0; g < col; g++) {
      let td = $("<td>");
      tr.append(td);
      td.html("click");
      td.on("click", function () {
        if (i + 1 == targetX && g + 1 == targetY) {
          td.css("background-color", "blue");
          alert("Yes");
        } else {
          alert("No");
        }
      });
    }
    $(table).append(tr);
  }
  $("body").append(table);
}
