$(start);
function start() {
  console.log("page loaded");
}

function createSelector() {
  let selectTagStart = $("<select>");
  let selectTagWithName = selectTagStart.attr("name", "#");

  let optionTagStart = $("<option>");

  selectTagWithName.attr({
    id: "selectTagHtmlID",
    class: "selectTagHtmlClass",
  });

  let selectTagEnd = $("</select>");
  let optionTagEnd = $("<option>");

  let myArr = ["1", "2", "3", "4"];

  for (let i = 0; i < myArr.length; i++) {
    let optionTagWithValue = optionTagStart.attr("value", "i");
  }
}
