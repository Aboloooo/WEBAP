$(start);

function start() {
  template();
}
function clearInput() {
  $("#inputField").val("");
}

/* function sum() {
  let inputV = Number($("#inputField").val());
  $("#result").text(inputV + " +");
  clearInput();
} */

function template() {
  let mainContainer = $("<div>").attr("id", "mainContainer");
  let h1 = $("<h1>");
  h1.text("two input calsulator");
  let inputField = $("<input>").attr("type", "text").attr("id", "inputField");
  let commadBtn = $("<button>").html(",").attr("id", "commaBtn");
  let plusBtn = $("<button>").html("+").attr("id", "plusBtn");
  let minusBtn = $("<button>").html("-").attr("id", "minusBtn");
  let equalBtn = $("<button>").html("=").attr("id", "equalBtn");
  let clearBtn = $("<button>").html("clear").attr("id", "clearBtn");
  let breakTag = $("<br/>");

  mainContainer
    .append(h1)
    .append(inputField)
    .append(commadBtn)
    .append(plusBtn)
    .append(minusBtn)
    .append(equalBtn)
    .append(clearBtn)
    .append(breakTag);

  let resultContainer = $("<div>").attr("id", "resultContainer");
  let h2 = $("<h2>").attr("id", "result");
  resultContainer.append(h2);

  $("body").append(mainContainer).append(resultContainer);

  let arrayOfInput = [];
  let operation;
  $("#plusBtn").on("click", function () {
    let inputV = parseFloat($("#inputField").val());
    arrayOfInput.push(inputV);
    operation = "+";
    clearInput();
  });
  $("#minusBtn").on("click", function () {
    let inputV = parseFloat($("#inputField").val());
    arrayOfInput.push(inputV);
    operation = "-";
    clearInput();
  });
  $("#equalBtn").on("click", function () {
    let inputV = parseFloat($("#inputField").val());
    arrayOfInput.push(inputV);
    let result;
    if (operation == "+") {
      result = 0;
      arrayOfInput.forEach((element) => {
        result += element;
        console.log(typeof element);
      });
    }
    if (operation == "-") {
      result = arrayOfInput[0];
      for (let i = 1; i < arrayOfInput.length; i++) {
        result -= arrayOfInput[i];
        console.log(typeof arrayOfInput[i]);
      }
    }
    $("#result").text(result);
    clearInput();
  });
  $("#clearBtn").on("click", function () {
    arrayOfInput = [];
    operation = null;
    $("#result").text("");
  });
  $("#commaBtn").on("click", function () {
    $("#inputField").val($("#inputField").val() + ".");
  });
}
