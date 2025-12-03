$(start);

function start() {
  creator();
}

let list = [];
/* 
var matrix = [
    [1, 2, 3],
    [4, 5, 6],
    [7, 8, 9]
];
console.log(matrix[0][1]); // 2
console.log(matrix[2][0]); // 7
*/
let orderRow;
function addBtn() {
  orderRow = 0;
  let selectedItemValue = $("#selectL").val();
  let selectedItemTxt = $("#selectL option:selected").text();
  let quantity = $("#quantity").val();
  let order = selectedItemTxt + "-" + quantity;
  list.push(order);
  alert("Item added to your list");
  /*   $("#finalSubBtn").on("click", () => showRecipt(selectedItemValue, selectedItemTxt));
   */
}

function creator() {
  let recipeNameInput = $("<input>")
    .attr("id", "recipeNameV")
    .attr("placeholder", "Recipe Name");
  $("body").append(recipeNameInput);

  let btn = $("<button>").attr("id", "btn").text("Create");

  $("body").append(btn);
  $("#btn").on("click", function () {
    let recipeNameV = $("#recipeNameV").val();
    $("body").html("");
    let Options = ["Carrots", "Potatoes", "Rice", "Milk"];

    let selectList = $("<select>")
      .attr("id", "selectL")
      .attr("name", "selectL");
    let pre_option = $("<option>").text("select an option");
    selectList.append(pre_option);

    for (let i = 0; i < Options.length; i++) {
      let option = $("<option>");
      option.attr("value", i).text(Options[i]);
      selectList.append(option);
    }
    $("body").append(selectList);

    let qunatityInput = $("<input>")
      .attr("id", "quantity")
      .attr("type", "number")
      .attr("placeholder", "Quantity");
    $("body").append(qunatityInput);

    let addItemBtn = $("<button>").attr("id", "addItemBtn").text("add");
    $("body").append(addItemBtn);
    $("#addItemBtn").on("click", addBtn);

    $("body").append("<br>");
    $("body").append("<br>");

    let finalSubmitBtn = $("<button>")
      .attr("id", "finalSubBtn")
      .text("create recipt");
    $("body").append(finalSubmitBtn);
    $("#finalSubBtn").on("click", function () {
      let header = $("<h1>");
      header.html(recipeNameV);
      let listDiv = $("<div>");
      listDiv.append(header);
      list.forEach((element) => {
        let ul = $("<ul>");
        ul.append(element);
        listDiv.append(ul);
      });
      $("body").append(listDiv);
    });
  });
}
