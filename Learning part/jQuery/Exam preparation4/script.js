$(start);

function start() {
  calculator();
}

var inputValue;
var operation;

/* clear input when a btn trigger */
function clearInput() {
  $("#input").val("");
}

function Plus() {
  inputValue = $("#input").val();
  operation = " +";
  clearInput();
}
function Minus() {
  inputValue = $("#input").val();
  operation = "-";
  clearInput();
}
function Equal() {}

function Clear() {}

function calculator() {
  let container = $("<div>");
  container.attr("id", "container");

  let header = $("<h1>").text("Two input calculator");

  let input = $("<input/>");
  input.attr("type", "number").attr("id", "input");

  let decimalBtn = $("<button>");
  decimalBtn.text(",").attr("id", "decimalBtn");

  let breakTag = $("<br/>");

  let plusBtn = $("<button>");
  plusBtn.text("+").attr("id", "plusBtn");

  let minusBtn = $("<button>");
  minusBtn.text("-").attr("id", "minusBtn");

  let equalBtn = $("<button>");
  equalBtn.text("=").attr("id", "equalBtn");

  let clearBtn = $("<button>");
  clearBtn.text("clear").attr("id", "clearBtn");

  container
    .append(header)
    .append(input)
    .append(decimalBtn)
    .append(breakTag)
    .append(plusBtn)
    .append(minusBtn)
    .append(equalBtn)
    .append(clearBtn);
  $("body").append(container);

  let resultContainer = $("<div>");
  let h2 = $("<h2>");
  h2.attr("id", "display");
  resultContainer.append(h2);
  $("body").append(resultContainer);

  /* listenier btns */
  $("#plusBtn").on("click", Plus);
  $("#minusBtn").on("click", Minus);
  $("#equalBtn").on("click", Equal);
  $("#clearBtn").on("click", Clear);
}
