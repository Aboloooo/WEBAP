$(start);
function start() {
  console.log("page loaded");

  $("#myBtn").click(createSelector)
}

function createSelector() {
  alert("Btn working")
  let selectTagStart = $("<select>");
  let selectTagWithName = selectTagStart.attr("name", "#");
  $("body").append(selectTagWithName)

  let optionTagStart = $("<option>");
  let openSelection = selectTagWithName.prepend(optionTagStart)
  $("body").append(openSelection)

  selectTagWithName.attr({
    id: "selectTagHtmlID",
    class: "selectTagHtmlClass",
  });


  let myArr = ["1", "2", "3", "4"];

  let optionTagWithValue 
  for (let i = 0; i < myArr.length; i++) {
    optionTagWithValue = optionTagStart.attr("value", i);
    $("body").append(optionTagWithValue)
  }
}
